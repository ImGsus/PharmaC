<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
    public function index(){
        $title = 'register';
        return view('admin.auth.register',compact('title'));
    }

    public function store(Request $request){
        $this->validate($request ,[
            'first_name'=>'required|max:50',
            'last_name'=>'required|max:50',
            'username'=>'required|max:100|unique:users,username',
            'gender'=>'required|in:Male,Female',
            'email'=>'required|email|unique:users,email',
            'password'=>'required|max:200|confirmed',
        ]);
        
        $user = User::create([
            'name'=>trim($request->first_name.' '.$request->last_name),
            'username'=>$request->username,
            'gender'=>$request->gender,
            'avatar'=>strtolower($request->gender) === 'female' ? 'femaleperson.jpg' : 'maleperson.jpg',
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
        ]);
        $user->assignRole('sales-person');
        auth()->attempt($request->only('email','password'));
        return redirect()->route('verification.choice');
    }

    public function choice(Request $request)
    {
        return view('admin.auth.verification-choice', [
            'title' => 'verify account',
            'existingAccount' => $request->query('source') === 'security',
        ]);
    }

    public function sendCode(Request $request)
    {
        $user = $request->user();
        $code = (string) random_int(100000, 999999);

        $request->session()->put('email_verification', [
            'user_id' => $user->id,
            'code' => Hash::make($code),
            'expires_at' => now()->addMinutes(10)->timestamp,
        ]);

        Mail::raw("Your PharMac verification code is: {$code}\n\nThis code expires in 10 minutes.", function ($message) use ($user) {
            $message->to($user->email)->subject('Verify your PharMac account');
        });

        if ($request->expectsJson()) {
            return response()->json(['message' => 'A verification code was sent to your email.']);
        }

        return redirect()->route('verification.form')->with('message', 'A verification code was sent to your email.');
    }

    public function form(Request $request)
    {
        return view('admin.auth.verify', [
            'title' => 'verify account',
            'email' => $request->user()->email,
        ]);
    }

    public function verify(Request $request)
    {
        $validator = validator($request->all(), ['code' => 'required|digits:6']);
        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $validator->errors()->first('code')], 422);
            }
            return back()->withInput()->withErrors($validator);
        }

        $pending = $request->session()->get('email_verification');

        if (!$pending || $pending['user_id'] !== $request->user()->id || now()->timestamp > $pending['expires_at'] || !Hash::check($request->code, $pending['code'])) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'The verification code is invalid or expired.'], 422);
            }
            return back()->withInput()->withErrors(['code' => 'The verification code is invalid or expired.']);
        }

        $request->user()->forceFill(['email_verified_at' => now()])->save();
        $request->session()->forget('email_verification');

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Your account has been verified.']);
        }

        return redirect()->route('dashboard')->with(notify('Your account has been verified.'));
    }
}
