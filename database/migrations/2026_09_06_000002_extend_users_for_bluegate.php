<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('users', function(Blueprint $t){
   if (!Schema::hasColumn('users','phone')) $t->string('phone')->nullable()->unique();
   if (!Schema::hasColumn('users','role')) $t->string('role')->default('user')->index();
   if (!Schema::hasColumn('users','status')) $t->string('status')->default('active')->index();
   if (!Schema::hasColumn('users','telegram_id')) $t->string('telegram_id')->nullable()->unique();
  });
 }
 public function down(): void {
  Schema::table('users', function(Blueprint $t){ foreach(['phone','role','status','telegram_id'] as $c) if(Schema::hasColumn('users',$c)) $t->dropColumn($c); });
 }
};
