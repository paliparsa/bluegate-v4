<?php
namespace App\Console\Commands;
use App\Domain\Nodes\Node;
use App\Domain\Provisioning\Providers\ThreeXUIProvider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class SyncUsage extends Command {
 protected $signature='bluegate:sync-usage'; protected $description='Sync active service traffic from 3x-ui';
 public function handle(ThreeXUIProvider $provider): int {
  $eps=DB::table('service_endpoints')->where('status','active')->get();
  foreach($eps as $ep){
   try{
    $node=Node::findOrFail($ep->node_id); $u=$provider->getUsage($node,$ep->provider_client_id);
    $up=(int)($u['up']??0); $down=(int)($u['down']??0); $total=$up+$down;
    DB::transaction(function() use($ep,$up,$down,$total){
     DB::table('service_endpoints')->where('id',$ep->id)->update(['traffic_used'=>$total,'last_synced_at'=>now(),'updated_at'=>now()]);
     DB::table('services')->where('id',$ep->service_id)->update(['traffic_used_bytes'=>$total,'updated_at'=>now()]);
     DB::table('usage_snapshots')->insert(['service_id'=>$ep->service_id,'node_id'=>$ep->node_id,'upload_bytes'=>$up,'download_bytes'=>$down,'total_bytes'=>$total,'recorded_at'=>now()]);
    });
   }catch(\Throwable $e){ $this->warn($ep->id.': '.$e->getMessage()); }
  }
  return self::SUCCESS;
 }
}