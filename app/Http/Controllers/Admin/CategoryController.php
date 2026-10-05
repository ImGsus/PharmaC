<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ArchiveService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $title = 'categories';
        if($request->ajax()){
            $categories = Category::get();
            return DataTables::of($categories)
                    ->addIndexColumn()
                    ->addColumn('created_at',function($category){
                        return date_format(date_create($category->created_at),"d M,Y");
                    })
                            ->addColumn('description', function($category){
                                return $category->description ? e(\Illuminate\Support\Str::limit($category->description, 120)) : '' ;
                            })
                    ->addColumn('action',function ($row){
                        $descAttr = htmlspecialchars($row->description ?? '', ENT_QUOTES);
                        $detailbtn = '<button type="button" class="dropdown-item category-description-btn" data-description="'.$descAttr.'"><i class="fas fa-info-circle mr-2"></i>View Description</button>';
                        $editbtn = '<a data-id="'.$row->id.'" data-name="'.e($row->name).'" data-description="'. $descAttr .'" data-fixed-key="'. e($row->fixed_key) .'" data-no-expiry="'.($row->no_expiry ? '1' : '0').'" href="javascript:void(0)" class="dropdown-item editbtn"><i class="fas fa-edit mr-2"></i>Edit</a>';
                        $deletebtn = '<a data-id="'.$row->id.'" data-route="'.route('categories.destroy',$row->id).'" href="javascript:void(0)" id="deletebtn" class="dropdown-item text-danger"><i class="fas fa-trash mr-2"></i>Delete</a>';
                        if(!auth()->user()->hasPermissionTo('edit-category')){
                            $editbtn = '';
                        }
                        if(!auth()->user()->hasPermissionTo('destroy-category')){
                            $deletebtn = '';
                        }
                        $menuItems = $detailbtn;
                        if ($editbtn || $deletebtn) {
                            $menuItems .= '<div class="dropdown-divider"></div>'.$editbtn.$deletebtn;
                        }

                        return '<div class="btn-group"><button type="button" class="btn btn-sm btn-secondary dropdown-toggle category-action-button" data-category-name="'.e($row->name).'" aria-haspopup="dialog" aria-expanded="false" aria-label="Category actions for '.e($row->name).'"><i class="fa fa-ellipsis-v"></i></button><div class="dropdown-menu dropdown-menu-right">'.$menuItems.'</div></div>';
                    })
                    ->rawColumns(['action'])
                    ->make(true);
        }
        $existingFixedKeys = Category::whereNotNull('fixed_key')->pluck('fixed_key');

        return view('admin.products.categories', compact(
            'title',
            'existingFixedKeys'
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
        $fixedKey = $request->input('fixed_key');
        $fixedNoExpiryKeys = ['medical-devices'];
        $existingCategory = null;

        if (!empty($fixedKey)) {
            $existingCategory = Category::where('fixed_key', $fixedKey)->first();
        }

        $nameRule = 'required|max:100';
        $nameRule .= $existingCategory ? '|unique:categories,name,'.$existingCategory->id : '|unique:categories,name';

        $this->validate($request, [
            'name' => $nameRule,
            'description' => 'nullable|string|max:1000',
            'fixed_key' => 'nullable|string|max:100',
            'no_expiry' => 'nullable|boolean',
        ]);

        $data = $request->only(['name', 'description', 'fixed_key', 'no_expiry']);
        $data['no_expiry'] = in_array($fixedKey, $fixedNoExpiryKeys, true) || $request->boolean('no_expiry');

        if (!empty($fixedKey)) {
            Category::updateOrCreate(
                ['fixed_key' => $fixedKey],
                $data
            );
        } else {
            Category::create($data);
        }

        $notification = array("Category has been added");
        return back()->with($notification);
    }

    

    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $category = Category::findOrFail($request->id);
        $fixedNoExpiryKeys = ['medical-devices'];

        $this->validate($request, [
            'name' => 'required|max:100|unique:categories,name,'.$category->id,
            'description' => 'nullable|string|max:1000',
            'fixed_key' => 'nullable|string|max:100|unique:categories,fixed_key,'.$category->id,
            'no_expiry' => 'nullable|boolean',
        ]);

        $data = $request->only(['name', 'description', 'fixed_key', 'no_expiry']);
        $data['no_expiry'] = in_array($data['fixed_key'] ?? $category->fixed_key, $fixedNoExpiryKeys, true)
            || $request->boolean('no_expiry');

        if ($category->fixed_key && empty($data['fixed_key'])) {
            $data['fixed_key'] = $category->fixed_key;
        }

        $category->update($data);
        $notification = notify("Category has been updated");
        return back()->with($notification);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $category = Category::findOrFail($request->id);
        ArchiveService::record($category, 'Category: '.$category->name);
        return $category->delete();
    }
}
