<?php
namespace App\Domain\Notifications;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class NotificationService {
 public function create(int $userId,string $title,string $body,string $type='info',?string $actionUrl=null): void {
  DB::table('user_notifications')->insert([
   'id'=>(string)Str::uuid(),'user_id'=>$userId,'type'=>$type,'title'=>$title,'body'=>$body,
   'action_url'=>$actionUrl,'created_at'=>now(),'updated_at'=>now()
  ]);
 }
}