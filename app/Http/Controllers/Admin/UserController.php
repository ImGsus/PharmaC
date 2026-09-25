<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use App\Services\ArchiveService;
use App\Services\OrganizedFileStorage;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $title = 'users';
		$defaultAvatars = ['femaleperson.jpg', 'maleperson.jpg'];
        if ($request->ajax()) {
            $users = User::get();
            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('created_at', function ($category) {
                    return date_format(date_create($category->created_at), "d M,Y");
                })
                ->addColumn('avatar', function ($user) use ($defaultAvatars) {
                    $src = asset('assets/img/avatar.png');
                    if (!empty($user->avatar)) {
						$src = in_array($user->avatar, $defaultAvatars, true)
							? asset('assets/img/'.$user->avatar)
                            : url('storage/system/profiles/'.$user->avatar);
                    } elseif (in_array(strtolower((string) $user->gender), ['male', 'female'], true)) {
                        $src = asset('assets/img/'.strtolower($user->gender).'person.jpg');
                    }
                    return '<img src="'.$src.'" class="avatar-img rounded-circle" width="50" />';
                })
                ->addColumn('role', function ($row) {
                    foreach ($row->getRoleNames() as $role) {
                        return '<span>'.$role.'</span>';
                    }
                })
                ->addColumn('action', function ($row) use ($defaultAvatars) {
                    $role = $row->getRoleNames()->first() ?: 'Not assigned';
                    $avatar = $role === 'super-admin'
                        ? asset('assets/img/ADMIN.jpg')
                        : (!empty($row->avatar)
                        ? (in_array($row->avatar, $defaultAvatars, true) ? asset('assets/img/'.$row->avatar) : url('storage/system/profiles/'.$row->avatar))
                        : (in_array(strtolower((string) $row->gender), ['male', 'female'], true)
                            ? asset('assets/img/'.strtolower($row->gender).'person.jpg')
                            : asset('assets/img/avatar.png')));
                    $details = htmlspecialchars(json_encode([
                        'id' => $row->id,
                        'name' => $row->name,
                        'email' => $row->email,
                        'gender' => $row->gender,
                        'username' => $row->username,
                        'role' => $role,
                        'avatar' => $avatar,
                        'created_at' => $row->created_at ? date_format(date_create($row->created_at), "d M,Y") : '',
                        'email_verified_at' => $row->email_verified_at ? date_format(date_create($row->email_verified_at), "d M,Y") : '',
                    ]), ENT_QUOTES, 'UTF-8');
                    $viewbtn = '<a href="javascript:void(0)" class="dropdown-item user-detail-btn" data-details="'.$details.'"><i class="fas fa-info-circle mr-2"></i>View Details</a>';
                    $editbtn = '<a href="javascript:void(0)" class="dropdown-item editbtn user-edit-btn" data-details="'.$details.'"><i class="fas fa-edit mr-2"></i>Edit</a>';
                    $deletebtn = '<a data-id="'.$row->id.'" data-route="'.route('users.destroy', $row->id).'" href="javascript:void(0)" id="deletebtn" class="dropdown-item text-danger"><i class="fas fa-trash mr-2"></i>Delete</a>';
                    if (!auth()->user()->hasPermissionTo('edit-user')) {
                        $editbtn = '';
                    }
                    if (!auth()->user()->hasPermissionTo('destroy-user')) {
                        $deletebtn = '';
                    }
                    return '<div class="btn-group"><button type="button" class="btn btn-sm btn-secondary dropdown-toggle user-action-button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="User actions"><i class="fa fa-ellipsis-v"></i></button><div class="dropdown-menu dropdown-menu-right">'.$viewbtn.$editbtn.'<div class="dropdown-divider"></div>'.$deletebtn.'</div></div>';
                })
                ->rawColumns(['avatar','role','action'])
                ->make(true);
        }
        $roles = Role::get();
        return view('admin.users.index', compact('title', 'roles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'create user';
        $roles = Role::get();
        return view('admin.users.create', compact('title','roles'));
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
            'first_name'=>'required_without:name|max:50',
            'last_name'=>'required_without:name|max:50',
            'name'=>'nullable|max:100',
            'email'=>'required|email',
            'username'=>'nullable|max:100|unique:users,username',
            'gender'=>'nullable|in:Male,Female',
            'role'=>'required',
            'password'=>'required|confirmed|max:200',
            'avatar'=>'nullable|file|image|mimes:jpg,jpeg,gif,png',
        ]);
        $imageName = null;
        if ($request->hasFile('avatar')) {
            $imageName = app(OrganizedFileStorage::class)->store($request->avatar, 'profiles');
        }
        $user = User::create([
            'name' => $request->filled('name') ? $request->name : trim($request->first_name.' '.$request->last_name),
            'username' => $request->username,
            'gender' => $request->gender,
            'email' => $request->email,
            'avatar' => $imageName,
            'password' => Hash::make($request->password),
        ]);
        $user->assignRole($request->role);
        $notifiation = notify('user created successfully');
        return redirect()->route('users.index')->with($notifiation);
    }

   
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \app\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        $title = "edit user";
        $roles = Role::get();
        return view('admin.users.edit',compact(
            'title','roles','user'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \app\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $this->validate($request,[
            'name'=>'required|max:100',
            'email'=>'required|email',
            'role'=>'required',
            'password'=>'nullable|confirmed|max:200',
            'avatar'=>'nullable|file|image|mimes:jpg,jpeg,gif,png',
        ]);
        $imageName = $user->avatar;
        $password = $user->password;

        if ($request->hasFile('avatar')) {
            if (!empty($user->avatar)) {
                app(OrganizedFileStorage::class)->delete('profiles', $user->avatar);
                $legacyPath = public_path('storage/users/'.basename($user->avatar));
                if (is_file($legacyPath)) @unlink($legacyPath);
            }

            $imageName = app(OrganizedFileStorage::class)->store($request->avatar, 'profiles');
        }

        if (!empty($request->password) && ($user->password != $request->password)) {
            $password = Hash::make($request->password);
        }
        $user->update([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'avatar' => $imageName,
            'password' => $password,
        ]);
        foreach($user->getRoleNames() as $userRole){
            $user->removeRole($userRole);
        }
        $user->assignRole($request->role);
        $notification = notify('user updated successfully');
        return redirect()->route('users.index')->with($notification);
    }

    public function profile(){
        $title = 'user profile';
        $roles = Role::get();
        return view('admin.users.profile',compact(
            'title','roles'
        ));
    }

    public function sendTwoFactorCode(Request $request)
    {
        $user = $request->user();
        if (!$user->email_verified_at) {
            return response()->json(['message' => 'Verify your email before enabling two-step verification.'], 422);
        }

        $code = (string) random_int(100000, 999999);
        $request->session()->put('two_factor_setup', [
            'user_id' => $user->id,
            'code' => Hash::make($code),
            'expires_at' => now()->addMinutes(10)->timestamp,
        ]);
        Mail::raw("Your PharMac two-step verification code is: {$code}\n\nThis code expires in 10 minutes.", function ($message) use ($user) {
            $message->to($user->email)->subject('Enable PharMac two-step verification');
        });

        return response()->json(['message' => 'A verification code was sent to your email.']);
    }

    public function enableTwoFactor(Request $request)
    {
        $this->validate($request, ['code' => 'required|digits:6']);
        $pending = $request->session()->get('two_factor_setup');
        if (!$pending || $pending['user_id'] !== $request->user()->id || now()->timestamp > $pending['expires_at'] || !Hash::check($request->code, $pending['code'])) {
            return response()->json(['message' => 'The verification code is invalid or expired.'], 422);
        }

        $request->user()->update(['two_factor_enabled' => true]);
        $request->session()->forget('two_factor_setup');
        return response()->json(['message' => 'Two-step verification is now enabled.']);
    }

    public function disableTwoFactor(Request $request)
    {
        $request->user()->update(['two_factor_enabled' => false]);
        $request->session()->forget('two_factor_pending');
        return back()->with(notify('Two-step verification has been disabled.'));
    }

    public function updateProfile(Request $request,User $user){
        $this->validate($request,[
            'name' => 'required|min:5|max:200',
            'email' => 'required|email',
            'username' => 'nullable|min:3|max:200',
            'avatar' => 'nullable|file|image|mimes:jpg,jpeg,png,gif'
        ]);
        $imageName = $user->avatar;
        if ($request->hasFile('avatar')) {
            if (!empty($user->avatar)) {
                app(OrganizedFileStorage::class)->delete('profiles', $user->avatar);
                $legacyPath = public_path('storage/users/'.basename($user->avatar));
                if (is_file($legacyPath)) @unlink($legacyPath);
            }

            $firstName = Str::before(trim($request->name), ' ');
            $firstName = preg_replace('/[^A-Za-z0-9_-]/', '', $firstName) ?: 'user';
            $imageName = app(OrganizedFileStorage::class)->store($request->avatar, 'profiles', $firstName.'.'.$request->avatar->extension());
        }
        $user->update([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'avatar' => $imageName,
        ]);
        $notification = notify('profile updated successfully');
        return redirect()->route('profile')->with($notification);
    }

    /**
     * Update current user password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request, User $user)
    {
        $this->validate($request, [
            'current_password'=>'required',
            'password'=>'required|max:200|confirmed',
        ]);
        $verify_password = password_verify($request->current_password, $user->password);
        if ($verify_password) {
            $user->update(['password'=>Hash::make($request->password)]);
            $notification = notify('User password updated successfully!!!');
            $logout = auth()->logout();
            return back()->with($notification, $logout);
        } elseif(!$verify_password) {
            $notification = notify("Incorrect Old Password!!!",'danger');
            return back()->with($notification);
        }
    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  \Illuminate\Http\Request $request
    * @return \Illuminate\Http\Response
    */
    public function destroy(Request $request)
    {
        $user = User::findOrFail($request->id);

        ArchiveService::record($user, 'User: '.$user->name);

        if (!empty($user->avatar)) {
            app(OrganizedFileStorage::class)->delete('profiles', $user->avatar);
            $legacyPath = public_path('storage/users/'.basename($user->avatar));
            if (is_file($legacyPath)) @unlink($legacyPath);
        }

        $user->delete();

        return response()->json(['success' => true]);
    }
}
