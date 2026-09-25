<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function index(){
        $title = 'login';
        return view('admin.auth.login',compact('title'));
    }

    public function login(Request $request){
        $this->validate($request ,[
              'email'=>'required',
            'password'=>'required',
        ]);
       $authenticate = auth()->attempt($request->only('email','password'));
       if (!$authenticate){
           if ($request->expectsJson()) {
               return response()->json([
                   'message' => "We couldn't find an account with those credentials.",
               ], 422);
           }
           return back()->withInput($request->only('email','password'))->with('login_error',"We couldn't find an account with those credentials.");
       }
       $user = auth()->user();
       if ($user->two_factor_enabled) {
           $code = (string) random_int(100000, 999999);
           $request->session()->put('two_factor_pending', [
               'user_id' => $user->id,
               'code' => Hash::make($code),
               'expires_at' => now()->addMinutes(10)->timestamp,
           ]);
           Mail::raw("Your PharMac login verification code is: {$code}\n\nThis code expires in 10 minutes.", function ($message) use ($user) {
               $message->to($user->email)->subject('Your PharMac login verification code');
           });
           auth()->logout();
           $request->session()->flash('two_factor_prompt', 'A verification code was sent to your verified email address.');
           if ($request->expectsJson()) {
               return response()->json(['redirect' => route('login')]);
           }
           return redirect()->route('login')->with('two_factor_prompt', 'A verification code was sent to your verified email address.');
       }
       if ($request->expectsJson()) {
           return response()->json(['redirect' => route('dashboard')]);
       }
       return redirect()->route('dashboard');

    }

    public function verifyTwoFactor(Request $request)
    {
        $this->validate($request, ['code' => 'required|digits:6']);
        $pending = $request->session()->get('two_factor_pending');

        if (!$pending || now()->timestamp > $pending['expires_at'] || !Hash::check($request->code, $pending['code'])) {
            return back()->with('two_factor_error', 'The verification code is invalid or expired.');
        }

        $user = \App\Models\User::find($pending['user_id']);
        if (!$user || !$user->two_factor_enabled) {
            $request->session()->forget('two_factor_pending');
            return back()->with('two_factor_error', 'Two-step verification is no longer enabled.');
        }

        auth()->login($user);
        $request->session()->forget('two_factor_pending');
        return redirect()->route('dashboard');
    }
}
