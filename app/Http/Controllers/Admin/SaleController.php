<?php

namespace App\Http\Controllers\Admin;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\PosSession;
use Illuminate\Http\Request;
use App\Events\PurchaseOutStock;
use Yajra\DataTables\DataTables;
use App\Services\ArchiveService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $title = 'sales';
        if($request->ajax()){
            $sales = Sale::latest();
            return DataTables::of($sales)
                    ->addIndexColumn()
                    ->addColumn('product',function($sale){
                        if (empty($sale->product) || empty($sale->product->purchase)) {
                            return '';
                        }

                        $purchase = $sale->product->purchase;
                        $details = [
                            'product' => $purchase->product,
                            'category' => optional($purchase->category)->name,
                            'supplier' => optional($purchase->supplier)->name,
                            'price' => settings('app_currency','$').' '.number_format((float) $sale->product->price, 2),
                            'quantity' => $purchase->quantity,
                            'item_quantity' => $purchase->item_quantity,
                            'packaging_box' => $purchase->packaging_box,
                            'quantity_per_box' => $purchase->quantity_per_box,
                            'expiry' => $purchase->expiry_date ? date_format(date_create($purchase->expiry_date), 'd M, Y') : 'No expiry',
                            'purchased' => $purchase->created_at ? date_format($purchase->created_at, 'd M, Y') : '',
                            'image' => $purchase->image_url,
                        ];
                        $encodedDetails = e(json_encode($details));

                        return '<button type="button" class="btn btn-link sale-product-detail-link p-0" data-details="'.$encodedDetails.'" title="View product details">'.e($purchase->product).'</button>';
                    })
                    ->addColumn('total_price',function($sale){                   
                        return settings('app_currency','$').' '. $sale->total_price;
                    })
                    ->addColumn('quantity',function($sale){
                        return $sale->quantity;
                    })
                    ->addColumn('date',function($row){
                        return date_format(date_create($row->created_at),'d M, Y');
                    })
                    ->addColumn('action', function ($row) {
                        $editbtn = '<a href="javascript:void(0)" class="dropdown-item sale-edit-btn" data-sale-id="'.$row->id.'" data-product-id="'.$row->product_id.'" data-quantity="'.$row->quantity.'"><i class="fas fa-edit mr-2"></i>Edit</a>';
                        $deletebtn = '<a data-id="'.$row->id.'" data-route="'.route('sales.destroy', $row->id).'" href="javascript:void(0)" id="deletebtn" class="dropdown-item text-danger"><i class="fas fa-trash mr-2"></i>Delete</a>';
                        if (!auth()->user()->hasPermissionTo('edit-sale')) {
                            $editbtn = '';
                        }
                        if (!auth()->user()->hasPermissionTo('destroy-sale')) {
                            $deletebtn = '';
                        }
                        return '<div class="btn-group"><button type="button" class="btn btn-sm btn-secondary dropdown-toggle sale-action-button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Sale actions"><i class="fa fa-ellipsis-v"></i></button><div class="dropdown-menu dropdown-menu-right">'.$editbtn.'<div class="dropdown-divider"></div>'.$deletebtn.'</div></div>';
                    })
                    ->rawColumns(['product','action'])
                    ->make(true);

        }
        $products = Product::get();
        return view('admin.sales.index',compact(
            'title','products',
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'create sales';
        $products = Product::where('is_active', true)->get();
        $selectedProducts = [];

        if(request()->query('barcodes')){
            $barcodeParts = explode(',', request()->query('barcodes'));
            $quantities = [];
            foreach ($barcodeParts as $part) {
                $part = trim($part);
                if ($part === '') {
                    continue;
                }
                $parts = explode(':', $part);
                $sku = trim($parts[0]);
                if ($sku === '') {
                    continue;
                }
                $qty = 1;
                if (isset($parts[1]) && is_numeric($parts[1])) {
                    $qty = max(1, intval($parts[1]));
                }
                if (!isset($quantities[$sku])) {
                    $quantities[$sku] = 0;
                }
                $quantities[$sku] += $qty;
            }

            if (!empty($quantities)) {
                $productsBySku = Product::whereIn('barcode', array_keys($quantities))->where('is_active', true)->get()->keyBy('barcode');
                foreach ($quantities as $sku => $qty) {
                    if (!isset($productsBySku[$sku])) {
                        continue;
                    }
                    $selectedProducts[] = [
                        'product' => $productsBySku[$sku],
                        'quantity' => $qty,
                    ];
                }
            }
        }

        return view('admin.sales.create',compact(
            'title','products','selectedProducts'
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // support batch items: items[][product]=id, items[][quantity]=n
        if($request->has('items') && is_array($request->items)){
            $notification = '';
            $prepared = [];
            $neededPerPurchase = [];

            $session = PosSession::openForUser(auth()->id());
            if (!$session) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['ok' => false, 'message' => 'Start a POS session first before saving a transaction.'], 409);
                }
                return redirect()->back()->with(notify('Start a POS session first before saving a transaction.', 'warning'));
            }

            // cashier / payment info (all optional, all nullable)
            $customerName   = trim((string) $request->input('customer_name', '')) ?: null;
            $notes          = $request->input('notes');
            $paymentMethod  = $request->input('payment_method', 'cash');
            $paymentAmount  = $request->input('payment_amount');
            $changeAmount   = null;
            // whole-sale discount (currency value) — surfaced from the POS v2 UI
            $discount       = $request->input('discount');

            // prepare and validate incoming items
            $blockedInactive = [];
            foreach($request->items as $item){
                if(empty($item['product']) || empty($item['quantity'])) continue;
                $qty = intval($item['quantity']);
                if($qty <= 0) continue;
                $sold_product = Product::find($item['product']);
                if(!$sold_product || empty($sold_product->purchase)) continue;
                if(!$sold_product->is_active){
                    $blockedInactive[] = optional($sold_product->purchase)->product ?? ('#'.$sold_product->id);
                    continue;
                }
                $purchaseId = $sold_product->purchase->id;
                $prepared[] = [
                    'product' => $sold_product,
                    'purchase_id' => $purchaseId,
                    'qty' => $qty,
                ];
                if (!isset($neededPerPurchase[$purchaseId])) $neededPerPurchase[$purchaseId] = 0;
                $neededPerPurchase[$purchaseId] += $qty;
            }

            if (empty($prepared)) {
                $message = !empty($blockedInactive)
                    ? 'These products are Not Active and cannot be sold: '.implode(', ', array_unique($blockedInactive)).'.'
                    : 'No valid items to process.';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['ok' => false, 'message' => $message], 422);
                }
                return redirect()->route('sales.index')->with(notify($message, 'warning'));
            }

            // perform transactional stock checks and updates to avoid oversell
            DB::beginTransaction();
            try {
                $purchaseIds = array_keys($neededPerPurchase);
                $lockedPurchases = Purchase::whereIn('id', $purchaseIds)->lockForUpdate()->get()->keyBy('id');

                // verify stock availability
                foreach ($neededPerPurchase as $pid => $need) {
                    if (!isset($lockedPurchases[$pid]) || $lockedPurchases[$pid]->quantity < $need) {
                        DB::rollBack();
                        if ($request->wantsJson() || $request->ajax()) {
                            return response()->json(['ok' => false, 'message' => 'Not enough stock for one or more scanned products.'], 422);
                        }
                        return redirect()->back()->withInput()->withErrors(['stock' => "Not enough stock for one or more scanned products."]);
                    }
                }

                // pre-compute the order total once (sum of qty * price across items)
                $orderTotal = 0;
                foreach ($prepared as $it) {
                    $orderTotal += $it['qty'] * $it['product']->price;
                }

                // normalize the cashier-facing discount to a currency amount
                $discountAmount = 0.0;
                if ($discount !== null && $discount !== '' && is_numeric($discount)) {
                    $discountAmount = max(0.0, (float) $discount);
                }
                // never let the discount exceed the pre-discount subtotal
                if ($discountAmount > $orderTotal) {
                    $discountAmount = $orderTotal;
                }

                // compute change due if a payment amount was provided (use the discounted total)
                $discountedTotal = max(0, $orderTotal - $discountAmount);
                if ($paymentAmount !== null && is_numeric($paymentAmount) && $paymentAmount !== '') {
                    $paymentAmount = (float) $paymentAmount;
                    $changeAmount  = max(0, $paymentAmount - $discountedTotal);
                }

                // deduct stock and create sales
                $firstSaleId = null;
                foreach ($prepared as $it) {
                    $purchased_item = $lockedPurchases[$it['purchase_id']];
                    $new_quantity = $purchased_item->quantity - $it['qty'];
                    $purchased_item->quantity = $new_quantity;
                    $purchased_item->save();

                    // Discount is stored on every row so the receipt (which
                    // groups siblings) can read it from any one of them.
                    $total_price = $it['qty'] * $it['product']->price;
                    $sale = Sale::create([
                        'product_id'      => $it['product']->id,
                        'quantity'        => $it['qty'],
                        'total_price'     => $total_price,
                        'customer_name'   => $customerName,
                        'notes'           => $notes,
                        'payment_method'  => $paymentMethod,
                        'payment_amount'  => $paymentAmount,
                        'change_amount'   => $changeAmount,
                        'discount'        => $discountAmount,
                        'cashier_id'      => auth()->id(),
                        'pos_session_id'  => $session->id,
                    ]);
                    if ($firstSaleId === null) {
                        $firstSaleId = $sale->id;
                    }

                    StockMovement::create([
                        'purchase_id' => $purchased_item->id,
                        'product_id' => $it['product']->id,
                        'user_id' => auth()->id(),
                        'movement_type' => 'outgoing',
                        'quantity' => -$it['qty'],
                        'quantity_after' => $new_quantity,
                        'unit_cost' => $purchased_item->cost_price,
                        'batch_number' => $purchased_item->batch_number,
                        'reference_type' => 'sale',
                        'reference_id' => $sale->id,
                        'notes' => 'Dispensed through POS.',
                    ]);

                    if ($new_quantity === 0) {
                        event(new PurchaseOutStock($purchased_item, 'out_of_stock'));
                        $notification = notify("Product is now out of stock!", 'danger');
                    } elseif ($new_quantity <= 10) {
                        event(new PurchaseOutStock($purchased_item, 'low_stock'));
                        $notification = notify("Product stock is low and needs refill.", 'warning');
                    } else {
                        $notification = notify("Products have been sold");
                    }
                }

                // surface a clear summary message for the cashier
                if ($paymentAmount !== null && $changeAmount !== null) {
                    $summary = sprintf(
                        'Sale #%d recorded. Total %s %s. Cash %s — change %s %s.',
                        $firstSaleId,
                        settings('app_currency', '$'),
                        number_format($discountedTotal, 2),
                        number_format((float) $paymentAmount, 2),
                        settings('app_currency', '$'),
                        number_format($changeAmount, 2)
                    );
                    $notification = notify($summary, 'success');
                }

                DB::commit();

                if ($request->wantsJson() || $request->ajax()) {
                    // notify() returns [message, alert-type] as a plain array
                    $msg = is_array($notification) ? ($notification['message'] ?? 'Transaction recorded.') : (string) $notification;
                    return response()->json([
                        'ok'          => true,
                        'sale_id'     => $firstSaleId,
                        'receipt_url' => route('pos.orders.receipt', ['sale' => $firstSaleId]),
                        'message'     => $msg,
                    ]);
                }

                return redirect()->route('sales.index')->with($notification);
            } catch (\Exception $e) {
                DB::rollBack();
                // Log the full exception so the cashier-visible "An error
                // occurred" message isn't the only thing in the system. The
                // user reports the modal but cannot see the root cause, so
                // we surface it to storage/logs/laravel.log with the
                // request payload that triggered it.
                Log::error('SaleController::store failed', [
                    'message' => $e->getMessage(),
                    'class'   => get_class($e),
                    'file'    => $e->getFile() . ':' . $e->getLine(),
                    'payload' => $request->all(),
                ]);
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'ok'      => false,
                        'message' => 'An error occurred processing the sale: ' . $e->getMessage(),
                    ], 500);
                }
                return redirect()->back()->withInput()->withErrors(['stock' => "An error occurred processing the sale: " . $e->getMessage()]);
            }
        }

        // legacy single-item support (use row lock to avoid races)
        $this->validate($request, [
            'product' => 'required',
            'quantity' => 'required|integer|min:1'
        ]);
        $sold_product = Product::find($request->product);
        $notification = '';
        if ($sold_product && !$sold_product->is_active) {
            return redirect()->back()->withInput()->withErrors(['stock' => 'This product is Not Active and cannot be sold. Switch it to Active first.']);
        }
        if ($sold_product && !empty($sold_product->purchase)) {
            // lock the purchase row for update to avoid concurrent oversell
            DB::beginTransaction();
            try {
                $purchased_item = Purchase::where('id', $sold_product->purchase->id)->lockForUpdate()->first();
                if ($purchased_item) {
                    $new_quantity = ($purchased_item->quantity) - ($request->quantity);
                    if (!($new_quantity < 0)) {
                        $purchased_item->update(['quantity' => $new_quantity]);
                        $total_price = ($request->quantity) * ($sold_product->price);
                        Sale::create([
                            'product_id' => $request->product,
                            'quantity' => $request->quantity,
                            'total_price' => $total_price,
                        ]);

                        StockMovement::create([
                            'purchase_id' => $purchased_item->id,
                            'product_id' => $sold_product->id,
                            'user_id' => auth()->id(),
                            'movement_type' => 'outgoing',
                            'quantity' => -(int) $request->quantity,
                            'quantity_after' => $new_quantity,
                            'unit_cost' => $purchased_item->cost_price,
                            'batch_number' => $purchased_item->batch_number,
                            'reference_type' => 'sale',
                            'reference_id' => null,
                            'notes' => 'Dispensed through sales form.',
                        ]);

                        if ($new_quantity === 0) {
                            event(new PurchaseOutStock($purchased_item, 'out_of_stock'));
                            $notification = notify("Product is now out of stock!", 'danger');
                        } elseif ($new_quantity <= 10) {
                            event(new PurchaseOutStock($purchased_item, 'low_stock'));
                            $notification = notify("Product stock is low and needs refill.", 'warning');
                        } else {
                            $notification = notify("Product has been sold");
                        }
                    }
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()->withInput()->withErrors(['stock' => "An error occurred processing the sale."]);
            }
        }
        return redirect()->route('sales.index')->with($notification);
    }

    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \app\Models\Sale $sale
     * @return \Illuminate\Http\Response
     */
    public function edit(Sale $sale)
    {
        $title = 'edit sale';
        $products = Product::where('is_active', true)->orWhere('id', $sale->product_id)->get();
        return view('admin.sales.edit',compact(
            'title','sale','products'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \app\Models\Sale $sale
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Sale $sale)
    {
        $this->validate($request,[
            'product'=>'required',
            'quantity'=>'required|integer|min:1'
        ]);
        $sold_product = Product::find($request->product);
        /**
         * update quantity of sold item from purchases
        **/
        $purchased_item = Purchase::find($sold_product->purchase->id);
        if(!empty($request->quantity)){
            $new_quantity = ($purchased_item->quantity) - ($request->quantity);
        }
        $new_quantity = $sale->quantity;
        $notification = '';
        if (!($new_quantity < 0)){
            $purchased_item->update([
                'quantity'=>$new_quantity,
            ]);

            /**
             * calcualting item's total price
            **/
            if(!empty($request->quantity)){
                $total_price = ($request->quantity) * ($sold_product->price);
            }
            $total_price = $sale->total_price;
            $sale->update([
                'product_id'=>$request->product,
                'quantity'=>$request->quantity,
                'total_price'=>$total_price,
            ]);

            $notification = notify("Product has been updated");
        } 
        if($new_quantity <=1 && $new_quantity !=0){
            // send notification 
            $product = Purchase::where('quantity', '<=', 1)->first();
            event(new PurchaseOutStock($product));
            // end of notification 
            $notification = notify("Product is running out of stock!!!");
            
        }
        return redirect()->route('sales.index')->with($notification);
    }

    /**
     * Generate sales reports index
     *
     * @return \Illuminate\Http\Response
     */
    public function reports(Request $request){
        $title = 'sales reports';
        if ($request->boolean('embedded')) {
            return view('admin.reports.sales-embedded', compact('title'));
        }
        return view('admin.sales.reports',compact(
            'title'
        ));
    }

    /**
     * Generate sales report form post
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function generateReport(Request $request){
        $this->validate($request,[
            'from_date' => 'required',
            'to_date' => 'required',
        ]);
        $title = 'sales reports';
        $sales = Sale::whereBetween(DB::raw('DATE(created_at)'), array($request->from_date, $request->to_date))->get();
        if ($request->boolean('embedded')) {
            return view('admin.reports.sales-embedded', compact('sales', 'title'));
        }
        return view('admin.sales.reports',compact(
            'sales','title'
        ));
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $sale = Sale::findOrFail($request->id);
        ArchiveService::record($sale, 'Sale #'.$sale->id);
        return $sale->delete();
    }
}
