<?php
namespace App\Domain\Growth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class CouponService {
 public function calculate(?string $code,int $userId,object $plan,float $subtotal): array {
  if(!$code) return ['discount'=>0,'coupon'=>null];
  $coupon=DB::table('coupons')->whereRaw('UPPER(code)=?',[strtoupper(trim($code))])->where('active',true)->lockForUpdate()->first();
  if(!$coupon) throw new RuntimeException('کد تخفیف معتبر نیست.');
  $now=now();
  if($coupon->starts_at && $now->lt($coupon->starts_at)) throw new RuntimeException('زمان استفاده از این کد هنوز شروع نشده.');
  if($coupon->ends_at && $now->gt($coupon->ends_at)) throw new RuntimeException('این کد تخفیف منقضی شده است.');
  if($coupon->min_order && $subtotal<(float)$coupon->min_order) throw new RuntimeException('حداقل مبلغ سفارش برای این کد رعایت نشده.');
  $totalUses=DB::table('coupon_redemptions')->join('orders','orders.id','=','coupon_redemptions.order_id')
   ->where('coupon_redemptions.coupon_id',$coupon->id)->whereIn('orders.status',['paid','active'])->count();
  if($coupon->max_uses && $totalUses >= $coupon->max_uses) throw new RuntimeException('ظرفیت این کد تخفیف تمام شده است.');
  $userUses=DB::table('coupon_redemptions')->join('orders','orders.id','=','coupon_redemptions.order_id')
   ->where('coupon_redemptions.coupon_id',$coupon->id)->where('coupon_redemptions.user_id',$userId)
   ->whereIn('orders.status',['paid','active'])->count();
  if($userUses >= $coupon->max_uses_per_user) throw new RuntimeException('قبلاً از این کد استفاده کرده‌ای.');
  $productIds=(array)json_decode($coupon->product_ids??'[]',true); $planIds=(array)json_decode($coupon->plan_ids??'[]',true);
  if($productIds && !in_array($plan->product_id,$productIds,true)) throw new RuntimeException('این کد برای محصول انتخابی قابل استفاده نیست.');
  if($planIds && !in_array($plan->id,$planIds,true)) throw new RuntimeException('این کد برای پلن انتخابی قابل استفاده نیست.');
  $discount=$coupon->type==='fixed' ? (float)$coupon->value : $subtotal*((float)$coupon->value/100);
  if($coupon->max_discount) $discount=min($discount,(float)$coupon->max_discount);
  return ['discount'=>max(0,min($subtotal,$discount)),'coupon'=>$coupon];
 }
 public function redeem(object $coupon,int $userId,string $orderId,float $discount): void {
  DB::table('coupon_redemptions')->insertOrIgnore([
   'id'=>(string)Str::uuid(),'coupon_id'=>$coupon->id,'user_id'=>$userId,'order_id'=>$orderId,
   'discount'=>$discount,'created_at'=>now(),'updated_at'=>now()
  ]);
 }
}