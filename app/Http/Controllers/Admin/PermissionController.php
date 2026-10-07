<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use App\Services\ArchiveService;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $title = 'permissions';
        if ($request->ajax()){
            $permissions = Permission::get();
            return DataTables::of($permissions)
                    ->addIndexColumn()
                    ->addColumn('created_at',function($row){
                        return date_format(date_create($row->created_at),'D M Y');
                    })
                    ->addColumn('action',function ($row){
                        $editbtn = '<a data-id="'.$row->id.'" data-name="'.$row->name.'" href="javascript:void(0)" class="dropdown-item editbtn"><i class="fa fa-edit mr-2"></i>Edit</a>';
                        $deletebtn = '<a data-id="'.$row->id.'" data-route="'.route('permissions.destroy',$row->id).'" href="javascript:void(0)" id="deletebtn" class="dropdown-item text-danger"><i class="fa fa-trash mr-2"></i>Delete</a>';
                        if(!auth()->user()->hasPermissionTo('edit-permission')){
                            $editbtn = '';
                        }
                        if(!auth()->user()->hasPermissionTo('destroy-permission')){
                            $deletebtn = '';
                        }
                        $permissionName = htmlspecialchars($row->name, ENT_QUOTES, 'UTF-8');
                        return '<div class="btn-group"><button type="button" class="btn btn-sm btn-secondary dropdown-toggle permission-action-button row-action-modal-trigger" data-action-title="Permission Actions" data-context-label="Name" data-context-value="'.$permissionName.'" aria-haspopup="true" aria-expanded="false" aria-label="Permission actions"><i class="fa fa-ellipsis-v"></i></button><div class="dropdown-menu dropdown-menu-right">'.$editbtn.'<div class="dropdown-divider"></div>'.$deletebtn.'</div></div>';
                    })
                    ->rawColumns(['action'])
                    ->make(true);
        }
        
        return view('admin.roles.permissions',compact(
            'title',
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
        $this->validate($request,[
            'permission' => 'required|min:3|max:255'
        ]);
        foreach (explode(',',$request->permission) as $permission){
            $permission = Permission::create(['name' => $permission]);
            $permission->assignRole('super-admin');
        }
        $notification = notify("permission created");
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
        $this->validate($request,[
            'permission' => 'required|min:3|max:255'
        ]);
        $permission = Permission::findOrFail($request->id);
        $permission->update([
            'name' => $request->permission,
        ]);
        $notification = notify('permission updated');
        return back()->with($notification);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $permission = Permission::findOrFail($request->id);
        ArchiveService::record($permission, 'Permission: '.$permission->name);
        return $permission->delete();
    }
}
