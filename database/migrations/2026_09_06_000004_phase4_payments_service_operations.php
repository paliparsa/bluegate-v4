<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
 public function up(): void {
  if (!Schema::hasColumn('payments','idempotency_key')) {
   Schema::table('payments', function(Blueprint $t){
    $t->string('idempotency_key')->nullable()->unique();
   });
  }
  if (!Schema::hasColumn('payments','callback_token')) {
   Schema::table('payments', function(Blueprint $t){
    $t->string('callback_token',64)->nullable()->unique();
   });
  }
  if (!Schema::hasColumn('payments','gateway_payload')) {
   Schema::table('payments', function(Blueprint $t){
    $t->jsonb('gateway_payload')->nullable();
   });
  }

  if (!Schema::hasTable('service_operations')) {
   Schema::create('service_operations', function(Blueprint $t){
    $t->uuid('id')->primary();
    $t->foreignUuid('service_id')->constrained()->cascadeOnDelete();
    $t->foreignId('user_id')->constrained()->cascadeOnDelete();
    $t->string('type');
    $t->string('status')->default('pending');
    $t->decimal('amount',18,2)->default(0);
    $t->string('currency',3)->default('IRR');
    $t->string('idempotency_key')->unique();
    $t->jsonb('request_payload')->nullable();
    $t->jsonb('response_payload')->nullable();
    $t->text('last_error')->nullable();
    $t->timestampTz('completed_at')->nullable();
    $t->timestamps();
   });
  }
 }
 public function down(): void {
  Schema::dropIfExists('service_operations');
  Schema::table('payments', function(Blueprint $t){
   foreach (['idempotency_key','callback_token','gateway_payload'] as $column) {
    if (Schema::hasColumn('payments',$column)) $t->dropColumn($column);
   }
  });
 }
};