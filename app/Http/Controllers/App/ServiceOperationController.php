<?php
namespace App\Http\Controllers\App;
use App\Domain\Notifications\TelegramNotifier;
use App\Domain\Services\Service;
use App\Domain\Services\ServiceOperationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class ServiceOperationController extends Controller {
 private function service(Request $r,string $id): Service {
  return Service::where('id',$id)->where('user_id',$r->user()->id)->firstOrFail();
 }
 public function renew(Request $r,string $id,ServiceOperationService $ops,TelegramNotifier $tg){
  $s=$this->service($r,$id); $key='renew:'.$s->id.':'.$r->validate(['idempotency_key'=>'required|string|max:100'])['idempotency_key'];
  try{ $res=$ops->renew($s,$key); $tg->send($r->user(),"🔄 سرویس شما تمدید شد.\nService: <code>{$s->id}</code>"); return back()->with('success','سرویس با موفقیت تمدید شد.'); }
  catch(\Throwable $e){ report($e); return back()->with('error',$e->getMessage());}
 }
 public function traffic(Request $r,string $id,ServiceOperationService $ops,TelegramNotifier $tg){
  $s=$this->service($r,$id); $d=$r->validate(['gb'=>'required|integer|in:10,25,50,100','idempotency_key'=>'required|string|max:100']);
  try{ $ops->addTraffic($s,(int)$d['gb'],'traffic:'.$s->id.':'.$d['idempotency_key']); $tg->send($r->user(),"📦 {$d['gb']}GB به سرویس شما اضافه شد."); return back()->with('success',$d['gb'].' گیگابایت اضافه شد.');}
  catch(\Throwable $e){ report($e); return back()->with('error',$e->getMessage());}
 }
 public function location(Request $r,string $id,ServiceOperationService $ops,TelegramNotifier $tg){
  $s=$this->service($r,$id); $d=$r->validate(['location_id'=>'required|uuid','idempotency_key'=>'required|string|max:100']);
  try{ $ops->changeLocation($s,$d['location_id'],'location:'.$s->id.':'.$d['idempotency_key']); $loc=DB::table('locations')->where('id',$d['location_id'])->first(); $tg->send($r->user(),"🌍 لوکیشن سرویس به ".($loc->name??'مقصد جدید')." تغییر کرد."); return back()->with('success','لوکیشن با موفقیت تغییر کرد.');}
  catch(\Throwable $e){ report($e); return back()->with('error',$e->getMessage());}
 }
}