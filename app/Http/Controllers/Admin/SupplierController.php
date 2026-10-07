<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArchivedSupplier;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $title = 'suppliers';
        if($request->ajax()){
            $suppliers = Supplier::get();
            return DataTables::of($suppliers)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $products = Purchase::with(['category', 'purchaseProduct'])
                        ->where('supplier_id', $row->id)
                        ->latest()
                        ->get()
                        ->map(function ($purchase) use ($row) {
                            $currentPrice = optional($purchase->purchaseProduct)->price;
                            $formattedPrice = settings('app_currency', '$').' '.($currentPrice !== null ? $currentPrice : $purchase->cost_price);
                            return [
                                'id' => $purchase->id,
                                'product' => $purchase->product,
                                'category' => optional($purchase->category)->name,
                                'supplier' => $row->name,
                                'quantity' => $purchase->quantity,
                                'cost' => $formattedPrice,
                                'price' => $formattedPrice,
                                'item_quantity' => $purchase->item_quantity,
                                'packaging_box' => $purchase->packaging_box,
                                'quantity_per_box' => $purchase->quantity_per_box,
                                'expiry' => $purchase->expiry_date
                                    ? date_format(date_create($purchase->expiry_date), 'd M, Y')
                                    : 'No expiry',
                                'purchased' => optional($purchase->created_at)->format('d M, Y'),
                                'image' => $purchase->image_url,
                            ];
                        });
                    $detailbtn = '<button type="button" class="dropdown-item supplier-detail-btn" data-supplier="'.htmlspecialchars($row->name, ENT_QUOTES, 'UTF-8').'" data-products="'.htmlspecialchars($products->toJson(), ENT_QUOTES, 'UTF-8').'" ><i class="fas fa-info-circle mr-2"></i>View Details</button>';
                    $editbtn = '<a href="'.route("suppliers.edit", $row->id).'" data-row-action-name="Supplier" data-row-action-table="supplier-table" data-row-action-list-path="'.route('suppliers.index').'" class="dropdown-item editbtn row-action-iframe-edit"><i class="fas fa-edit mr-2"></i>Edit</a>';
                    $deletebtn = '<a data-id="'.$row->id.'" data-route="'.route('suppliers.destroy',$row->id).'" href="javascript:void(0)" id="deletebtn" class="dropdown-item text-danger"><i class="fas fa-trash mr-2"></i>Delete</a>';
                    if (!auth()->user()->hasPermissionTo('edit-supplier')) {
                        $editbtn = '';
                    }
                    if (!auth()->user()->hasPermissionTo('destroy-supplier')) {
                        $deletebtn = '';
                    }
                    $supplierName = htmlspecialchars($row->name, ENT_QUOTES, 'UTF-8');
                    return '<div class="btn-group"><button type="button" class="btn btn-sm btn-secondary dropdown-toggle supplier-action-button row-action-modal-trigger" data-action-title="Supplier Actions" data-context-label="Name" data-context-value="'.$supplierName.'" aria-haspopup="true" aria-expanded="false" aria-label="Supplier actions"><i class="fa fa-ellipsis-v"></i></button><div class="dropdown-menu dropdown-menu-right">'.$detailbtn.'<div class="dropdown-divider"></div>'.$editbtn.$deletebtn.'</div></div>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    
        return view('admin.suppliers.index',compact(
            'title'
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'create supplier';
        return view('admin.suppliers.create',compact(
            'title'
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
        try {
            $this->validate($request,[
                'name'=>'required|min:10|max:255',
                'product'=>'required',
                'email'=>'nullable|email|string',
                'phone'=>'nullable|min:10|max:20',
                'company'=>'nullable|max:200|required',
                'address'=>'nullable|required|max:200',
                'comment' =>'nullable|max:255',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // When submitted from the Suppliers list modal, send the user back
            // to the list so the modal can re-open with errors + old input.
            if ($request->has('from_suppliers_modal')) {
                return redirect()->route('suppliers.index')
                    ->withErrors($e->validator)
                    ->withInput()
                    ->with('open_add_supplier_modal', true);
            }
            throw $e;
        }
        if ($request->input('form_submit') === 'next') {
            session(['pending_supplier' => $request->only([
                'name', 'email', 'phone', 'company', 'address', 'product', 'comment',
            ])]);
            return redirect()->route('purchases.create', [
                'product' => $request->product,
            ]);
        }

        $supplier = Supplier::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'phone'=>$request->phone,
            'company'=>$request->company,
            'address'=>$request->address,
            'product'=>$request->product,
            'comment'=>$request->comment,
        ]);

        $notification = notify("Supplier has been updated");
        return redirect()->route('suppliers.index')->with($notification);
    }

    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \app\Models\Supplier $supplier
     * @return \Illuminate\Http\Response
     */
    public function edit(Supplier $supplier)
    {
        $title = 'edit supplier';
        return view('admin.suppliers.edit',compact(
            'title','supplier'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \app\Models\Supplier $supplier
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Supplier $supplier)
    {
        $this->validate($request,[
            'name'=>'required|min:10|max:255',
            'product'=>'required',
            'email'=>'nullable|email|string',
            'phone'=>'nullable|min:10|max:20',
            'company'=>'nullable|max:200|required',
            'address'=>'nullable|required|max:200',
            'comment' =>'nullable|max:255',
        ]);
        $supplier->update([
            'name'=>$request->name,
            'email'=>$request->email,
            'phone'=>$request->phone,
            'company'=>$request->company,
            'address'=>$request->address,
            'product'=>$request->product,
            'comment'=>$request->comment,
        ]);
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Supplier has been updated']);
        }
        $notification = notify("Supplier has been added");
        return redirect()->route('suppliers.index')->with($notification);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        DB::transaction(function () use ($request) {
            $supplier = Supplier::findOrFail($request->id);
            $purchases = Purchase::where('supplier_id', $supplier->id)->get();
            $purchaseData = $purchases->map(function ($purchase) {
                $products = Product::withTrashed()->where('purchase_id', $purchase->id)->get();

                return [
                    'purchase' => $purchase->toArray(),
                    'products' => $products->toArray(),
                ];
            })->values()->all();

            ArchivedSupplier::create([
                'supplier_name' => $supplier->name,
                'archived_at' => now(),
                'data' => [
                    'supplier' => $supplier->toArray(),
                    'purchases' => $purchaseData,
                ],
            ]);

            foreach ($purchases as $purchase) {
                Product::withTrashed()->where('purchase_id', $purchase->id)->forceDelete();
                $purchase->delete();
            }

            $supplier->delete();
        });

        return response()->json(['success' => true]);
    }
}
