<?php
namespace App\Domain\Failover;

use App\Domain\Nodes\Node;
use App\Domain\Nodes\NodeSelector;
use App\Domain\Provisioning\ConfigUriBuilder;
use App\Domain\Provisioning\Providers\ThreeXUIProvider;
use App\Domain\Services\Service;
use App\Domain\Services\ServiceEndpoint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class FailoverService {
 public function __construct(
  private NodeSelector $selector,
  private ThreeXUIProvider $provider,
  private ConfigUriBuilder $uriBuilder,
 ) {}

 public function migrateEndpoint(ServiceEndpoint $oldEndpoint,string $reason='node_unhealthy'): ServiceEndpoint {
  $service=Service::findOrFail($oldEndpoint->service_id);
  $oldNode=Node::findOrFail($oldEndpoint->node_id);

  $existing=DB::table('failover_operations')
   ->where('service_id',$service->id)
   ->where('from_node_id',$oldNode->id)
   ->whereIn('status',['running','completed'])
   ->latest('created_at')->first();

  if($existing?->status==='completed' && $existing->to_node_id){
   $ready=ServiceEndpoint::where('service_id',$service->id)->where('node_id',$existing->to_node_id)->where('status','active')->first();
   if($ready) return $ready;
  }
  if($existing?->status==='running') throw new RuntimeException('Failover already running for this service.');

  $opId=(string)Str::uuid();
  DB::table('failover_operations')->insert([
   'id'=>$opId,'service_id'=>$service->id,'from_node_id'=>$oldNode->id,'reason'=>$reason,
   'status'=>'running','attempts'=>1,'created_at'=>now(),'updated_at'=>now()
  ]);

  try{
   $target=$this->selector->select($oldNode->location_id,[$oldNode->id]);
   $inbound=DB::table('inbounds')->where('node_id',$target->id)->where('active',true)
    ->where('protocol',optional(DB::table('inbounds')->where('id',$oldEndpoint->inbound_id)->first())->protocol ?? '')
    ->orderBy('created_at')->first();

   if(!$inbound){
    $inbound=DB::table('inbounds')->where('node_id',$target->id)->where('active',true)->orderBy('created_at')->first();
   }
   if(!$inbound) throw new RuntimeException('No active inbound exists on failover target.');

   $client=[
    'id'=>$oldEndpoint->uuid,
    'email'=>$oldEndpoint->provider_client_id,
    'limitIp'=>$service->device_limit ?? 0,
    'totalGB'=>(int)($service->traffic_limit_bytes ?? 0),
    'expiryTime'=>$service->expires_at ? $service->expires_at->getTimestampMs() : 0,
    'enable'=>true,'tgId'=>'','subId'=>Str::random(16),'reset'=>0
   ];

   $result=$this->provider->createClient($target,[
    'inbound_id'=>$inbound->provider_inbound_id,'client'=>$client
   ]);
   if(($result['success']??true)===false) throw new RuntimeException($result['msg']??'Failover target rejected client.');

   $uri=$this->uriBuilder->build($target,$inbound,(string)$oldEndpoint->uuid,$oldEndpoint->provider_client_id);

   $newEndpoint=DB::transaction(function() use($service,$oldEndpoint,$target,$inbound,$result,$uri,$opId){
    $endpoint=ServiceEndpoint::where('service_id',$service->id)->where('node_id',$target->id)
     ->where('provider_client_id',$oldEndpoint->provider_client_id)->where('id','!=',$oldEndpoint->id)->first();
    if($endpoint){
     $endpoint->update([
      'inbound_id'=>$inbound->id,'uuid'=>$oldEndpoint->uuid,'status'=>'active',
      'traffic_used'=>$oldEndpoint->traffic_used,'last_synced_at'=>now(),
      'metadata'=>['provider_response'=>$result,'uri'=>$uri,'failover_from'=>$oldEndpoint->id]
     ]);
    }else{
     $endpoint=ServiceEndpoint::create([
      'id'=>(string)Str::uuid(),'service_id'=>$service->id,'node_id'=>$target->id,'inbound_id'=>$inbound->id,
      'provider_client_id'=>$oldEndpoint->provider_client_id,'uuid'=>$oldEndpoint->uuid,'status'=>'active',
      'traffic_used'=>$oldEndpoint->traffic_used,'last_synced_at'=>now(),
      'metadata'=>['provider_response'=>$result,'uri'=>$uri,'failover_from'=>$oldEndpoint->id]
     ]);
    }
    $oldEndpoint->update(['status'=>'failed_over']);
    $service->update(['current_location_id'=>$target->location_id,'status'=>'active']);
    DB::table('failover_operations')->where('id',$opId)->update([
     'to_node_id'=>$target->id,'status'=>'completed','completed_at'=>now(),
     'metadata'=>json_encode(['new_endpoint_id'=>$endpoint->id]),'updated_at'=>now()
    ]);
    return $endpoint;
   });

   try{
    $oldInbound=DB::table('inbounds')->where('id',$oldEndpoint->inbound_id)->first();
    if($oldInbound){
     $this->provider->deleteClientFromInbound($oldNode,(string)$oldInbound->provider_inbound_id,(string)$oldEndpoint->uuid);
    }
   }catch(\Throwable $cleanupError){ report($cleanupError); }

   return $newEndpoint;
  }catch(\Throwable $e){
   DB::table('failover_operations')->where('id',$opId)->update([
    'status'=>'failed','last_error'=>$e->getMessage(),'updated_at'=>now()
   ]);
   throw $e;
  }
 }

 public function migrateNode(Node $node): array {
  $endpoints=ServiceEndpoint::where('node_id',$node->id)->where('status','active')->get();
  $ok=0; $failed=0;
  foreach($endpoints as $endpoint){
   try{ $this->migrateEndpoint($endpoint); $ok++; }
   catch(\Throwable $e){ report($e); $failed++; }
  }
  return ['migrated'=>$ok,'failed'=>$failed,'total'=>$endpoints->count()];
 }
}