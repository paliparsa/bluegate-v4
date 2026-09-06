<?php
namespace App\Http\Controllers\App;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
class WalletController extends Controller {
 public function index(){ $wallet=DB::table('wallets')->where('user_id',request()->user()->id)->first(); $tx=$wallet?DB::table('wallet_transactions')->where('wallet_id',$wallet->id)->latest()->paginate(20):collect(); return view('app.wallet',compact('wallet','tx')); }
 public function orders(){ $orders=DB::table('orders')->where('user_id',request()->user()->id)->latest()->paginate(20); return view('app.orders',compact('orders')); }
}
