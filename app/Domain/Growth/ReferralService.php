<?php
namespace App\Domain\Growth;
use App\Domain\Wallet\WalletService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class ReferralService {
 public function ensureCode(User $user): string {
  if($user->referral_code) return $user->referral_code;
  do { $code=strtoupper(Str::random(8)); } while(DB::table('users')->where('referral_code',$code)->exists());
  $user->forceFill(['referral_code'=>$code])->save(); return $code;
 }
 public function settleOrder(string $orderId,WalletService $wallets): void {
  DB::transaction(function() use($orderId,$wallets){
   $order=DB::table('orders')->where('id',$orderId)->lockForUpdate()->first();
   if(!$order || !in_array($order->status,['paid','active'],true)) return;
   if(DB::table('referral_commissions')->where('order_id',$orderId)->exists()) return;
   $buyer=User::find($order->user_id); if(!$buyer?->referred_by_user_id) return;
   $eligibleCount=DB::table('orders')->where('user_id',$buyer->id)->whereIn('status',['paid','active'])->count();
   $maxOrders=(int)config('bluegate.referral.eligible_orders',3); if($eligibleCount>$maxOrders) return;
   $rate=(float)config('bluegate.referral.rate',10);
   $base=(float)$order->subtotal-(float)$order->discount; $commission=max(0,$base*$rate/100); if($commission<=0) return;
   $referrer=User::find($buyer->referred_by_user_id); if(!$referrer) return;
   DB::table('referral_commissions')->insert([
    'id'=>(string)Str::uuid(),'referrer_user_id'=>$referrer->id,'referred_user_id'=>$buyer->id,'order_id'=>$orderId,
    'base_amount'=>$base,'rate'=>$rate,'commission'=>$commission,'status'=>'credited','created_at'=>now(),'updated_at'=>now()
   ]);
   $wallet=$wallets->walletFor($referrer->id,'IRR');
   $wallets->credit($wallet,$commission,'referral_commission','Referral commission for order '.$order->order_number);
  });
 }
}