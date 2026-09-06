<?php
namespace App\Http\Controllers\App;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class TelegramLinkController extends Controller {
 public function create(Request $r){
  $token=Str::random(48);
  DB::table('telegram_link_tokens')->where('user_id',$r->user()->id)->whereNull('used_at')->delete();
  DB::table('telegram_link_tokens')->insert(['id'=>(string)Str::uuid(),'user_id'=>$r->user()->id,'token'=>$token,'expires_at'=>now()->addMinutes(20),'created_at'=>now(),'updated_at'=>now()]);
  $username=ltrim((string)config('bluegate.telegram.bot_username'),'@');
  if(!$username) return back()->with('error','TELEGRAM_BOT_USERNAME تنظیم نشده است.');
  return back()->with('telegram_link',"https://t.me/{$username}?start={$token}");
 }
}