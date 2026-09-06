<?php
namespace App\Http\Controllers\App;
use App\Domain\Services\Service;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
class ServiceController extends Controller {
 public function index(){ $services=Service::where('user_id',request()->user()->id)->latest()->paginate(15); return view('app.services.index',compact('services')); }
 public function show(string $id){
  $s=Service::where('id',$id)->where('user_id',request()->user()->id)->firstOrFail();
  $product=DB::table('products')->where('id',$s->product_id)->first();
  $plan=$s->plan_id?DB::table('plans')->where('id',$s->plan_id)->first():null;
  $location=$s->current_location_id?DB::table('locations')->where('id',$s->current_location_id)->first():null;
  $subscriptionUrl=$s->subscription_token_plain ? url('/s/'.$s->subscription_token_plain) : null;
  return view('app.services.show',compact('s','product','plan','location','subscriptionUrl'));
 }
}