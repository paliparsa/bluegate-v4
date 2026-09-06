<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
class Wallet extends Model { use HasUuids; protected $guarded=[]; protected $casts=['balance_cached'=>'decimal:2']; }
