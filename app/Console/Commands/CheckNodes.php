<?php
namespace App\Console\Commands;
use App\Domain\Nodes\Node;
use App\Domain\Provisioning\Providers\ThreeXUIProvider;
use Illuminate\Console\Command;

final class CheckNodes extends Command {
 protected $signature='bluegate:check-nodes';
 protected $description='Check provider nodes and update health/failure state';

 public function handle(ThreeXUIProvider $provider): int {
  foreach(Node::query()->get() as $node){
   try{
    $health=$provider->healthCheck($node); $ok=(bool)($health['ok']??false);
    $node->update([
     'status'=>$ok?'online':'offline',
     'failure_count'=>$ok?0:((int)$node->failure_count+1),
     'offline_since'=>$ok?null:($node->offline_since?:now()),
     'last_heartbeat_at'=>now(),
     'metadata'=>array_merge($node->metadata??[],['last_health'=>$health]),
    ]);
    $this->line($node->slug.': '.($ok?'online':'offline').' failures='.$node->failure_count);
   }catch(\Throwable $e){
    report($e);
    $node->update([
     'status'=>'offline','failure_count'=>(int)$node->failure_count+1,
     'offline_since'=>$node->offline_since?:now(),'last_heartbeat_at'=>now()
    ]);
   }
  }
  return self::SUCCESS;
 }
}