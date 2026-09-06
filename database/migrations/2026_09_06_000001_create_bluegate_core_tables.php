<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $t) {
            $t->uuid('id')->primary(); $t->string('slug')->unique(); $t->string('name');
            $t->text('description')->nullable(); $t->string('category')->default('network_service');
            $t->boolean('active')->default(true); $t->unsignedInteger('sort_order')->default(0);
            $t->jsonb('config')->nullable(); $t->timestamps();
        });

        Schema::create('plans', function (Blueprint $t) {
            $t->uuid('id')->primary(); $t->foreignUuid('product_id')->constrained()->cascadeOnDelete();
            $t->string('name'); $t->unsignedBigInteger('traffic_gb')->nullable(); $t->unsignedInteger('duration_days')->nullable();
            $t->unsignedInteger('device_limit')->nullable(); $t->unsignedInteger('ip_limit')->nullable();
            $t->decimal('base_price', 18, 2)->default(0); $t->string('currency',3)->default('IRR');
            $t->boolean('unlimited_traffic')->default(false); $t->boolean('unlimited_duration')->default(false);
            $t->boolean('renewable')->default(true); $t->boolean('upgradeable')->default(true); $t->boolean('active')->default(true);
            $t->unsignedInteger('sort_order')->default(0); $t->jsonb('config')->nullable(); $t->timestamps();
        });

        Schema::create('locations', function (Blueprint $t) {
            $t->uuid('id')->primary(); $t->string('name'); $t->char('country_code',2); $t->string('city')->nullable();
            $t->string('flag')->nullable(); $t->string('service_type')->nullable(); $t->boolean('active')->default(true);
            $t->unsignedInteger('sort_order')->default(0); $t->timestamps();
        });

        Schema::create('nodes', function (Blueprint $t) {
            $t->uuid('id')->primary(); $t->foreignUuid('location_id')->nullable()->constrained()->nullOnDelete();
            $t->string('name'); $t->string('slug')->unique(); $t->string('provider_type')->default('3xui');
            $t->string('panel_url'); $t->text('credentials'); $t->string('status')->default('unknown');
            $t->unsignedInteger('max_users')->nullable(); $t->unsignedBigInteger('max_traffic_bytes')->nullable();
            $t->unsignedSmallInteger('weight')->default(100); $t->boolean('maintenance_mode')->default(false);
            $t->boolean('sales_enabled')->default(true); $t->timestampTz('last_heartbeat_at')->nullable();
            $t->jsonb('metadata')->nullable(); $t->timestamps();
        });

        Schema::create('inbounds', function (Blueprint $t) {
            $t->uuid('id')->primary(); $t->foreignUuid('node_id')->constrained()->cascadeOnDelete();
            $t->string('provider_inbound_id'); $t->string('protocol'); $t->unsignedInteger('port')->nullable();
            $t->string('name'); $t->string('product_group')->nullable(); $t->boolean('active')->default(true);
            $t->jsonb('settings')->nullable(); $t->timestamps();
            $t->unique(['node_id','provider_inbound_id']);
        });

        Schema::create('wallets', function (Blueprint $t) {
            $t->uuid('id')->primary(); $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('currency',3)->default('IRR'); $t->decimal('balance_cached',18,2)->default(0); $t->timestamps();
            $t->unique(['user_id','currency']);
        });

        Schema::create('orders', function (Blueprint $t) {
            $t->uuid('id')->primary(); $t->string('order_number')->unique(); $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('status')->default('pending_payment'); $t->decimal('subtotal',18,2)->default(0);
            $t->decimal('discount',18,2)->default(0); $t->decimal('wallet_used',18,2)->default(0); $t->decimal('payable',18,2)->default(0);
            $t->string('currency',3)->default('IRR'); $t->timestampTz('paid_at')->nullable(); $t->jsonb('metadata')->nullable(); $t->timestamps();
        });

        Schema::create('order_items', function (Blueprint $t) {
            $t->uuid('id')->primary(); $t->foreignUuid('order_id')->constrained()->cascadeOnDelete();
            $t->foreignUuid('product_id')->constrained()->restrictOnDelete(); $t->foreignUuid('plan_id')->nullable()->constrained()->nullOnDelete();
            $t->unsignedInteger('quantity')->default(1); $t->decimal('unit_price',18,2); $t->decimal('total_price',18,2);
            $t->jsonb('configuration')->nullable(); $t->timestamps();
        });

        Schema::create('payments', function (Blueprint $t) {
            $t->uuid('id')->primary(); $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignUuid('order_id')->nullable()->constrained()->nullOnDelete(); $t->string('gateway');
            $t->decimal('amount',18,2); $t->string('currency',3)->default('IRR'); $t->string('status')->default('created');
            $t->string('authority')->nullable()->index(); $t->string('transaction_id')->nullable()->index();
            $t->jsonb('callback_payload')->nullable(); $t->timestampTz('verified_at')->nullable(); $t->timestamps();
        });

        Schema::create('wallet_transactions', function (Blueprint $t) {
            $t->uuid('id')->primary(); $t->foreignUuid('wallet_id')->constrained()->cascadeOnDelete();
            $t->string('type'); $t->string('direction'); $t->decimal('amount',18,2);
            $t->decimal('balance_before',18,2); $t->decimal('balance_after',18,2);
            $t->nullableMorphs('reference'); $t->text('description')->nullable(); $t->timestamps();
        });

        Schema::create('services', function (Blueprint $t) {
            $t->uuid('id')->primary(); $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignUuid('order_id')->nullable()->constrained()->nullOnDelete(); $t->foreignUuid('product_id')->constrained()->restrictOnDelete();
            $t->foreignUuid('plan_id')->nullable()->constrained()->nullOnDelete(); $t->string('status')->default('provisioning');
            $t->unsignedBigInteger('traffic_limit_bytes')->nullable(); $t->unsignedBigInteger('traffic_used_bytes')->default(0);
            $t->timestampTz('starts_at')->nullable(); $t->timestampTz('expires_at')->nullable(); $t->unsignedInteger('device_limit')->nullable();
            $t->char('subscription_token_hash',64)->unique(); $t->foreignUuid('current_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $t->boolean('auto_renew')->default(false); $t->jsonb('provider_metadata')->nullable(); $t->timestamps();
        });

        Schema::create('service_endpoints', function (Blueprint $t) {
            $t->uuid('id')->primary(); $t->foreignUuid('service_id')->constrained()->cascadeOnDelete();
            $t->foreignUuid('node_id')->constrained()->restrictOnDelete(); $t->foreignUuid('inbound_id')->nullable()->constrained()->nullOnDelete();
            $t->string('provider_client_id'); $t->uuid('uuid')->nullable(); $t->string('status')->default('active');
            $t->unsignedBigInteger('traffic_used')->default(0); $t->timestampTz('last_synced_at')->nullable(); $t->jsonb('metadata')->nullable(); $t->timestamps();
            $t->unique(['service_id','node_id','provider_client_id']);
        });

        Schema::create('usage_snapshots', function (Blueprint $t) {
            $t->bigIncrements('id'); $t->foreignUuid('service_id')->constrained()->cascadeOnDelete(); $t->foreignUuid('node_id')->nullable()->constrained()->nullOnDelete();
            $t->unsignedBigInteger('upload_bytes')->default(0); $t->unsignedBigInteger('download_bytes')->default(0); $t->unsignedBigInteger('total_bytes')->default(0);
            $t->timestampTz('recorded_at')->index();
        });

        Schema::create('provisioning_operations', function (Blueprint $t) {
            $t->uuid('id')->primary(); $t->foreignUuid('order_item_id')->constrained()->cascadeOnDelete();
            $t->string('operation_key')->unique(); $t->string('status')->default('pending'); $t->unsignedInteger('attempts')->default(0);
            $t->jsonb('request_payload')->nullable(); $t->jsonb('response_payload')->nullable(); $t->text('last_error')->nullable();
            $t->timestampTz('completed_at')->nullable(); $t->timestamps();
        });

        Schema::create('admin_audit_logs', function (Blueprint $t) {
            $t->bigIncrements('id'); $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('action'); $t->string('entity_type'); $t->string('entity_id')->nullable(); $t->jsonb('before')->nullable(); $t->jsonb('after')->nullable();
            $t->ipAddress('ip')->nullable(); $t->timestampTz('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        foreach (['admin_audit_logs','provisioning_operations','usage_snapshots','service_endpoints','services','wallet_transactions','payments','order_items','orders','wallets','inbounds','nodes','locations','plans','products'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
