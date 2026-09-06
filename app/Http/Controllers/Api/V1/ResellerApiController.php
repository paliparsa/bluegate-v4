<?php
namespace App\Http\Controllers\Api\V1;

use App\Domain\Provisioning\ProvisionService;
use App\Domain\Wallet\WalletService;
use App\Domain\Reseller\ResellerBillingService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class ResellerApiController extends Controller {
 private function profile(Request $r): object {
  return $r->attributes->get('reseller_profile');
 }

 public function catalog(Request $r){
  $profile=$this->profile($r);
  $products=DB::table('products')->where('active',true)->orderBy('sort_order')->get();
  $plans=DB::table('plans')->where('active',true)->orderBy('sort_order')->get()->map(function($p) use($profile){
   $base=(float)$p->base_price; $discount=(float)$profile->discount_percent;
   $p->reseller_price=max(0,$base*(1-$discount/100)); return $p;
  });
  return response()->json(['products'=>$products,'plans'=>$plans]);
 }

 public function balance(Request $r,WalletService $wallets){
  $wallet=$wallets->walletFor($r->user()->id,'IRR');
  return response()->json(['currency'=>'IRR','balance'=>(float)$wallet->balance_cached]);
 }

 public function orders(Request $r){
  $items=DB::table('orders')->where('user_id',$r->user()->id)->orderByDesc('created_at')->paginate(50);
  return response()->json($items);
 }

 public function services(Request $r){
  $items=DB::table('services')->where('user_id',$r->user()->id)->orderByDesc('created_at')->paginate(50);
  return response()->json($items);
 }

 public function createOrder(Request $r,ResellerBillingService $billing,ProvisionService $provisioner){
  $idem=trim((string)$r->header('Idempotency-Key'));
  if($idem==='' || strlen($idem)>120) return response()->json(['message'=>'Idempotency-Key header is required'],422);
  $d=$r->validate(['plan_id'=>'required|uuid|exists:plans,id']);
  $keyHash=hash('sha256',$idem);

  $existing=DB::table('api_idempotency_keys')->where('user_id',$r->user()->id)->where('scope','reseller.order')
   ->where('key_hash',$keyHash)->first();
  if($existing && $existing->response_body){
   return response()->json(json_decode($existing->response_body,true),$existing->status_code?:200);
  }

  try{
   $result=DB::transaction(function() use($r,$d,$billing,$keyHash){
    $again=DB::table('api_idempotency_keys')->where('user_id',$r->user()->id)->where('scope','reseller.order')
     ->where('key_hash',$keyHash)->lockForUpdate()->first();
    if($again && $again->resource_id) return ['existing_order_id'=>$again->resource_id];

    if(!$again){
     DB::table('api_idempotency_keys')->insert([
      'user_id'=>$r->user()->id,'scope'=>'reseller.order','key_hash'=>$keyHash,'created_at'=>now(),'updated_at'=>now()
     ]);
    }

    $profile=DB::table('reseller_profiles')->where('user_id',$r->user()->id)->where('active',true)->lockForUpdate()->firstOrFail();
    $plan=DB::table('plans')->where('id',$d['plan_id'])->where('active',true)->firstOrFail();
    $product=DB::table('products')->where('id',$plan->product_id)->where('active',true)->firstOrFail();

    $allowed=(array)json_decode($profile->allowed_product_ids??'[]',true);
    if($allowed && !in_array($product->id,$allowed,true)) throw new RuntimeException('Product is not enabled for this reseller.');

    $price=max(0,(float)$plan->base_price*(1-(float)$profile->discount_percent/100));
    $billing->debit($r->user()->id,$price,'Reseller API purchase');

    $orderId=(string)Str::uuid(); $itemId=(string)Str::uuid();
    DB::table('orders')->insert([
     'id'=>$orderId,'order_number'=>'RS'.now()->format('ymd').strtoupper(Str::random(6)),'user_id'=>$r->user()->id,
     'status'=>'paid','subtotal'=>$price,'discount'=>0,'wallet_used'=>$price,'payable'=>0,'currency'=>$plan->currency,
     'payment_method'=>'reseller_wallet','paid_at'=>now(),
     'metadata'=>json_encode(['reseller'=>true,'base_catalog_price'=>(float)$plan->base_price,'discount_percent'=>(float)$profile->discount_percent]),
     'created_at'=>now(),'updated_at'=>now()
    ]);
    DB::table('order_items')->insert([
     'id'=>$itemId,'order_id'=>$orderId,'product_id'=>$product->id,'plan_id'=>$plan->id,'quantity'=>1,
     'unit_price'=>$price,'total_price'=>$price,'created_at'=>now(),'updated_at'=>now()
    ]);
    DB::table('api_idempotency_keys')->where('user_id',$r->user()->id)->where('scope','reseller.order')->where('key_hash',$keyHash)
     ->update(['resource_type'=>'order','resource_id'=>$orderId,'updated_at'=>now()]);
    return ['order_id'=>$orderId,'price'=>$price];
   },3);

   $orderId=$result['existing_order_id']??$result['order_id'];
   $service=$provisioner->fromOrder($orderId);
   $payload=['order_id'=>$orderId,'service_id'=>$service->id,'status'=>'active'];

   DB::table('api_idempotency_keys')->where('user_id',$r->user()->id)->where('scope','reseller.order')->where('key_hash',$keyHash)
    ->update(['status_code'=>201,'response_body'=>json_encode($payload),'updated_at'=>now()]);
   return response()->json($payload,201);
  }catch(\Throwable $e){
   report($e);
   $record=DB::table('api_idempotency_keys')->where('user_id',$r->user()->id)->where('scope','reseller.order')->where('key_hash',$keyHash)->first();
   if($record?->resource_id){
    $payload=['order_id'=>$record->resource_id,'status'=>'provisioning_failed','message'=>'Order paid but provisioning needs retry'];
    DB::table('api_idempotency_keys')->where('id',$record->id)->update(['status_code'=>202,'response_body'=>json_encode($payload),'updated_at'=>now()]);
    return response()->json($payload,202);
   }
   return response()->json(['message'=>$e->getMessage()],422);
  }
 }

 public function retry(Request $r,string $id,ProvisionService $provisioner){
  $order=DB::table('orders')->where('id',$id)->where('user_id',$r->user()->id)->firstOrFail();
  if(!in_array($order->status,['paid','provisioning_failed'],true)) return response()->json(['message'=>'Order is not retryable'],409);
  try{
   $service=$provisioner->fromOrder($order->id);
   return response()->json(['order_id'=>$order->id,'service_id'=>$service->id,'status'=>'active']);
  }catch(\Throwable $e){ report($e); return response()->json(['order_id'=>$order->id,'status'=>'provisioning_failed','message'=>$e->getMessage()],202); }
 }
}