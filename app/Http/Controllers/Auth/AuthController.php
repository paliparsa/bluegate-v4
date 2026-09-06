<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
class AuthController extends Controller {
 public function showLogin(){ return view('auth.login'); }
 public function login(Request $r){
  $data=$r->validate(['email'=>'required|email','password'=>'required|string']);
  if(!Auth::attempt($data,$r->boolean('remember'))) return back()->withErrors(['email'=>'اطلاعات ورود صحیح نیست.'])->onlyInput('email');
  $r->session()->regenerate();
  if($r->user()->status!=='active'){ Auth::logout(); return back()->withErrors(['email'=>'حساب شما غیرفعال است.']); }
  return redirect()->intended(route('app.dashboard'));
 }
 public function showRegister(){ return view('auth.register'); }
 public function register(Request $r){
  $data=$r->validate(['name'=>'required|string|max:100','email'=>'required|email|max:190|unique:users,email','phone'=>'nullable|string|max:30|unique:users,phone','password'=>'required|min:8|confirmed']);
  $user=DB::transaction(function() use($data){
   $u=User::create(['name'=>$data['name'],'email'=>$data['email'],'phone'=>$data['phone']??null,'password'=>Hash::make($data['password'])]);
   Wallet::create(['user_id'=>$u->id,'currency'=>'IRR','balance_cached'=>0]);
   return $u;
  });
  Auth::login($user); $r->session()->regenerate(); return redirect()->route('app.dashboard');
 }
 public function logout(Request $r){ Auth::logout(); $r->session()->invalidate(); $r->session()->regenerateToken(); return redirect('/'); }
}
