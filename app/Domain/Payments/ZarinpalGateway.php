<?php
namespace App\Domain\Payments;
use App\Domain\Payments\Contracts\PaymentGateway;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class ZarinpalGateway implements PaymentGateway {
 private function merchant(): string {
  $id=(string)config('bluegate.payments.zarinpal.merchant_id');
  if(!$id) throw new RuntimeException('ZARINPAL_MERCHANT_ID is not configured');
  return $id;
 }
 private function gatewayAmount(float $amount): int {
  return (int)round($amount * (float)config('bluegate.payments.zarinpal.amount_multiplier',10));
 }
 public function request(float $amount,string $description,string $callbackUrl,array $metadata=[]): array {
  $payload=[
   'merchant_id'=>$this->merchant(),
   'amount'=>$this->gatewayAmount($amount),
   'description'=>$description,
   'callback_url'=>$callbackUrl,
   'metadata'=>array_filter([
    'mobile'=>$metadata['mobile']??null,
    'email'=>$metadata['email']??null,
   ])
  ];
  $json=Http::timeout(15)->acceptJson()->asJson()
   ->post(rtrim(config('bluegate.payments.zarinpal.api_base'),'/').'/pg/v4/payment/request.json',$payload)
   ->throw()->json();
  $data=$json['data']??[];
  if(empty($data['authority']) || (int)($data['code']??0)!==100)
   throw new RuntimeException('Zarinpal request failed: '.json_encode($json,JSON_UNESCAPED_UNICODE));
  return [
   'authority'=>$data['authority'],
   'redirect_url'=>rtrim(config('bluegate.payments.zarinpal.startpay_base'),'/').'/'.$data['authority'],
   'raw'=>$json
  ];
 }
 public function verify(string $authority,float $amount): array {
  $json=Http::timeout(15)->acceptJson()->asJson()
   ->post(rtrim(config('bluegate.payments.zarinpal.api_base'),'/').'/pg/v4/payment/verify.json',[
    'merchant_id'=>$this->merchant(),'amount'=>$this->gatewayAmount($amount),'authority'=>$authority
   ])->throw()->json();
  $data=$json['data']??[]; $code=(int)($data['code']??0);
  if(!in_array($code,[100,101],true))
   throw new RuntimeException('Zarinpal verify failed: '.json_encode($json,JSON_UNESCAPED_UNICODE));
  return ['verified'=>true,'already_verified'=>$code===101,'ref_id'=>(string)($data['ref_id']??''),'card_pan'=>$data['card_pan']??null,'raw'=>$json];
 }
}