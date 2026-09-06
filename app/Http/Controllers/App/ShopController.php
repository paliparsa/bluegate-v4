<?php
namespace App\Http\Controllers\App;
use App\Domain\Growth\CouponService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShopController extends Controller {
 public function index(){
  $products=DB::table('products')->where('active',true)->orderBy('sort_order')->get();
  $plans=DB::table('plans')->where('active',true)->orderBy('sort_order')->get()->groupBy('product_id');
  return view('app.buy',compact('products','plans'));
 }

 public function order(Request $r,CouponService $coupons){
  $data=$r->validate(['plan_id'=>'required|uuid','coupon_code'=>'nullable|string|max:60']);
  try{
   $orderId=DB::transaction(function() use($r,$data,$coupons){
    $plan=DB::table('plans')->where('id',$data['plan_id'])->where('active',true)->lockForUpdate()->firstOrFail();
    $product=DB::table('products')->where('id',$plan->product_id)->where('active',true)->firstOrFail();
    $couponResult=$coupons->calculate($data['coupon_code']??null,$r->user()->id,$plan,(float)$plan->base_price);
    $discount=(float)$couponResult['discount']; $payable=max(0,(float)$plan->base_price-$discount);
    $orderId=(string)Str::uuid(); $itemId=(string)Str::uuid();
    DB::table('orders')->insert([
     'id'=>$orderId,'order_number'=>'BG'.now()->format('ymd').strtoupper(Str::random(6)),
     'user_id'=>$r->user()->id,'status'=>'pending_payment','subtotal'=>$plan->base_price,
     'discount'=>$discount,'wallet_used'=>0,'payable'=>$payable,'currency'=>$plan->currency,
     'metadata'=>json_encode(['coupon_code'=>$couponResult['coupon']->code??null]),
     'created_at'=>now(),'updated_at'=>now()
    ]);
    DB::table('order_items')->insert([
     'id'=>$itemId,'order_id'=>$orderId,'product_id'=>$product->id,'plan_id'=>$plan->id,'quantity'=>1,
     'unit_price'=>$plan->base_price,'total_price'=>$plan->base_price,'created_at'=>now(),'updated_at'=>now()
    ]);
    if($couponResult['coupon']) $coupons->redeem($couponResult['coupon'],$r->user()->id,$orderId,$discount);
    return $orderId;
   },3);
   return redirect()->route('app.orders')->with('success','سفارش ساخته شد؛ روش پرداخت را انتخاب کن.');
  }catch(\Throwable $e){
   report($e); return back()->withInput()->with('error',$e->getMessage());
  }
 }
}