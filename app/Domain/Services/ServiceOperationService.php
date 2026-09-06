<?php
namespace App\Domain\Services;

use App\Domain\Nodes\Node;
use App\Domain\Nodes\NodeSelector;
use App\Domain\Provisioning\ConfigUriBuilder;
use App\Domain\Provisioning\Providers\ThreeXUIProvider;
use App\Domain\Wallet\WalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class ServiceOperationService {
 public function __construct(
  private WalletService $wallets,
  private ThreeXUIProvider $provider,
  private NodeSelector $selector,
  private ConfigUriBuilder $uriBuilder,
 ){}

 private function op(Service $service,string $type,string $key,float $amount,array $request,callable $callback): array {
  $existingResult=DB::transaction(function() use($service,$type,$key,$amount,$request){
   $existing=DB::table('service_operations')->where('idempotency_key',$key)->lockForUpdate()->first();
   if($existing){
    if($existing->status==='completed') return ['done'=>true,'result'=>(array)json_decode($existing->response_payload??'{}',true)];
    if($existing->status==='running') throw new RuntimeException('این عملیات در حال انجام است.');
   } else {
    DB::table('service_operations')->insert([
     'id'=>(string)Str::uuid(),'service_id'=>$service->id,'user_id'=>$service->user_id,'type'=>$type,
     'status'=>'pending','amount'=>$amount,'currency'=>'IRR','idempotency_key'=>$key,
     'request_payload'=>json_encode($request),'created_at'=>now(),'updated_at'=>now()
    ]);
   }
   DB::table('service_operations')->where('idempotency_key',$key)->update(['status'=>'running','last_error'=>null,'updated_at'=>now()]);
   if($amount>0){
    $wallet=$this->wallets->walletFor($service->user_id,'IRR');
    $this->wallets->debit($wallet,$amount,'service_'.$type,'Service '.$type.' '.$service->id);
   }
   return ['done'=>false];
  });
  if($existingResult['done']) return $existingResult['result'];

  try{
   $result=$callback();
   DB::table('service_operations')->where('idempotency_key',$key)->update([
    'status'=>'completed','response_payload'=>json_encode($result),'completed_at'=>now(),'updated_at'=>now()
   ]);
   return $result;
  }catch(\Throwable $e){
   DB::transaction(function() use($service,$type,$key,$amount,$e){
    $op=DB::table('service_operations')->where('idempotency_key',$key)->lockForUpdate()->first();
    if($op && $op->status!=='completed'){
     if($amount>0){
      $wallet=$this->wallets->walletFor($service->user_id,'IRR');
      $this->wallets->credit($wallet,$amount,'service_'.$type.'_refund','Refund '.$type.' '.$service->id);
     }
     DB::table('service_operations')->where('idempotency_key',$key)->update([
      'status'=>'failed','last_error'=>$e->getMessage(),'updated_at'=>now()
     ]);
    }
   });
   throw $e;
  }
 }
 private function context(Service $service): array {
  $endpoint=ServiceEndpoint::where('service_id',$service->id)->where('status','active')->firstOrFail();
  $node=Node::findOrFail($endpoint->node_id);
  $inbound=DB::table('inbounds')->where('id',$endpoint->inbound_id)->firstOrFail();
  return [$endpoint,$node,$inbound];
 }

 private function client(Service $service, ServiceEndpoint $endpoint, int $totalBytes, ?\Carbon\CarbonInterface $expiry): array {
  return [
   'id'=>$endpoint->uuid,
   'email'=>$endpoint->provider_client_id,
   'limitIp'=>$service->device_limit ?? 0,
   'totalGB'=>$totalBytes,
   'expiryTime'=>$expiry ? $expiry->getTimestampMs() : 0,
   'enable'=>true,'tgId'=>'','subId'=>Str::random(16),'reset'=>0
  ];
 }

 public function renew(Service $service,string $idempotencyKey): array {
  $plan=DB::table('plans')->where('id',$service->plan_id)->firstOrFail();
  if(!$plan->renewable) throw new RuntimeException('این پلن قابل تمدید نیست.');
  $amount=(float)$plan->base_price;
  return $this->op($service,'renew',$idempotencyKey,$amount,['plan_id'=>$plan->id],function() use($service,$plan){
   $service->refresh(); [$endpoint,$node,$inbound]=$this->context($service);
   $base=$service->expires_at && $service->expires_at->isFuture() ? $service->expires_at : now();
   $expiry=$plan->unlimited_duration ? null : $base->copy()->addDays((int)$plan->duration_days);
   $total=(int)($service->traffic_limit_bytes??0);
   $client=$this->client($service,$endpoint,$total,$expiry);
   $response=$this->provider->updateClientOnInbound($node,(string)$inbound->provider_inbound_id,(string)$endpoint->uuid,$client);
   $service->update(['expires_at'=>$expiry,'status'=>'active']);
   return ['expires_at'=>$expiry?->toIso8601String(),'provider'=>$response];
  });
 }

 public function addTraffic(Service $service,int $gb,string $idempotencyKey): array {
  if(!in_array($gb,[10,25,50,100],true)) throw new RuntimeException('حجم انتخابی معتبر نیست.');
  if($service->traffic_limit_bytes===null) throw new RuntimeException('این سرویس حجم نامحدود دارد.');
  $amount=$gb*(float)config('bluegate.operations.traffic_price_per_gb',5000);
  return $this->op($service,'add_traffic',$idempotencyKey,$amount,['gb'=>$gb],function() use($service,$gb){
   $service->refresh(); [$endpoint,$node,$inbound]=$this->context($service);
   $newTotal=(int)$service->traffic_limit_bytes+($gb*1024**3);
   $client=$this->client($service,$endpoint,$newTotal,$service->expires_at);
   $response=$this->provider->updateClientOnInbound($node,(string)$inbound->provider_inbound_id,(string)$endpoint->uuid,$client);
   $service->update(['traffic_limit_bytes'=>$newTotal]);
   return ['traffic_limit_bytes'=>$newTotal,'added_gb'=>$gb,'provider'=>$response];
  });
 }

 public function changeLocation(Service $service,string $locationId,string $idempotencyKey): array {
  $location=DB::table('locations')->where('id',$locationId)->where('active',true)->firstOrFail();
  $amount=(float)config('bluegate.operations.location_change_price',0);
  return $this->op($service,'change_location',$idempotencyKey,$amount,['location_id'=>$locationId],function() use($service,$location){
   $service->refresh(); [$oldEndpoint,$oldNode,$oldInbound]=$this->context($service);
   if($oldNode->location_id===$location->id) throw new RuntimeException('سرویس همین حالا روی این لوکیشن است.');
   $newNode=$this->selector->select($location->id);
   $newInbound=DB::table('inbounds')->where('node_id',$newNode->id)->where('active',true)->orderBy('created_at')->first();
   if(!$newInbound) throw new RuntimeException('Inbound فعال برای لوکیشن مقصد وجود ندارد.');
   $client=$this->client($service,$oldEndpoint,(int)($service->traffic_limit_bytes??0),$service->expires_at);
   $result=$this->provider->createClient($newNode,['inbound_id'=>$newInbound->provider_inbound_id,'client'=>$client]);
   if(($result['success']??true)===false) throw new RuntimeException($result['msg']??'ساخت کلاینت در نود مقصد ناموفق بود.');
   $uri=$this->uriBuilder->build($newNode,$newInbound,(string)$oldEndpoint->uuid,$oldEndpoint->provider_client_id);

   $newEndpoint=ServiceEndpoint::create([
    'id'=>(string)Str::uuid(),'service_id'=>$service->id,'node_id'=>$newNode->id,'inbound_id'=>$newInbound->id,
    'provider_client_id'=>$oldEndpoint->provider_client_id,'uuid'=>$oldEndpoint->uuid,'status'=>'active',
    'traffic_used'=>$oldEndpoint->traffic_used,'metadata'=>['provider_response'=>$result,'uri'=>$uri]
   ]);
   $oldEndpoint->update(['status'=>'migrated']);
   $service->update(['current_location_id'=>$location->id]);
   try{
    $this->provider->deleteClientFromInbound($oldNode,(string)$oldInbound->provider_inbound_id,(string)$oldEndpoint->uuid);
   }catch(\Throwable $e){ report($e); }
   return ['location_id'=>$location->id,'node_id'=>$newNode->id,'endpoint_id'=>$newEndpoint->id];
  });
 }
}