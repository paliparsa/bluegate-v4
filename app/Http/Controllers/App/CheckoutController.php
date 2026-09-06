<?php
namespace App\Http\Controllers\App;
use App\Domain\Provisioning\ProvisionService;
use App\Domain\Notifications\TelegramNotifier;
use App\Domain\Wallet\WalletService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class CheckoutController extends Controller {
 public function payWallet(Request $r, string $id, WalletService $wallets, ProvisionService $provisioner, TelegramNotifier $telegram){
  $order=DB::table('orders')->where('id',$id)->where('user_id',$r->user()->id)->firstOrFail();
  if($order->status!=='pending_payment') return back()->with('error','این سفارش قابل پرداخت نیست.');
  try{
   DB::transaction(function() use($order,$r,$wallets){
    $locked=DB::table('orders')->where('id',$order->id)->lockForUpdate()->first();
    if($locked->status!=='pending_payment') return;
    $wallet=$wallets->walletFor($r->user()->id,$locked->currency);
    $wallets->debit($wallet,(float)$locked->payable,'order_payment','پرداخت سفارش '.$locked->order_number);
    DB::table('orders')->where('id',$locked->id)->update([
     'status'=>'paid','wallet_used'=>$locked->payable,'payable'=>0,'payment_method'=>'wallet','paid_at'=>now(),'updated_at'=>now()
    ]);
   });
   $provisioner->fromOrder($order->id);
   $telegram->send($r->user(),"✅ <b>سرویس BlueGate تحویل شد</b>\nسفارش: <code>{$order->order_number}</code>");
   return redirect()->route('app.services')->with('success','پرداخت انجام شد و سرویس با موفقیت ساخته شد.');
  }catch(\Throwable $e){
   report($e);
   $telegram->send($r->user(),"⚠️ <b>سفارش نیاز به بررسی دارد</b>\nسفارش: <code>{$order->order_number}</code>");
   return redirect()->route('app.orders')->with('error','پرداخت/ساخت سرویس کامل نشد: '.$e->getMessage());
  }
 }
}