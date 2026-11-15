<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\AuditService;

class AuthController extends Controller {
    public function showLogin(){ return view('admin.auth.login'); }
    public function login(Request $request){
        $credentials=$request->validate(['email'=>'required|email','password'=>'required|string']);
        $remember=$request->boolean('remember');
        if (Auth::attempt(['email'=>$credentials['email'],'password'=>$credentials['password'],'is_active'=>true],$remember)) {
            $request->session()->regenerate(); AuditService::log('login','user',Auth::id(),null,null,'Admin login');
            return redirect()->intended(route('admin.dashboard'));
        }
        return back()->withInput($request->only('email','remember'))->withErrors(['email'=>'Invalid credentials or inactive account.']);
    }
    public function logout(Request $request){ AuditService::log('logout','user',Auth::id(),null,null,'Admin logout'); Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect()->route('admin.login'); }
    public function profile(){ return view('admin.auth.profile',['user'=>auth()->user()]); }
    public function password(Request $request){
        $d=$request->validate(['current_password'=>'required','password'=>'required|string|min:8|confirmed']);
        if(!Hash::check($d['current_password'],auth()->user()->password)) return back()->withErrors(['current_password'=>'Current password is incorrect.']);
        $user=auth()->user(); $user->password=Hash::make($d['password']); $user->save(); AuditService::log('password_changed','user',$user->id,null,null,'Admin password changed'); return back()->with('success','Password changed successfully.');
    }
}
