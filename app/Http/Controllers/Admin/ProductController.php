<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\ArchivedSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use QCod\AppSettings\Setting\AppSettings;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $title = 'products';
        if ($request->ajax()) {
            $products = Product::latest();
            return DataTables::of($products)
                ->addColumn('product',function($product){
                    if(!empty($product->purchase)){
                        return $product->purchase->product;
                    }
                })

                ->addColumn('category',function($product){
                    $category = null;
                    if(!empty($product->purchase->category)){
                        $category = $product->purchase->category->name;
                    }
                    return $category;
                })
                ->addColumn('status',function($product){
                    if ($product->is_active) {
                        return '<span class="product-status-text active">Active</span>';
                    }
                    return '<span class="product-status-text inactive">Not Active</span>';
                })
                ->addColumn('quantity',function($product){
                    if(!empty($product->purchase)){
                        return $product->purchase->quantity;
                    }
                })
                ->addColumn('expiry_date',function($product){
                    if(!empty($product->purchase)){
                        return $product->purchase->expiry_date ? date_format(date_create($product->purchase->expiry_date),'d M, Y') : 'No expiry';
                    }
                    return 'No expiry';
                })
                ->addColumn('action', function ($row) {
                    $purchase = $row->purchase;
                    $isActive = (bool) $row->is_active;
                    $stateClass = $isActive ? 'is-active' : 'is-inactive';
                    $statusMenuItem = '';
                    if (auth()->user() && auth()->user()->hasPermissionTo('edit-product')) {
                        $statusMenuItem = '<label class="dropdown-item product-status-menu-item" title="Toggle product availability">'
                            . '<span class="product-active-switch">'
                            . '<input type="checkbox" class="product-active-toggle" data-id="'.$row->id.'" '
                            . 'data-url="'.route('products.toggle-status', $row->id).'" '
                            . 'aria-label="Toggle product availability" '
                            . ($isActive ? 'checked' : '').'>'
                            . '</span>'
                            . '<span class="product-status-menu-text">'.($isActive ? 'Active' : 'Not Active').'</span>'
                            . '</label>'
                            . '<div class="dropdown-divider"></div>';
                    }
                    $detailbtn = '<button type="button" class="btn btn-secondary product-detail-btn" '
                        . 'data-details="'.htmlspecialchars(json_encode([
                            'product' => optional($purchase)->product,
                            'image' => optional($purchase)->image ? $purchase->image_url : asset('assets/img/productnoimage.png'),
                            'category' => optional(optional($purchase)->category)->name,
                            'supplier' => optional(optional($purchase)->supplier)->name,
                            'price' => settings('app_currency', '$').' '.$row->price,
                            'quantity' => optional($purchase)->quantity,
                            'expiry' => optional($purchase)->expiry_date ? date_format(date_create($purchase->expiry_date), 'd M, Y') : 'No expiry',
                            'purchased' => optional(optional($purchase)->created_at)->format('d M, Y'),
                            'item_quantity' => optional($purchase)->item_quantity,
                            'packaging_box' => optional($purchase)->packaging_box,
                            'quantity_per_box' => optional($purchase)->quantity_per_box,
                        ]), ENT_QUOTES, 'UTF-8').'" title="View product details">...</button>';
                    $detailbtn = str_replace(
                        '<button type="button" class="btn btn-secondary product-detail-btn"',
                        '<button type="button" class="dropdown-item product-detail-btn"',
                        $detailbtn
                    );
                    $detailbtn = str_replace(' title="View product details">...</button>', '><i class="fas fa-info-circle mr-2"></i>View Details</button>', $detailbtn);
                    $editbtn = '<a href="'.route("products.edit", $row->id).'" class="dropdown-item editbtn"><i class="fas fa-edit mr-2"></i>Edit</a>';
                    $deletebtn = '<a data-id="'.$row->id.'" data-route="'.route('products.destroy', $row->id).'" href="javascript:void(0)" id="deletebtn" class="dropdown-item text-danger"><i class="fas fa-trash mr-2"></i>Delete</a>';
                    if (!auth()->user()->hasPermissionTo('edit-product')) {
                        $editbtn = '';
                    }
                    if (!auth()->user()->hasPermissionTo('destroy-purchase')) {
                        $deletebtn = '';
                    }
                    $menuItems = $statusMenuItem.$detailbtn;
                    if ($editbtn || $deletebtn) {
                        $menuItems .= '<div class="dropdown-divider"></div>'.$editbtn.$deletebtn;
                    }

                    return '<div class="btn-group product-action-cell '.$stateClass.'" data-active="'.($isActive ? '1' : '0').'"><button type="button" class="btn btn-sm btn-secondary dropdown-toggle product-action-button product-status-action-button '.$stateClass.'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Product actions"><i class="fa fa-ellipsis-v"></i></button><div class="dropdown-menu dropdown-menu-right">'.$menuItems.'</div></div>';
                })
                ->rawColumns(['product','status','action'])
                ->make(true);
        }
        return view('admin.products.index', array_merge(
            compact('title'),
            (new PurchaseController)->purchaseFormData($request)
        ));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return redirect()->route('purchases.create');

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $existingProduct = Product::where('purchase_id', $request->product)->first();

        $barcodeRules = ['nullable', 'string', 'max:100'];
        if ($existingProduct) {
            $barcodeRules[] = Rule::unique('products', 'barcode')->ignore($existingProduct->id);
        } else {
            $barcodeRules[] = 'unique:products,barcode';
        }

        $this->validate($request,[
            'product'=>'required|max:200',
            'price'=>'required|min:1',
            'discount'=>'nullable',
            'barcode'=>$barcodeRules,
            'description'=>'nullable|max:255',
        ]);

          $discount = $request->input('discount') ?? 0;
        $price = $request->price;
          if($discount > 0){
              $price = $discount * $request->price;
        }
        $barcode = $request->barcode ?: Str::upper(Str::random(10));

        if ($existingProduct) {
            $existingProduct->update([
                'price'=>$price,
                'discount'=>$discount,
                'barcode'=>$barcode,
                'description'=>$request->description,
            ]);
            $notification = notify("Product has been updated");
        } else {
            Product::create([
                'purchase_id'=>$request->product,
                'price'=>$price,
                'discount'=>$discount,
                'barcode'=>$barcode,
                'description'=>$request->description,
                'is_active'=>true,
            ]);
            $notification = notify("Product has been added");
        }

        return redirect()->route('products.index')->with($notification);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \app\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $title = 'edit product';
        $purchases = Purchase::with('purchaseProduct')->get();

        // Build product map for client side: purchase_id => product data or null
        $purchaseMap = $purchases->mapWithKeys(function ($purchase) {
            $prod = $purchase->purchaseProduct;
            return [$purchase->id => $prod ? $prod->toArray() : null];
        })->toArray();

        return view('admin.products.edit',compact(
            'title','product','purchases','purchaseMap'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \app\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $this->validate($request,[
            'product'=>'required|max:200',
            'price'=>'required',
            'discount'=>'nullable',
            'barcode'=>['nullable','string','max:100',Rule::unique('products','barcode')->ignore($product->id)],
            'description'=>'nullable|max:255',
        ]);

          $discount = $request->input('discount') ?? 0;
        $price = $request->price;
          if($discount > 0){
              $price = $discount * $request->price;
        }

        $barcode = $request->barcode ?: ($product->barcode ?: Str::upper(Str::random(10)));

        $product->update([
            'purchase_id'=>$request->product,
            'price'=>$price,
            'discount'=>$discount,
            'barcode'=>$barcode,
            'description'=>$request->description,
        ]);
        $notification = notify('product has been updated');
        return redirect()->route('products.index')->with($notification);
    }

/**
     * Toggle whether a product is Active (visible on the POS) or Not Active.
     *
     * Called from the checkbox in the Products table action column.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \app\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function toggleStatus(Request $request, Product $product)
    {
        abort_unless(auth()->user()->hasPermissionTo('edit-product'), 403);

        $active = $request->boolean('is_active');

        $product->update([
            'is_active' => $active,
        ]);

        return response()->json([
            'success'   => true,
            'is_active' => (bool) $product->is_active,
            'message'   => $active
                ? 'Product is now Active and visible on the POS.'
                : 'Product is now Not Active and hidden from the POS.',
        ]);
    }
     /**
     * Display a listing of expired resources.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function expired(Request $request){
        $title = "expired Products";
        Product::markExpiredProducts();
        if($request->ajax()){
            $products = Product::with(['purchase.category'])->where('expired', true)->get();

            return DataTables::of($products)
                ->addColumn('product',function($product){
                    if(!empty($product->purchase)){
                        return $product->purchase->product;
                    }
                })

                ->addColumn('category',function($product){
                    $category = null;
                    if(!empty($product->purchase->category)){
                        $category = $product->purchase->category->name;
                    }
                    return $category;
                })
                ->addColumn('price',function($product){
                    return settings('app_currency','$').' '. $product->price;
                })
                ->addColumn('quantity',function($product){
                    if(!empty($product->purchase)){
                        return $product->purchase->quantity;
                    }
                })
                ->addColumn('action', function ($row) {
                    $purchase = $row->purchase;
                    $detailbtn = '<button type="button" class="dropdown-item expired-detail-btn" '
                        . 'data-details="'.htmlspecialchars(json_encode([
                            'product'          => optional($purchase)->product,
                            'image'            => optional($purchase)->image ? $purchase->image_url : asset('assets/img/productnoimage.png'),
                            'category'         => optional(optional($purchase)->category)->name,
                            'supplier'         => optional(optional($purchase)->supplier)->name,
                            'price'            => settings('app_currency', '$').' '.$row->price,
                            'quantity'         => optional($purchase)->quantity,
                            'expiry'           => optional($purchase)->expiry_date ? date_format(date_create($purchase->expiry_date), 'd M, Y') : 'No expiry',
                            'purchased'        => optional(optional($purchase)->created_at)->format('d M, Y'),
                            'item_quantity'    => optional($purchase)->item_quantity,
                            'packaging_box'    => optional($purchase)->packaging_box,
                            'quantity_per_box' => optional($purchase)->quantity_per_box,
                        ]), ENT_QUOTES, 'UTF-8').'">'
                        . '<i class="fas fa-info-circle mr-2"></i>View Details</button>';
                    $editbtn = '<a href="'.route("products.edit", $row->id).'" class="dropdown-item editbtn"><i class="fas fa-edit mr-2"></i>Edit</a>';
                    $deletebtn = '<a data-id="'.$row->id.'" data-route="'.route('products.destroy', $row->id).'" href="javascript:void(0)" id="deletebtn" class="dropdown-item text-danger"><i class="fas fa-trash mr-2"></i>Delete</a>';
                    if (!auth()->user()->hasPermissionTo('edit-product')) {
                        $editbtn = '';
                    }
                    if (!auth()->user()->hasPermissionTo('destroy-purchase')) {
                        $deletebtn = '';
                    }
                    $menuItems = $detailbtn;
                    if ($editbtn || $deletebtn) {
                        $menuItems .= '<div class="dropdown-divider"></div>'.$editbtn.$deletebtn;
                    }
                    return '<div class="btn-group"><button type="button" class="btn btn-sm btn-secondary dropdown-toggle product-action-button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Product actions"><i class="fa fa-ellipsis-v"></i></button><div class="dropdown-menu dropdown-menu-right">'.$menuItems.'</div></div>';
                })
                ->rawColumns(['product','action'])
                ->make(true);
        }

        return view('admin.products.expired',compact(
            'title',
        ));
    }

    /**
     * Display a listing of out of stock resources.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function outstock(Request $request){
        $title = "outstocked Products";
        if($request->ajax()){
            $products = Product::with(['purchase.category'])->whereHas('purchase', function($q){
                return $q->where('quantity', '<=', 0);
            })->get();
            return DataTables::of($products)
                ->addColumn('product',function($product){
                    if(!empty($product->purchase)){
                        return $product->purchase->product;
                    }
                })

                ->addColumn('category',function($product){
                    $category = null;
                    if(!empty($product->purchase->category)){
                        $category = $product->purchase->category->name;
                    }
                    return $category;
                })
                ->addColumn('price',function($product){
                    return settings('app_currency','$').' '. $product->price;
                })
                ->addColumn('quantity',function($product){
                    if(!empty($product->purchase)){
                        return $product->purchase->quantity;
                    }
                })
                ->addColumn('action', function ($row) {
                    $purchase = $row->purchase;
                    $detailbtn = '<button type="button" class="dropdown-item outstock-detail-btn" '
                        . 'data-details="'.htmlspecialchars(json_encode([
                            'product'          => optional($purchase)->product,
                            'image'            => optional($purchase)->image ? $purchase->image_url : asset('assets/img/productnoimage.png'),
                            'category'         => optional(optional($purchase)->category)->name,
                            'supplier'         => optional(optional($purchase)->supplier)->name,
                            'price'            => settings('app_currency', '$').' '.$row->price,
                            'quantity'         => optional($purchase)->quantity,
                            'expiry'           => optional($purchase)->expiry_date ? date_format(date_create($purchase->expiry_date), 'd M, Y') : 'No expiry',
                            'purchased'        => optional(optional($purchase)->created_at)->format('d M, Y'),
                            'item_quantity'    => optional($purchase)->item_quantity,
                            'packaging_box'    => optional($purchase)->packaging_box,
                            'quantity_per_box' => optional($purchase)->quantity_per_box,
                        ]), ENT_QUOTES, 'UTF-8').'">'
                        . '<i class="fas fa-info-circle mr-2"></i>View Details</button>';
                    $editbtn = '<a href="'.route("products.edit", $row->id).'" class="dropdown-item editbtn"><i class="fas fa-edit mr-2"></i>Edit</a>';
                    $deletebtn = '<a data-id="'.$row->id.'" data-route="'.route('products.destroy', $row->id).'" href="javascript:void(0)" id="deletebtn" class="dropdown-item text-danger"><i class="fas fa-trash mr-2"></i>Delete</a>';
                    if (!auth()->user()->hasPermissionTo('edit-product')) {
                        $editbtn = '';
                    }
                    if (!auth()->user()->hasPermissionTo('destroy-purchase')) {
                        $deletebtn = '';
                    }
                    $menuItems = $detailbtn;
                    if ($editbtn || $deletebtn) {
                        $menuItems .= '<div class="dropdown-divider"></div>'.$editbtn.$deletebtn;
                    }
                    return '<div class="btn-group"><button type="button" class="btn btn-sm btn-secondary dropdown-toggle product-action-button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Product actions"><i class="fa fa-ellipsis-v"></i></button><div class="dropdown-menu dropdown-menu-right">'.$menuItems.'</div></div>';
                })
                ->rawColumns(['product','action'])
                ->make(true);
        }
        $product = Purchase::where('quantity', '<=', 0)->first();
        return view('admin.products.outstock',compact(
            'title',
        ));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $purchase = $product->purchase;

        DB::transaction(function () use ($product, $purchase) {
            ArchivedSupplier::create([
                'supplier_name' => 'Product: '.($purchase->product ?? 'Unknown'),
                'archived_at' => now(),
                'data' => [
                    'type' => 'product',
                    'product' => $product->toArray(),
                    'purchase' => $purchase ? $purchase->toArray() : null,
                ],
            ]);

            $product->delete();
        });

        return response()->json(['success' => true]);
    }
}
