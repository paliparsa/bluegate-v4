<?php
namespace App\Domain\Nodes;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

final class Node extends Model
{
    use HasUuids;
    protected $guarded = [];
    protected $hidden = ['credentials'];
    protected $casts = [
        'credentials' => 'encrypted:array',
        'metadata' => 'array',
        'sales_enabled' => 'boolean',
        'maintenance_mode' => 'boolean',
        'last_heartbeat_at' => 'datetime',
    ];
}
