<?php
namespace App\Console\Commands;
use App\Domain\Failover\FailoverService;
use App\Domain\Nodes\Node;
use Illuminate\Console\Command;

final class RunFailover extends Command {
 protected $signature='bluegate:failover {--node=}';
 protected $description='Fail over services from unhealthy nodes';

 public function handle(FailoverService $failover): int {
  $threshold=(int)config('bluegate.failover.failure_threshold',3);
  $query=Node::query()->where('status','offline')->where('failure_count','>=',$threshold);
  if($this->option('node')) $query->where('id',$this->option('node'));
  $nodes=$query->get();

  foreach($nodes as $node){
   if(!$node->location_id){
    $this->warn($node->slug.' skipped: no location');
    continue;
   }
   try{
    $result=$failover->migrateNode($node);
    $this->info($node->slug.': migrated='.$result['migrated'].' failed='.$result['failed']);
   }catch(\Throwable $e){
    report($e); $this->error($node->slug.': '.$e->getMessage());
   }
  }
  return self::SUCCESS;
 }
}