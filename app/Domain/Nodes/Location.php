<?php
namespace App\Domain\Nodes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
final class Location extends Model { use HasUuids; protected $guarded=[]; }
