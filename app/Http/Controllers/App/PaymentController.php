<?php
namespace App\Http\Controllers\App;
use App\Domain\Notifications\TelegramNotifier;
use App\Domain\Notifications\NotificationService;
use App\Domain\Growth\ReferralService;
use App\Domain\Wallet\WalletService;
use App\Domain\Payments\ZarinpalGateway;
use App\Domain\Provisioning\ProvisionService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class PaymentController extends Controller {
 public function start(Request $request,string $id,ZarinpalGateway $gateway){
  $order=DB::table('orders')->where('id',$id)->where('user_id',$request->user()->id)->firstOrFail();
  if($order->status!=='pending_payment') return back()->with('error','این سفارش قابل پرداخت نیست.');
  $existing=DB::table('payments')->where('order_id',$order->id)->where('gateway','zarinpal')->whereIn('status',['pending','verified'])->latest()->first();
  if($existing?->status==='verified') return back()->with('success','این سفارش قبلاً پرداخت شده است.');

  $paymentId=(string)Str::uuid(); $token=Str::random(64);
  $payment=$existing;
  if(!$payment){
   DB::table('payments')->insert([
    'id'=>$paymentId,'user_id'=>$request->user()->id,'order_id'=>$order->id,'gateway'=>'zarinpal',
    'amount'=>$order->payable,'currency'=>$order->currency,'status'=>'created',
    'idempotency_key'=>'zarinpal:order:'.$order->id,'callback_token'=>$token,'created_at'=>now(),'updated_at'=>now()
   ]);
   $payment=DB::table('payments')->where('id',$paymentId)->first();
  }
  $callback=route('payments.zarinpal.callback',['payment'=>$payment->id,'token'=>$payment->callback_token]);
  try{
   $result=$gateway->request((float)$payment->amount,'BlueGate Order '.$order->order_number,$callback,[
    'email'=>$request->user()->email,'mobile'=>$request->user()->phone
   ]);
   DB::table('payments')->where('id',$payment->id)->update([
    'status'=>'pending','authority'=>$result['authority'],'gateway_payload'=>json_encode($result['raw']??[]),'updated_at'=>now()
   ]);
   return redirect()->away($result['redirect_url']);
  }catch(\Throwable $e){ report($e); return back()->with('error','اتصال به درگاه ناموفق بود: '.$e->getMessage()); }
 }

 public function callback(Request $request,string $payment,string $token,ZarinpalGateway $gateway,ProvisionService $provisioner,TelegramNotifier $telegram,ReferralService $referrals,WalletService $wallets,NotificationService $notifications){
  $p=DB::table('payments')->where('id',$payment)->where('callback_token',$token)->firstOrFail();
  $order=DB::table('orders')->where('id',$p->order_id)->firstOrFail();
  $user=User::find($p->user_id);
  if($p->status==='verified') return redirect()->route(auth()->check()?'app.orders':'login')->with('success','پرداخت قبلاً تایید شده است.');
  if(strtoupper((string)$request->query('Status'))!=='OK'){
   DB::table('payments')->where('id',$p->id)->update(['status'=>'cancelled','callback_payload'=>json_encode($request->query()),'updated_at'=>now()]);
   return redirect()->route(auth()->check()?'app.orders':'login')->with('error','پرداخت لغو یا ناموفق بود.');
  }
  if(!$p->authority) throw new RuntimeException('Payment authority missing');
  try{
   $verified=$gateway->verify($p->authority,(float)$p->amount);
   DB::transaction(function() use($p,$order,$verified,$request){
    $locked=DB::table('payments')->where('id',$p->id)->lockForUpdate()->first();
    if($locked->status==='verified') return;
    DB::table('payments')->where('id',$p->id)->update([
     'status'=>'verified','transaction_id'=>$verified['ref_id']?:$locked->transaction_id,
     'callback_payload'=>json_encode($request->query()),'gateway_payload'=>json_encode($verified['raw']??[]),
     'verified_at'=>now(),'updated_at'=>now()
    ]);
    DB::table('orders')->where('id',$order->id)->update([
     'status'=>'paid','payment_method'=>'zarinpal','paid_at'=>now(),'updated_at'=>now()
    ]);
   });
   try{
    $service=$provisioner->fromOrder($order->id);
    $referrals->settleOrder($order->id,$wallets);
    $notifications->create($user->id,'پرداخت و تحویل موفق','سفارش '.$order->order_number.' تایید و سرویس فعال شد.','success',route('app.services'));
    $telegram->send($user,"✅ <b>پرداخت و تحویل سرویس موفق</b>\nسفارش: <code>{$order->order_number}</code>\nمبلغ: ".number_format((float)$p->amount)." تومان");
    return redirect()->route(auth()->check()?'app.services':'login')->with('success','پرداخت تایید و سرویس با موفقیت ساخته شد.');
   }catch(\Throwable $provisionError){
    report($provisionError);
    $telegram->send($user,"⚠️ <b>پرداخت تایید شد اما Provisioning ناموفق بود</b>\nسفارش: <code>{$order->order_number}</code>");
    return redirect()->route(auth()->check()?'app.orders':'login')->with('error','پرداخت تایید شد ولی ساخت سرویس نیاز به بررسی دارد. مبلغ پرداخت از بین نرفته است.');
   }
  }catch(\Throwable $e){
   report($e);
   DB::table('payments')->where('id',$p->id)->update(['status'=>'verify_failed','callback_payload'=>json_encode($request->query()),'updated_at'=>now()]);
   return redirect()->route(auth()->check()?'app.orders':'login')->with('error','تایید پرداخت از سمت درگاه ناموفق بود.');
  }
 }
}