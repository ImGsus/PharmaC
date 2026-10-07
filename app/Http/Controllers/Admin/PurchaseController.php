<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use QCod\AppSettings\Setting\AppSettings;
use App\Services\ArchiveService;
use App\Services\OrganizedFileStorage;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $title = 'purchases';
        if($request->ajax()){
            $purchases = Purchase::latest()
                ->get()
                ->unique('supplier_id')
                ->values();
            return DataTables::of($purchases)
                ->addColumn('product',function($purchase){
                    $image = '';
                    if(!empty($purchase->image)){
                        $imageUrl = $purchase->image_url;
                        $image = '<span class="avatar avatar-sm mr-2">
                            <img class="avatar-img" src="'.$imageUrl.'" alt="product">
                        </span>';
                    }
                    $productName = htmlspecialchars($purchase->product, ENT_QUOTES, 'UTF-8');
                    $categoryName = htmlspecialchars(optional($purchase->category)->name, ENT_QUOTES, 'UTF-8');
                    $supplierName = htmlspecialchars(optional($purchase->supplier)->name, ENT_QUOTES, 'UTF-8');
                    $detailsBtn = '<button type="button" class="btn btn-sm btn-light purchase-detail-btn" '
                        . 'data-product="'.$productName.'" '
                        . 'data-category="'.$categoryName.'" '
                        . 'data-supplier="'.$supplierName.'" '
                        . 'data-cost="'.settings('app_currency','$').' '.$purchase->cost_price.'" '
                        . 'data-quantity="'.htmlspecialchars($purchase->quantity, ENT_QUOTES, 'UTF-8').'" '
                        . 'data-expiry="'.($purchase->expiry_date ? date_format(date_create($purchase->expiry_date),'d M, Y') : 'No expiry').'" '
                        . 'data-item_quantity="'.htmlspecialchars($purchase->item_quantity, ENT_QUOTES, 'UTF-8').'" '
                        . 'data-packaging_box="'.htmlspecialchars($purchase->packaging_box, ENT_QUOTES, 'UTF-8').'" '
                        . 'data-quantity_per_box="'.htmlspecialchars($purchase->quantity_per_box, ENT_QUOTES, 'UTF-8').'" '
                        . 'title="View purchase details">...</button>';
                    return $image.'<span class="purchase-name-text">'.$productName.'</span> '.$detailsBtn;
                })
                ->addColumn('submitted_at', function ($purchase) {
                    return optional($purchase->created_at)->format('d M, Y');
                })
                ->addColumn('category',function($purchase){
                    if(!empty($purchase->category)){
                        return $purchase->category->name;
                    }
                })
                ->addColumn('cost_price',function($purchase){
                    return settings('app_currency','$'). ' '. $purchase->cost_price;
                })
                ->addColumn('supplier',function($purchase){
                    return $purchase->supplier->name;
                })
                ->addColumn('expiry_date',function($purchase){
                    return $purchase->expiry_date
                        ? date_format(date_create($purchase->expiry_date),'d M, Y')
                        : 'No expiry';
                })
                ->addColumn('action', function ($row) {
                    $supplierProducts = Purchase::with(['category', 'supplier', 'purchaseProduct'])
                        ->where('supplier_id', $row->supplier_id)
                        ->latest()
                        ->get()
                        ->map(function ($purchase) {
                            $currentPrice = optional($purchase->purchaseProduct)->price;
                            $formattedPrice = settings('app_currency', '$').' '.($currentPrice !== null ? $currentPrice : $purchase->cost_price);
                            return [
                                'id' => $purchase->id,
                                'product' => $purchase->product,
                                'category' => optional($purchase->category)->name,
                                'supplier' => optional($purchase->supplier)->name,
                                'cost' => $formattedPrice,
                                'price' => $formattedPrice,
                                'quantity' => $purchase->quantity,
                                'item_quantity' => $purchase->item_quantity,
                                'packaging_box' => $purchase->packaging_box,
                                'quantity_per_box' => $purchase->quantity_per_box,
                                'expiry' => $purchase->expiry_date ? date_format(date_create($purchase->expiry_date), 'd M, Y') : 'No expiry',
                                'box_expiries' => $purchase->box_expiries,
                                'purchased' => optional($purchase->created_at)->format('d M, Y'),
                                'image' => $purchase->image_url,
                            ];
                        });
                    $editbtn = '<a href="'.route("purchases.edit", $row->id).'" data-row-action-name="Purchase" data-row-action-table="purchase-table" data-row-action-list-path="'.route('purchases.index').'" class="dropdown-item editbtn row-action-iframe-edit"><i class="fas fa-edit mr-2"></i>Edit</a>';
                    $deletebtn = '<a data-id="'.$row->supplier_id.'" data-route="'.route('purchases.supplier-destroy', $row->supplier_id).'" href="javascript:void(0)" id="deletebtn" class="dropdown-item text-danger"><i class="fas fa-trash mr-2"></i>Delete</a>';
                    if (!auth()->user()->hasPermissionTo('edit-purchase')) {
                        $editbtn = '';
                    }
                    if (!auth()->user()->hasPermissionTo('destroy-purchase')) {
                        $deletebtn = '';
                    }
                    $detailbtn = '<button type="button" class="dropdown-item purchase-detail-btn" '
                        . 'data-supplier="'.htmlspecialchars(optional($row->supplier)->name, ENT_QUOTES, 'UTF-8').'" '
                        . 'data-products="'.htmlspecialchars($supplierProducts->toJson(), ENT_QUOTES, 'UTF-8').'" ><i class="fas fa-info-circle mr-2"></i>View Details</button>';
                    $supplierName = htmlspecialchars(optional($row->supplier)->name ?? '', ENT_QUOTES, 'UTF-8');
                    return '<div class="btn-group"><button type="button" class="btn btn-sm btn-secondary dropdown-toggle purchase-action-button row-action-modal-trigger" data-action-title="Purchase Actions" data-context-label="Supplier" data-context-value="'.$supplierName.'" aria-haspopup="true" aria-expanded="false" aria-label="Purchase actions"><i class="fa fa-ellipsis-v"></i></button><div class="dropdown-menu dropdown-menu-right">'.$detailbtn.'<div class="dropdown-divider"></div>'.$editbtn.$deletebtn.'</div></div>';
                })
                ->rawColumns(['product','action'])
                ->make(true);
        }
        return view('admin.purchases.index', array_merge(
            compact('title'),
            $this->purchaseFormData($request)
        ));
    }

    public function purchaseFormData(Request $request): array
    {
        $categories = Category::get();
        $preselectedSupplier = $request->query('supplier');
        $preselectedProduct = $request->query('product');
        $preselectedCategory = $request->query('category');
        $clearPrefill = filter_var($request->query('clearPrefill'), FILTER_VALIDATE_BOOLEAN);
        $blockedSupplier = $request->query('blocked_supplier');
        $blockedProduct = $request->query('blocked_product');
        $pendingSupplier = session('pending_supplier');

        if ($pendingSupplier && $preselectedProduct !== ($pendingSupplier['product'] ?? null)) {
            session()->forget('pending_supplier');
            $pendingSupplier = null;
        }

        $suppliersQuery = Supplier::query();
        if (!empty($blockedSupplier)) $suppliersQuery->where('id', '!=', $blockedSupplier);
        if (!empty($blockedProduct)) $suppliersQuery->where('product', '!=', $blockedProduct);
        if (!empty($preselectedSupplier)) $suppliersQuery->where('id', $preselectedSupplier);
        $suppliers = $suppliersQuery->get()
            ->unique(fn ($supplier) => strtolower(trim($supplier->name)))
            ->values();

        $usedSupplierIds = Purchase::whereIn('supplier_id', $suppliers->pluck('id'))
            ->whereHas('purchaseProduct')
            ->pluck('supplier_id')
            ->map(fn ($id) => (string) $id)
            ->all();

        if ($pendingSupplier) $suppliers = collect();

        $supplierPurchaseMap = [];
        foreach ($suppliers as $supplier) {
            $last = Purchase::where('supplier_id', $supplier->id)
                ->whereDoesntHave('purchaseProduct')
                ->doesntHave('stockMovements')
                ->latest()
                ->first();
            if ($last) {
                $supplierPurchaseMap[$supplier->id] = [
                    'product' => $last->product,
                    'category_id' => $last->category_id,
                    'cost_price' => $last->cost_price,
                    'expiry_date' => $last->expiry_date,
                ];
            }
        }

        return compact(
            'categories', 'suppliers', 'preselectedSupplier', 'preselectedProduct',
            'preselectedCategory', 'clearPrefill', 'blockedSupplier', 'blockedProduct',
            'supplierPurchaseMap', 'pendingSupplier', 'usedSupplierIds'
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $title = 'create purchase';
        $categories = Category::get();
        $preselectedSupplier = $request->query('supplier');
        $preselectedProduct = $request->query('product');
        $preselectedCategory = $request->query('category');
        $clearPrefill = filter_var($request->query('clearPrefill'), FILTER_VALIDATE_BOOLEAN);
        $blockedSupplier = $request->query('blocked_supplier');
        $blockedProduct = $request->query('blocked_product');
        $pendingSupplier = session('pending_supplier');

        if ($pendingSupplier && $preselectedProduct !== ($pendingSupplier['product'] ?? null)) {
            session()->forget('pending_supplier');
            $pendingSupplier = null;
        }

        if (!empty($preselectedProduct)) {
            $existingProduct = Purchase::whereRaw('LOWER(TRIM(product)) = ?', [strtolower(trim($preselectedProduct))])
                ->whereHas('purchaseProduct')
                ->exists();

            if ($existingProduct) {
                $preselectedProduct = null;
                $preselectedCategory = null;
            }
        }

        // Build suppliers query while excluding blocked supplier/product so they never render
        $suppliersQuery = Supplier::query();
        if (!empty($blockedSupplier)) {
            $suppliersQuery->where('id', '!=', $blockedSupplier);
        }
        if (!empty($blockedProduct)) {
            $suppliersQuery->where('product', '!=', $blockedProduct);
        }
        if (!empty($preselectedSupplier)) {
            $suppliersQuery->where('id', $preselectedSupplier);
        }
        $suppliers = $suppliersQuery->get()
            ->unique(function ($supplier) {
                return strtolower(trim($supplier->name));
            })
            ->values();

        $usedSupplierIds = Purchase::whereIn('supplier_id', $suppliers->pluck('id'))
            ->whereHas('purchaseProduct')
            ->pluck('supplier_id')
            ->map(fn ($id) => (string) $id)
            ->all();

        if ($pendingSupplier) {
            $suppliers = collect();
        }

        // Build a map of supplier => latest purchase data to enable client-side autofill
        $supplierPurchaseMap = [];
        foreach ($suppliers as $s) {
            $last = Purchase::where('supplier_id', $s->id)
                ->whereDoesntHave('purchaseProduct')
                ->doesntHave('stockMovements')
                ->latest()
                ->first();
            if ($last) {
                $supplierPurchaseMap[$s->id] = [
                    'product' => $last->product,
                    'category_id' => $last->category_id,
                    'cost_price' => $last->cost_price,
                    'expiry_date' => $last->expiry_date,
                    'item_quantity' => $last->item_quantity,
                    'packaging_box' => $last->packaging_box,
                    'quantity_per_box' => $last->quantity_per_box,
                ];
            }
        }

        if ($clearPrefill) {
            $preselectedSupplier = null;
            $preselectedProduct = null;
            $preselectedCategory = null;
            // If there is a blocked supplier or product, remove matching suppliers
            if (!empty($blockedSupplier) || !empty($blockedProduct)) {
                $suppliers = $suppliers->reject(function($s) use ($blockedSupplier, $blockedProduct) {
                    if (!empty($blockedSupplier) && $s->id == $blockedSupplier) return true;
                    if (!empty($blockedProduct) && isset($s->product) && trim($s->product) !== '' && trim($s->product) == trim($blockedProduct)) return true;
                    return false;
                })->values();
            }
            $duplicatePurchase = Purchase::whereRaw('LOWER(TRIM(product)) = ?', [strtolower(trim($preselectedProduct))])
                ->where('supplier_id', $preselectedSupplier)
                ->exists();

            if ($duplicatePurchase) {
                $preselectedSupplier = null;
                $preselectedProduct = null;
                $preselectedCategory = null;
                $clearPrefill = true;
            }
        }

        return view('admin.purchases.create',compact(
            'title','categories','suppliers','preselectedSupplier','preselectedProduct','preselectedCategory','clearPrefill','blockedSupplier','blockedProduct','supplierPurchaseMap','pendingSupplier','usedSupplierIds'
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
        $category = Category::find($request->category);
        $isNoExpiry = ($category && $category->no_expiry) || $request->boolean('no_expiry');

        $boxExpiriesList = [];
        $dates = [];

        if (!$isNoExpiry) {
            $rawBoxExpiries = $request->input('box_expiries', []);
            if (is_array($rawBoxExpiries)) {
                foreach ($rawBoxExpiries as $idx => $date) {
                    $trimmed = trim((string)$date);
                    if (!empty($trimmed)) {
                        $boxExpiriesList[] = [
                            'box' => $idx + 1,
                            'expiry_date' => $trimmed,
                        ];
                        $dates[] = $trimmed;
                    }
                }
            }
            if ($request->filled('loose_expiry')) {
                $looseDate = trim((string)$request->loose_expiry);
                if (!empty($looseDate)) {
                    $boxExpiriesList[] = [
                        'box' => 'Loose Items',
                        'expiry_date' => $looseDate,
                    ];
                    $dates[] = $looseDate;
                }
            }
        }

        $effectiveExpiryDate = null;
        if (!$isNoExpiry) {
            if (!empty($dates)) {
                $effectiveExpiryDate = min($dates);
            } elseif ($request->filled('expiry_date')) {
                $effectiveExpiryDate = $request->expiry_date;
            }
        }

        $this->validate($request,[
            'product'=>'required|max:200',
            'category'=>'required|exists:categories,id',
            'cost_price'=>'required|min:1',
            'item_quantity'=>'nullable|integer|min:0',
            'packaging_box'=>'nullable|integer|min:0',
            'quantity_per_box'=>'nullable|integer|min:0',
            'total_quantity'=>'nullable|integer|min:0',
            'supplier'=>'required',
            'batch_number'=>'nullable|string|max:100',
            'manufacture_date'=>'nullable|date',
            'reorder_level'=>'nullable|integer|min:0',
            'order_number'=>'nullable|string|max:100',
            'expected_delivery_date'=>'nullable|date',
            'image'=>'file|image|mimes:jpg,jpeg,png,gif',
            'barcode'=>['nullable', 'string', 'max:100', Rule::unique('products', 'barcode')],
        ]);

        if (!$isNoExpiry && empty($effectiveExpiryDate)) {
            return redirect()->back()->withInput()->withErrors([
                'expiry_date' => 'Please provide at least one expiration date for the items/packaging boxes or product.',
            ]);
        }

        $itemQty = (int)$request->input('item_quantity', 0);
        $boxQty = (int)$request->input('packaging_box', 0);
        $perBox = (int)$request->input('quantity_per_box', 0);

        if ($boxQty > 0 && $perBox <= 0) {
            return redirect()->back()->withInput()->withErrors([
                'quantity_per_box' => 'Please specify the quantity inside per packaging box.',
            ]);
        }

        // Compute incoming amounts
        $incomingTotal = $itemQty + ($boxQty * $perBox);
        if ($incomingTotal <= 0) {
            return redirect()->back()->withInput()->withErrors([
                'total_quantity' => 'Total quantity must be greater than 0. Please enter item quantity or packaging boxes.',
            ]);
        }

        if ($request->supplier === 'pending') {
            $pendingSupplier = $request->session()->pull('pending_supplier');
            if (!$pendingSupplier) {
                return redirect()->route('suppliers.create')->withErrors([
                    'supplier' => 'The pending supplier details are no longer available.',
                ]);
            }

            $supplier = Supplier::create($pendingSupplier);
            $request->merge(['supplier' => $supplier->id]);
        }

        // If a purchase with same product (case-insensitive) and supplier exists, update its quantities instead of creating a new row
        $existing = Purchase::whereRaw('LOWER(TRIM(product)) = ?', [strtolower(trim($request->product))])
            ->where('supplier_id', $request->supplier)
            ->first();

        if ($existing) {
            $existing->item_quantity = ((int)$existing->item_quantity) + $itemQty;
            // update packaging/box info to the latest submission (optional)
            $existing->packaging_box = $boxQty;
            $existing->quantity_per_box = $perBox;
            $existing->quantity = ((int)$existing->quantity) + $incomingTotal;
            $existing->total_quantity = ((int)$existing->total_quantity) + $incomingTotal;
            // update cost/expiry to latest values (keep image unchanged)
            $existing->cost_price = $request->cost_price;
            $existing->expiry_date = $effectiveExpiryDate;
            $existing->box_expiries = !empty($boxExpiriesList) ? $boxExpiriesList : null;
            $existing->batch_number = $request->batch_number ?: $existing->batch_number;
            $existing->manufacture_date = $request->manufacture_date ?: $existing->manufacture_date;
            $existing->reorder_level = $request->input('reorder_level', $existing->reorder_level ?: 10);
            $existing->order_number = $request->order_number ?: $existing->order_number;
            $existing->expected_delivery_date = $request->expected_delivery_date ?: $existing->expected_delivery_date;
            $existing->received_date = now()->toDateString();
            $existing->status = 'received';
            $existing->save();

            StockMovement::create([
                'purchase_id' => $existing->id,
                'product_id' => optional($existing->purchaseProduct)->id,
                'user_id' => auth()->id(),
                'movement_type' => 'incoming',
                'quantity' => $incomingTotal,
                'quantity_after' => $existing->quantity,
                'unit_cost' => $existing->cost_price,
                'batch_number' => $existing->batch_number,
                'reference_type' => 'purchase',
                'reference_id' => $existing->id,
                'notes' => 'Additional stock received.',
            ]);

            $existing->purchaseProduct()->updateOrCreate(
                [],
                [
                    'price' => $request->cost_price,
                    'discount' => 0,
                    'barcode' => $request->barcode,
                ]
            );

            $hasExpired = \App\Services\ExpiryNotificationService::checkAndNotifyPurchase($existing);
            $msg = $hasExpired > 0
                ? "Purchase updated. ⚠️ Notice: Product contains expired stock and is currently Active!"
                : "Purchase updated (quantities increased)";
            $notifications = notify($msg, $hasExpired > 0 ? 'warning' : 'success');
            if ($request->expectsJson()) {
                return response()->json(['message' => $msg]);
            }

            if ($request->input('redirect_to') === 'products.index' || ($request->headers->get('referer') && str_contains($request->headers->get('referer'), '/products') && !str_contains($request->headers->get('referer'), '/purchases'))) {
                return redirect()->route('products.index')->with($notifications);
            }

            return redirect()->route('purchases.index')->with($notifications);
        }

        $imageName = null;
        if($request->hasFile('image')){
            // Make sure the destination folder exists and is writable before
            // Symfony's File::move() tries to write — gives a cleaner error
            // than "Unable to write in the directory" if the folder is missing.
            $imageName = app(OrganizedFileStorage::class)->store($request->image, 'purchases');
        }

        $purchase = Purchase::create([
            'product'=>$request->product,
            'category_id'=>$request->category,
            'supplier_id'=>$request->supplier,
            'cost_price'=>$request->cost_price,
            'quantity'=>$incomingTotal,
            'item_quantity'=>$itemQty,
            'packaging_box'=>$boxQty,
            'quantity_per_box'=>$perBox,
            'total_quantity'=>$incomingTotal,
            'expiry_date'=>$effectiveExpiryDate,
            'box_expiries'=>!empty($boxExpiriesList) ? $boxExpiriesList : null,
            'image'=>$imageName,
            'batch_number'=>$request->batch_number,
            'manufacture_date'=>$request->manufacture_date,
            'reorder_level'=>$request->input('reorder_level', 10),
            'order_number'=>$request->order_number,
            'expected_delivery_date'=>$request->expected_delivery_date,
            'received_date'=>now()->toDateString(),
            'status'=>'received',
        ]);

        Product::create([
            'purchase_id' => $purchase->id,
            'price' => $request->cost_price,
            'discount' => 0,
            'barcode' => $request->barcode,
            'is_active' => true,
        ]);

        $product = $purchase->purchaseProduct;
        StockMovement::create([
            'purchase_id' => $purchase->id,
            'product_id' => optional($product)->id,
            'user_id' => auth()->id(),
            'movement_type' => 'incoming',
            'quantity' => $purchase->quantity,
            'quantity_after' => $purchase->quantity,
            'unit_cost' => $purchase->cost_price,
            'batch_number' => $purchase->batch_number,
            'reference_type' => 'purchase',
            'reference_id' => $purchase->id,
            'notes' => 'Purchase received.',
        ]);

        $hasExpired = \App\Services\ExpiryNotificationService::checkAndNotifyPurchase($purchase);
        $msg = $hasExpired > 0
            ? "Product purchase added. ⚠️ Notice: Product contains expired stock and is currently Active!"
            : "Product purchase has been added successfully";
        $notification = notify($msg, $hasExpired > 0 ? 'warning' : 'success');

        if ($request->input('redirect_to') === 'products.index' || ($request->headers->get('referer') && str_contains($request->headers->get('referer'), '/products') && !str_contains($request->headers->get('referer'), '/purchases'))) {
            return redirect()->route('products.index')->with($notification);
        }

        return redirect()->route('purchases.index', ['purchase_created' => 1])->with($notification);
    }

    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \app\Models\Purchase $purchase
     * @return \Illuminate\Http\Response
     */
    public function edit(Purchase $purchase)
    {
        $title = 'edit purchase';
        $categories = Category::get();
        $suppliers = Supplier::get();
        return view('admin.purchases.edit',compact(
            'title','purchase','categories','suppliers'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \app\Models\Purchase $purchase
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Purchase $purchase)
    {
        $category = Category::find($request->category);
        $this->validate($request,[
            'product'=>'required|max:200',
            'category'=>'required|exists:categories,id',
            'cost_price'=>'required|min:1',
            'item_quantity'=>'nullable|integer|min:0',
            'packaging_box'=>'nullable|integer|min:0',
            'quantity_per_box'=>'nullable|integer|min:0',
            'total_quantity'=>'nullable|integer|min:0',
            'expiry_date'=>$category && ($category->no_expiry || $request->boolean('no_expiry')) ? 'nullable' : 'required',
            'supplier'=>'required',
            'batch_number'=>'nullable|string|max:100',
            'manufacture_date'=>'nullable|date',
            'reorder_level'=>'nullable|integer|min:0',
            'order_number'=>'nullable|string|max:100',
            'expected_delivery_date'=>'nullable|date',
            'image'=>'file|image|mimes:jpg,jpeg,png,gif',
        ]);

        $itemQty = (int)$request->input('item_quantity', 0);
        $boxQty = (int)$request->input('packaging_box', 0);
        $perBox = (int)$request->input('quantity_per_box', 0);

        if ($boxQty > 0 && $perBox <= 0) {
            return redirect()->back()->withInput()->withErrors([
                'quantity_per_box' => 'Please specify the quantity inside per packaging box.',
            ]);
        }

        $computedTotal = $itemQty + ($boxQty * $perBox);
        if ($computedTotal <= 0) {
            return redirect()->back()->withInput()->withErrors([
                'total_quantity' => 'Total quantity must be greater than 0. Please enter item quantity or packaging boxes.',
            ]);
        }

        $duplicate = Purchase::whereRaw('LOWER(product) = ?', [strtolower($request->product)])
            ->where('category_id', $request->category)
            ->where('supplier_id', $request->supplier)
            ->where('expiry_date', $request->expiry_date)
            ->where('cost_price', $request->cost_price)
            ->where('id', '!=', $purchase->id)
            ->first();

        if ($duplicate) {
            $notification = notify('A purchase with the same product, category, supplier, expiry date, and cost already exists. Duplicate update prevented.', 'warning');
            return redirect()->back()->withInput()->with($notification);
        }

        $imageName = $purchase->image;
        if($request->hasFile('image')){
            $oldImage = $purchase->image;
            if(!empty($oldImage)){
                $storage = app(OrganizedFileStorage::class);
                $oldImagePath = $storage->path('purchases', $oldImage);
                if (!file_exists($oldImagePath)) $oldImagePath = $storage->legacyPath('purchases', $oldImage);
                if(file_exists($oldImagePath)){
                    @unlink($oldImagePath);
                }
            }

            $imageName = app(OrganizedFileStorage::class)->store($request->image, 'purchases');
        }

        $purchase->update([
            'product'=>$request->product,
            'category_id'=>$request->category,
            'supplier_id'=>$request->supplier,
            'cost_price'=>$request->cost_price,
            'quantity'=>$computedTotal,
            'item_quantity'=>$itemQty,
            'packaging_box'=>$boxQty,
            'quantity_per_box'=>$perBox,
            'total_quantity'=>$computedTotal,
            'expiry_date'=>$request->expiry_date,
            'image'=>$imageName,
            'batch_number'=>$request->batch_number,
            'manufacture_date'=>$request->manufacture_date,
            'reorder_level'=>$request->input('reorder_level', 10),
            'order_number'=>$request->order_number,
            'expected_delivery_date'=>$request->expected_delivery_date,
            'received_date'=>$purchase->received_date ?: now()->toDateString(),
            'status'=>$purchase->status ?: 'received',
        ]);

        $hasExpired = \App\Services\ExpiryNotificationService::checkAndNotifyPurchase($purchase);
        $msg = $hasExpired > 0
            ? "Purchase updated. ⚠️ Notice: Product contains expired stock and is currently Active!"
            : "Purchase has been updated";
        $notifications = notify($msg, $hasExpired > 0 ? 'warning' : 'success');
        return redirect()->route('purchases.index')->with($notifications);
    }

    public function destroy(Request $request)
    {
        $purchase = Purchase::findOrFail($request->id);
        ArchiveService::record($purchase, 'Purchase: '.$purchase->product);
        if(!empty($purchase->image)){
            $storage = app(OrganizedFileStorage::class);
            $imagePath = $storage->path('purchases', $purchase->image);
            if (!file_exists($imagePath)) $imagePath = $storage->legacyPath('purchases', $purchase->image);
            if(file_exists($imagePath)){
                @unlink($imagePath);
            }
        }

        return $purchase->delete();
    }

    public function bulkDestroy(Request $request)
    {
        abort_unless(auth()->user()->hasPermissionTo('destroy-purchase'), 403);

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'exists:purchases,id'],
        ]);

        $purchases = Purchase::whereIn('id', $validated['ids'])->get();

        foreach ($purchases as $purchase) {
            ArchiveService::record($purchase, 'Purchase: '.$purchase->product);
            if (!empty($purchase->image)) {
                $storage = app(OrganizedFileStorage::class);
                $imagePath = $storage->path('purchases', $purchase->image);
                if (!file_exists($imagePath)) $imagePath = $storage->legacyPath('purchases', $purchase->image);
                if (file_exists($imagePath)) {
                    @unlink($imagePath);
                }
            }

            Product::where('purchase_id', $purchase->id)->delete();
        }

        Purchase::whereIn('id', $validated['ids'])->delete();

        return response()->json([
            'message' => count($validated['ids']).' purchase(s) deleted successfully.',
        ]);
    }

    public function destroySupplier(Request $request, $supplier)
    {
        abort_unless(auth()->user()->hasPermissionTo('destroy-purchase'), 403);

        $purchases = Purchase::where('supplier_id', $supplier)->get();
        abort_if($purchases->isEmpty(), 404);

        foreach ($purchases as $purchase) {
            ArchiveService::record($purchase, 'Purchase: '.$purchase->product);
            if (!empty($purchase->image)) {
                $storage = app(OrganizedFileStorage::class);
                $imagePath = $storage->path('purchases', $purchase->image);
                if (!file_exists($imagePath)) $imagePath = $storage->legacyPath('purchases', $purchase->image);
                if (file_exists($imagePath)) {
                    @unlink($imagePath);
                }
            }

            Product::where('purchase_id', $purchase->id)->delete();
        }

        Purchase::where('supplier_id', $supplier)->delete();

        return response()->json([
            'message' => $purchases->count().' purchase(s) deleted successfully.',
        ]);
    }

    public function reports(Request $request){
        $title ='purchase reports';
        if ($request->boolean('embedded')) {
            return view('admin.reports.purchases-embedded', compact('title'));
        }
        return view('admin.purchases.reports',compact('title'));
    }

    public function generateReport(Request $request){
        $this->validate($request,[
            'from_date' => 'required',
            'to_date' => 'required'
        ]);
        $title = 'purchases reports';
        $purchases = Purchase::whereBetween(DB::raw('DATE(created_at)'), array($request->from_date, $request->to_date))->get();
        if ($request->boolean('embedded')) {
            return view('admin.reports.purchases-embedded', compact('purchases', 'title'));
        }
        return view('admin.purchases.reports',compact(
            'purchases','title'
        ));
    }

    public function exportReport(Request $request)
    {
        $this->validate($request, ['from_date' => 'required', 'to_date' => 'required']);
        $purchases = Purchase::with(['category', 'supplier'])
            ->whereBetween(DB::raw('DATE(created_at)'), [$request->from_date, $request->to_date])
            ->get();
        $currency = \QCod\AppSettings\Models\AppSettings::get('app_currency', '$');
        $appName  = \QCod\AppSettings\Models\AppSettings::get('app_name', config('app.name', 'PharmaC'));
        $rows     = $purchases->filter(fn($p) => !empty($p->supplier) && !empty($p->category))->map(fn($p) => [
            'Medicine Name'  => $p->product,
            'Category'       => $p->category->name,
            'Supplier'       => $p->supplier->name,
            'Purchase Cost'  => $currency . number_format((float)$p->cost_price, 2),
            'Quantity'       => $p->quantity,
            'Expire Date'    => $p->expiry_date ? date_format(date_create($p->expiry_date), 'd M, Y') : 'No expiry',
        ])->values();

        if ($request->input('format') === 'pdf') {
            $logoPath = \QCod\AppSettings\Models\AppSettings::get('logo');
            $logoUrl  = ($logoPath && file_exists(public_path('storage/' . $logoPath))) ? url('storage/' . $logoPath) : null;
            return view('admin.reports.pdf', [
                'title'       => 'Purchase Report',
                'report'      => 'purchase-orders',
                'definition'  => ['description' => 'Purchase records for the selected date range.'],
                'rows'        => $rows,
                'from'        => $request->from_date,
                'to'          => $request->to_date,
                'currency'    => $currency,
                'appName'     => $appName,
                'generatedAt' => now()->format('F d, Y h:i A'),
                'logoUrl'     => $logoUrl,
                'orientation' => 'landscape',
            ]);
        }

        $filename = 'purchase-report-' . $request->from_date . '-to-' . $request->to_date . '.csv';
        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            if ($rows->isNotEmpty()) {
                fputcsv($out, array_keys($rows->first()));
                foreach ($rows as $row) fputcsv($out, array_values($row));
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
