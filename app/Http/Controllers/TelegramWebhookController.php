<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class TelegramWebhookController extends Controller {
 public function __invoke(Request $r,string $secret){
  abort_unless(hash_equals((string)config('bluegate.telegram.webhook_secret'),$secret),404);
  $message=$r->input('message'); if(!$message) return response()->json(['ok'=>true]);
  $chatId=(string)data_get($message,'chat.id'); $text=trim((string)data_get($message,'text',''));
  if(str_starts_with($text,'/start ')){
   $token=trim(substr($text,7));
   $link=DB::table('telegram_link_tokens')->where('token',$token)->whereNull('used_at')->where('expires_at','>',now())->first();
   if($link){
    User::where('id',$link->user_id)->update(['telegram_id'=>$chatId]);
    DB::table('telegram_link_tokens')->where('id',$link->id)->update(['used_at'=>now(),'updated_at'=>now()]);
    $this->send($chatId,"✅ حساب Telegram با BlueGate متصل شد.");
   } else $this->send($chatId,"❌ لینک اتصال نامعتبر یا منقضی شده است.");
  } elseif($text==='/start') {
   $this->send($chatId,"BlueGate Bot\nبرای اتصال حساب، لینک ساخته‌شده داخل داشبورد BlueGate را باز کن.");
  }
  return response()->json(['ok'=>true]);
 }
 private function send(string $chatId,string $text): void {
  $token=(string)config('bluegate.telegram.bot_token'); if(!$token) return;
  try{\Illuminate\Support\Facades\Http::timeout(8)->asForm()->post("https://api.telegram.org/bot{$token}/sendMessage",['chat_id'=>$chatId,'text'=>$text]);}catch(\Throwable $e){report($e);}
 }
}