<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
 public function up(): void {
  Schema::table('payments', function(Blueprint $t){
   $t->string('idempotency_key')->nullable()->unique()->after('status');
   $t->string('callback_token',64)->nullable()->unique()->after('idempotency_key');
   $t->jsonb('gateway_payload')->nullable()->after('callback_payload');
  });
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
 public function down(): void {
  Schema::dropIfExists('service_operations');
  Schema::table('payments', fn(Blueprint $t)=>$t->dropColumn(['idempotency_key','callback_token','gateway_payload']));
 }
};