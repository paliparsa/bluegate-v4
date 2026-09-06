<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
class AdminController extends Controller {
 public function dashboard(){
  $stats=['users'=>DB::table('users')->count(),'services'=>DB::table('services')->where('status','active')->count(),'orders'=>DB::table('orders')->count(),'nodes'=>DB::table('nodes')->count()];
  return view('admin.dashboard',compact('stats'));
 }
 public function nodes(){ $nodes=DB::table('nodes')->leftJoin('locations','locations.id','=','nodes.location_id')->select('nodes.*','locations.name as location_name')->latest('nodes.created_at')->paginate(25); return view('admin.nodes',compact('nodes')); }
 public function products(){ $products=DB::table('products')->orderBy('sort_order')->get(); $plans=DB::table('plans')->orderBy('sort_order')->get()->groupBy('product_id'); return view('admin.products',compact('products','plans')); }
 public function payments(){
  $payments=\Illuminate\Support\Facades\DB::table('payments')->join('users','users.id','=','payments.user_id')->select('payments.*','users.email')->orderByDesc('payments.created_at')->paginate(50);
  return view('admin.payments',compact('payments'));
 }
}
