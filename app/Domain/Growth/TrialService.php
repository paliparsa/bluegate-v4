<?php
namespace App\Domain\Growth;
use App\Domain\Provisioning\ProvisionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class TrialService {
 public function claim(Request $request, ProvisionService $provisioner): string {
  $user=$request->user();
  if(DB::table('trial_claims')->where('user_id',$user->id)->exists()) throw new RuntimeException('قبلاً از تست رایگان استفاده کرده‌ای.');
  $ip=$request->ip(); $fingerprint=substr(hash('sha256',(string)$request->userAgent()),0,128);
  if(config('bluegate.trial.block_reused_ip',true) && $ip && DB::table('trial_claims')->where('ip',$ip)->whereIn('status',['provisioning','active'])->exists())
   throw new RuntimeException('برای این اتصال قبلاً تست رایگان ثبت شده است.');
  if(config('bluegate.trial.block_reused_fingerprint',true) && DB::table('trial_claims')->where('fingerprint',$fingerprint)->whereIn('status',['provisioning','active'])->exists())
   throw new RuntimeException('برای این دستگاه/مرورگر قبلاً تست رایگان ثبت شده است.');
  $plan=DB::table('plans')->where('trial_enabled',true)->where('active',true)->orderBy('sort_order')->first();
  if(!$plan) throw new RuntimeException('در حال حاضر پلن تست فعالی تعریف نشده است.');
  $product=DB::table('products')->where('id',$plan->product_id)->where('active',true)->firstOrFail();
  $claimId=(string)Str::uuid(); $orderId=(string)Str::uuid(); $itemId=(string)Str::uuid();
  DB::transaction(function() use($request,$user,$plan,$product,$claimId,$orderId,$itemId){
   DB::table('trial_claims')->insert([
    'id'=>$claimId,'user_id'=>$user->id,'plan_id'=>$plan->id,'status'=>'provisioning',
    'ip'=>$request->ip(),'fingerprint'=>substr(hash('sha256',(string)$request->userAgent()),0,128),
    'created_at'=>now(),'updated_at'=>now()
   ]);
   DB::table('orders')->insert([
    'id'=>$orderId,'order_number'=>'TR'.now()->format('ymd').strtoupper(Str::random(6)),'user_id'=>$user->id,
    'status'=>'paid','subtotal'=>0,'discount'=>0,'wallet_used'=>0,'payable'=>0,'currency'=>$plan->currency,
    'payment_method'=>'trial','paid_at'=>now(),'metadata'=>json_encode(['trial_claim_id'=>$claimId]),'created_at'=>now(),'updated_at'=>now()
   ]);
   DB::table('order_items')->insert([
    'id'=>$itemId,'order_id'=>$orderId,'product_id'=>$product->id,'plan_id'=>$plan->id,'quantity'=>1,
    'unit_price'=>0,'total_price'=>0,'configuration'=>json_encode(['trial'=>true]),'created_at'=>now(),'updated_at'=>now()
   ]);
  });
  try{
   $service=$provisioner->fromOrder($orderId);
   DB::table('trial_claims')->where('id',$claimId)->update(['service_id'=>$service->id,'status'=>'active','updated_at'=>now()]);
   return $service->id;
  }catch(\Throwable $e){
   DB::table('trial_claims')->where('id',$claimId)->update(['status'=>'failed','last_error'=>$e->getMessage(),'updated_at'=>now()]);
   throw $e;
  }
 }
}