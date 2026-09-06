<?php
namespace App\Domain\Services;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

final class Service extends Model
{
    use HasUuids;
    protected $guarded = [];
    protected $hidden = ['subscription_token_hash'];
    protected $casts = ['starts_at'=>'datetime','expires_at'=>'datetime','auto_renew'=>'boolean','provider_metadata'=>'array','subscription_token_plain'=>'encrypted'];
    public function endpoints() { return $this->hasMany(ServiceEndpoint::class); }
}
