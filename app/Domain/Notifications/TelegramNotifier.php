<?php
namespace App\Domain\Notifications;
use App\Models\User;
use Illuminate\Support\Facades\Http;

final class TelegramNotifier {
 public function send(?User $user,string $message,bool $alsoAdmin=true): void {
  $token=(string)config('bluegate.telegram.bot_token');
  if(!$token) return;
  $targets=[];
  if($user?->telegram_id) $targets[]=(string)$user->telegram_id;
  if($alsoAdmin && config('bluegate.telegram.admin_chat_id')) $targets[]=(string)config('bluegate.telegram.admin_chat_id');
  foreach(array_unique($targets) as $chatId){
   try{
    Http::timeout(8)->asForm()->post("https://api.telegram.org/bot{$token}/sendMessage",[
     'chat_id'=>$chatId,'text'=>$message,'parse_mode'=>'HTML','disable_web_page_preview'=>true
    ]);
   }catch(\Throwable $e){ report($e); }
  }
 }
}