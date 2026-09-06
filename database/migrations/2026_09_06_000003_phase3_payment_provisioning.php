<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('orders', function(Blueprint $t){
   $t->string('payment_method')->nullable()->after('currency');
  });
  Schema::table('services', function(Blueprint $t){
   $t->string('subscription_token_plain',128)->nullable()->after('subscription_token_hash');
  });
 }
 public function down(): void {
  Schema::table('services', fn(Blueprint $t)=>$t->dropColumn('subscription_token_plain'));
  Schema::table('orders', fn(Blueprint $t)=>$t->dropColumn('payment_method'));
 }
};