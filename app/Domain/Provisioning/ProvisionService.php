<?php
namespace App\Domain\Provisioning;
use App\Domain\Nodes\NodeSelector;
use App\Domain\Provisioning\Providers\ThreeXUIProvider;
use App\Domain\Services\Service;
use App\Domain\Subscription\SubscriptionToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class ProvisionService {
 public function __construct(private NodeSelector $selector, private ThreeXUIProvider $provider, private ConfigUriBuilder $uriBuilder){}
 public function fromOrder(string $orderId): Service {
  return DB::transaction(function() use($orderId){
   $order=DB::table('orders')->where('id',$orderId)->lockForUpdate()->firstOrFail();
   if($order->status==='active'){
    return Service::where('order_id',$orderId)->firstOrFail();
   }
   if($order->status!=='paid') throw new RuntimeException('Order is not paid');
   $item=DB::table('order_items')->where('order_id',$orderId)->firstOrFail();
   $plan=DB::table('plans')->where('id',$item->plan_id)->firstOrFail();
   $opKey='provision:'.$item->id;
   $existing=DB::table('provisioning_operations')->where('operation_key',$opKey)->first();
   if($existing && $existing->status==='completed') return Service::where('order_id',$orderId)->firstOrFail();

   if(!$existing) DB::table('provisioning_operations')->insert([
    'id'=>(string)Str::uuid(),'order_item_id'=>$item->id,'operation_key'=>$opKey,'status'=>'running',
    'attempts'=>1,'created_at'=>now(),'updated_at'=>now()
   ]); else DB::table('provisioning_operations')->where('id',$existing->id)->update(['status'=>'running','attempts'=>DB::raw('attempts+1'),'updated_at'=>now()]);

   $node=$this->selector->select();
   $inbound=DB::table('inbounds')->where('node_id',$node->id)->where('active',true)->orderBy('created_at')->first();
   if(!$inbound) throw new RuntimeException('No active inbound exists on selected node');

   $token=SubscriptionToken::generate(); $plain=$token['plain'];
   $clientUuid=(string)Str::uuid();
   $email='bg-'.substr(str_replace('-','',$orderId),0,12);
   $expiry=$plan->duration_days ? now()->addDays($plan->duration_days) : null;
   $totalBytes=$plan->unlimited_traffic ? 0 : ((int)$plan->traffic_gb * 1024**3);
   $payload=['inbound_id'=>$inbound->provider_inbound_id,'client'=>[
    'id'=>$clientUuid,'email'=>$email,'limitIp'=>$plan->device_limit ?? 0,'totalGB'=>$totalBytes,
    'expiryTime'=>$expiry ? $expiry->getTimestampMs() : 0,'enable'=>true,'tgId'=>'','subId'=>Str::random(16),'reset'=>0
   ]];
   try {
    $result=$this->provider->createClient($node,$payload);
    $uri=$this->uriBuilder->build($node,$inbound,$clientUuid,$email);
    if(($result['success']??true)===false) throw new RuntimeException($result['msg']??'3x-ui rejected client');
    $service=Service::create([
     'id'=>(string)Str::uuid(),'user_id'=>$order->user_id,'order_id'=>$order->id,'product_id'=>$item->product_id,
     'plan_id'=>$item->plan_id,'status'=>'active','traffic_limit_bytes'=>$plan->unlimited_traffic?null:$totalBytes,
     'traffic_used_bytes'=>0,'starts_at'=>now(),'expires_at'=>$expiry,'device_limit'=>$plan->device_limit,
     'subscription_token_hash'=>$token['hash'],'subscription_token_plain'=>$plain
    ]);
    DB::table('service_endpoints')->insert([
     'id'=>(string)Str::uuid(),'service_id'=>$service->id,'node_id'=>$node->id,'inbound_id'=>$inbound->id,
     'provider_client_id'=>$email,'uuid'=>$clientUuid,'status'=>'active','traffic_used'=>0,
     'metadata'=>json_encode(['provider_response'=>$result,'uri'=>$uri]),'created_at'=>now(),'updated_at'=>now()
    ]);
    DB::table('orders')->where('id',$orderId)->update(['status'=>'active','updated_at'=>now()]);
    DB::table('provisioning_operations')->where('operation_key',$opKey)->update([
     'status'=>'completed','response_payload'=>json_encode($result),'completed_at'=>now(),'updated_at'=>now()
    ]);
    return $service;
   } catch(\Throwable $e){
    DB::table('provisioning_operations')->where('operation_key',$opKey)->update(['status'=>'failed','last_error'=>$e->getMessage(),'updated_at'=>now()]);
    DB::table('orders')->where('id',$orderId)->update(['status'=>'provisioning_failed','updated_at'=>now()]);
    throw $e;
   }
  });
 }
}