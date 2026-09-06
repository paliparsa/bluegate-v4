<?php
namespace App\Http\Controllers\App;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
class DashboardController extends Controller {
 public function __invoke(){
  $u=request()->user();
  $wallet=DB::table('wallets')->where('user_id',$u->id)->first();
  $active=DB::table('services')->where('user_id',$u->id)->where('status','active')->count();
  $services=DB::table('services')->where('user_id',$u->id)->latest()->limit(5)->get();
  $orders=DB::table('orders')->where('user_id',$u->id)->latest()->limit(5)->get();
  return view('app.dashboard',compact('wallet','active','services','orders'));
 }
}
