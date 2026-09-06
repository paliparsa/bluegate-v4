<?php
namespace App\Domain\Nodes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
final class Node extends Model {
 use HasUuids; protected $guarded=[];
 protected $casts=['credentials'=>'encrypted:array','metadata'=>'array','maintenance_mode'=>'boolean','sales_enabled'=>'boolean','last_heartbeat_at'=>'datetime'];
 public function location(){ return $this->belongsTo(\App\Domain\Nodes\Location::class,'location_id'); }
}