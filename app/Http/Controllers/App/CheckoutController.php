<?php
namespace App\Http\Controllers\App;
use App\Domain\Provisioning\ProvisionService;
use App\Domain\Notifications\TelegramNotifier;
use App\Domain\Notifications\NotificationService;
use App\Domain\Growth\ReferralService;
use App\Domain\Wallet\WalletService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class CheckoutController extends Controller {
 public function payWallet(Request $r, string $id, WalletService $wallets, ProvisionService $provisioner, TelegramNotifier $telegram, ReferralService $referrals, NotificationService $notifications){
  $order=DB::table('orders')->where('id',$id)->where('user_id',$r->user()->id)->firstOrFail();
  if($order->status!=='pending_payment') return back()->with('error','این سفارش قابل پرداخت نیست.');
  try{
   DB::transaction(function() use($order,$r,$wallets){
    $locked=DB::table('orders')->where('id',$order->id)->lockForUpdate()->first();
    if($locked->status!=='pending_payment') return;
    $payable=(float)$locked->payable;
    if($payable>0){
     $wallet=$wallets->walletFor($r->user()->id,$locked->currency);
     $wallets->debit($wallet,$payable,'order_payment','پرداخت سفارش '.$locked->order_number);
    }
    DB::table('orders')->where('id',$locked->id)->update([
     'status'=>'paid','wallet_used'=>$payable,'payable'=>0,'payment_method'=>$payable>0?'wallet':'free','paid_at'=>now(),'updated_at'=>now()
    ]);
   });
   $provisioner->fromOrder($order->id);
   $referrals->settleOrder($order->id,$wallets);
   $notifications->create($r->user()->id,'سرویس تحویل شد','سفارش '.$order->order_number.' با موفقیت فعال شد.','success',route('app.services'));
   $telegram->send($r->user(),"✅ <b>سرویس BlueGate تحویل شد</b>\nسفارش: <code>{$order->order_number}</code>");
   return redirect()->route('app.services')->with('success','پرداخت انجام شد و سرویس با موفقیت ساخته شد.');
  }catch(\Throwable $e){
   report($e);
   $telegram->send($r->user(),"⚠️ <b>سفارش نیاز به بررسی دارد</b>\nسفارش: <code>{$order->order_number}</code>");
   return redirect()->route('app.orders')->with('error','پرداخت/ساخت سرویس کامل نشد: '.$e->getMessage());
  }
 }
}