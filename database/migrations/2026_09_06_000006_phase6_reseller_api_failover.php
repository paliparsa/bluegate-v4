<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
 public function up(): void {
  if(!Schema::hasTable('reseller_profiles')) Schema::create('reseller_profiles', function(Blueprint $t){
   $t->id(); $t->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
   $t->decimal('discount_percent',8,4)->default(0); $t->decimal('credit_limit',18,2)->default(0);
   $t->boolean('api_enabled')->default(true); $t->boolean('active')->default(true);
   $t->jsonb('allowed_product_ids')->nullable(); $t->timestamps();
  });

  if(!Schema::hasTable('api_keys')) Schema::create('api_keys', function(Blueprint $t){
   $t->uuid('id')->primary(); $t->foreignId('user_id')->constrained()->cascadeOnDelete();
   $t->string('name',120); $t->char('token_hash',64)->unique(); $t->string('token_prefix',20)->index();
   $t->jsonb('abilities')->nullable(); $t->timestampTz('last_used_at')->nullable();
   $t->timestampTz('expires_at')->nullable(); $t->timestampTz('revoked_at')->nullable(); $t->timestamps();
  });

  if(!Schema::hasTable('api_idempotency_keys')) Schema::create('api_idempotency_keys', function(Blueprint $t){
   $t->bigIncrements('id'); $t->foreignId('user_id')->constrained()->cascadeOnDelete();
   $t->char('key_hash',64); $t->string('scope',80); $t->string('resource_type')->nullable(); $t->string('resource_id')->nullable();
   $t->unsignedSmallInteger('status_code')->nullable(); $t->jsonb('response_body')->nullable(); $t->timestamps();
   $t->unique(['user_id','scope','key_hash']);
  });

  if(!Schema::hasTable('api_request_logs')) Schema::create('api_request_logs', function(Blueprint $t){
   $t->bigIncrements('id'); $t->foreignUuid('api_key_id')->nullable()->constrained('api_keys')->nullOnDelete();
   $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); $t->string('method',10);
   $t->string('path',500); $t->unsignedSmallInteger('status_code')->nullable();
   $t->ipAddress('ip')->nullable(); $t->unsignedInteger('duration_ms')->nullable(); $t->timestamps();
  });

  if(!Schema::hasTable('failover_operations')) Schema::create('failover_operations', function(Blueprint $t){
   $t->uuid('id')->primary(); $t->foreignUuid('service_id')->constrained()->cascadeOnDelete();
   $t->foreignUuid('from_node_id')->nullable()->constrained('nodes')->nullOnDelete();
   $t->foreignUuid('to_node_id')->nullable()->constrained('nodes')->nullOnDelete();
   $t->string('reason')->default('node_unhealthy'); $t->string('status')->default('pending');
   $t->unsignedInteger('attempts')->default(0); $t->text('last_error')->nullable();
   $t->jsonb('metadata')->nullable(); $t->timestampTz('completed_at')->nullable(); $t->timestamps();
   $t->index(['status','created_at']);
  });

  if(!Schema::hasColumn('nodes','failure_count')) Schema::table('nodes', function(Blueprint $t){
   $t->unsignedInteger('failure_count')->default(0);
   $t->timestampTz('offline_since')->nullable();
  });
 }
 public function down(): void {
  if(Schema::hasColumn('nodes','failure_count')) Schema::table('nodes', function(Blueprint $t){
   $t->dropColumn(['failure_count','offline_since']);
  });
  foreach(['failover_operations','api_request_logs','api_idempotency_keys','api_keys','reseller_profiles'] as $table) Schema::dropIfExists($table);
 }
};