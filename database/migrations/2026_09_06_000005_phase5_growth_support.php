<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
 public function up(): void {
  Schema::table('users', function(Blueprint $t){
   if(!Schema::hasColumn('users','referral_code')) $t->string('referral_code',20)->nullable()->unique();
   if(!Schema::hasColumn('users','referred_by_user_id')) $t->foreignId('referred_by_user_id')->nullable()->constrained('users')->nullOnDelete();
  });
  if(!Schema::hasColumn('plans','trial_enabled')) Schema::table('plans', function(Blueprint $t){
   $t->boolean('trial_enabled')->default(false);
  });

  if(!Schema::hasTable('coupons')) Schema::create('coupons', function(Blueprint $t){
   $t->uuid('id')->primary(); $t->string('code',60)->unique(); $t->string('name');
   $t->string('type')->default('percent'); $t->decimal('value',18,2);
   $t->decimal('max_discount',18,2)->nullable(); $t->decimal('min_order',18,2)->nullable();
   $t->unsignedInteger('max_uses')->nullable(); $t->unsignedInteger('max_uses_per_user')->default(1);
   $t->timestampTz('starts_at')->nullable(); $t->timestampTz('ends_at')->nullable();
   $t->boolean('active')->default(true); $t->jsonb('product_ids')->nullable(); $t->jsonb('plan_ids')->nullable();
   $t->timestamps();
  });

  if(!Schema::hasTable('coupon_redemptions')) Schema::create('coupon_redemptions', function(Blueprint $t){
   $t->uuid('id')->primary(); $t->foreignUuid('coupon_id')->constrained()->cascadeOnDelete();
   $t->foreignId('user_id')->constrained()->cascadeOnDelete(); $t->foreignUuid('order_id')->constrained()->cascadeOnDelete();
   $t->decimal('discount',18,2); $t->timestamps(); $t->unique(['coupon_id','order_id']);
  });

  if(!Schema::hasTable('referral_commissions')) Schema::create('referral_commissions', function(Blueprint $t){
   $t->uuid('id')->primary(); $t->foreignId('referrer_user_id')->constrained('users')->cascadeOnDelete();
   $t->foreignId('referred_user_id')->constrained('users')->cascadeOnDelete();
   $t->foreignUuid('order_id')->constrained()->cascadeOnDelete(); $t->decimal('base_amount',18,2);
   $t->decimal('rate',8,4); $t->decimal('commission',18,2); $t->string('status')->default('credited');
   $t->timestamps(); $t->unique('order_id');
  });

  if(!Schema::hasTable('trial_claims')) Schema::create('trial_claims', function(Blueprint $t){
   $t->uuid('id')->primary(); $t->foreignId('user_id')->constrained()->cascadeOnDelete();
   $t->foreignUuid('plan_id')->nullable()->constrained('plans')->nullOnDelete();
   $t->foreignUuid('service_id')->nullable()->constrained('services')->nullOnDelete();
   $t->string('status')->default('created'); $t->ipAddress('ip')->nullable(); $t->string('fingerprint',128)->nullable();
   $t->text('last_error')->nullable(); $t->timestamps(); $t->unique('user_id');
  });

  if(!Schema::hasTable('tickets')) Schema::create('tickets', function(Blueprint $t){
   $t->uuid('id')->primary(); $t->string('number',30)->unique(); $t->foreignId('user_id')->constrained()->cascadeOnDelete();
   $t->string('subject'); $t->string('department')->default('support'); $t->string('priority')->default('normal');
   $t->string('status')->default('open'); $t->timestampTz('last_message_at')->nullable(); $t->timestamps();
  });

  if(!Schema::hasTable('ticket_messages')) Schema::create('ticket_messages', function(Blueprint $t){
   $t->uuid('id')->primary(); $t->foreignUuid('ticket_id')->constrained()->cascadeOnDelete();
   $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); $t->string('sender_type')->default('user');
   $t->text('message'); $t->jsonb('attachments')->nullable(); $t->timestamps();
  });

  if(!Schema::hasTable('user_notifications')) Schema::create('user_notifications', function(Blueprint $t){
   $t->uuid('id')->primary(); $t->foreignId('user_id')->constrained()->cascadeOnDelete();
   $t->string('type')->default('info'); $t->string('title'); $t->text('body'); $t->string('action_url')->nullable();
   $t->timestampTz('read_at')->nullable(); $t->timestamps();
  });

  if(!Schema::hasTable('telegram_link_tokens')) Schema::create('telegram_link_tokens', function(Blueprint $t){
   $t->uuid('id')->primary(); $t->foreignId('user_id')->constrained()->cascadeOnDelete(); $t->string('token',64)->unique();
   $t->timestampTz('expires_at'); $t->timestampTz('used_at')->nullable(); $t->timestamps();
  });
 }
 public function down(): void {
  foreach(['telegram_link_tokens','user_notifications','ticket_messages','tickets','trial_claims','referral_commissions','coupon_redemptions','coupons'] as $table) Schema::dropIfExists($table);
  if(Schema::hasColumn('plans','trial_enabled')) Schema::table('plans', fn(Blueprint $t)=>$t->dropColumn('trial_enabled'));
  Schema::table('users', function(Blueprint $t){
   if(Schema::hasColumn('users','referred_by_user_id')) $t->dropConstrainedForeignId('referred_by_user_id');
   if(Schema::hasColumn('users','referral_code')) $t->dropColumn('referral_code');
  });
 }
};