<?php
namespace App\Domain\Catalog;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

final class Product extends Model
{
    use HasUuids;
    protected $guarded = [];
    protected $casts = ['active' => 'boolean', 'config' => 'array'];
    public function plans() { return $this->hasMany(Plan::class); }
}
