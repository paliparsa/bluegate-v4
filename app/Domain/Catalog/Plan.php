<?php
namespace App\Domain\Catalog;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

final class Plan extends Model
{
    use HasUuids;
    protected $guarded = [];
    protected $casts = ['active'=>'boolean','renewable'=>'boolean','upgradeable'=>'boolean','unlimited_traffic'=>'boolean','unlimited_duration'=>'boolean','config'=>'array'];
    public function product() { return $this->belongsTo(Product::class); }
}
