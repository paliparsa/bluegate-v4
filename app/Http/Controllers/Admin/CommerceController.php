<?php
namespace App\Http\Controllers\Admin;
use App\Domain\Notifications\NotificationService;
use App\Domain\Notifications\TelegramNotifier;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CommerceController extends Controller {
 public function products(){
  $products=DB::table('products')->orderBy('sort_order')->get();
  $plans=DB::table('plans')->orderBy('sort_order')->get()->groupBy('product_id');
  return view('admin.products',compact('products','plans'));
 }
 public function storeProduct(Request $r){
  $d=$r->validate(['name'=>'required|max:120','slug'=>'required|max:120|unique:products,slug','description'=>'nullable|max:3000','active'=>'nullable|boolean','sort_order'=>'nullable|integer|min:0']);
  DB::table('products')->insert(['id'=>(string)Str::uuid(),'name'=>$d['name'],'slug'=>$d['slug'],'description'=>$d['description']??null,'active'=>$r->boolean('active'),'sort_order'=>$d['sort_order']??0,'created_at'=>now(),'updated_at'=>now()]);
  return back()->with('success','محصول ساخته شد.');
 }
 public function updateProduct(Request $r,string $id){
  $d=$r->validate(['name'=>'required|max:120','slug'=>'required|max:120|unique:products,slug,'.$id.',id','description'=>'nullable|max:3000','sort_order'=>'nullable|integer|min:0']);
  DB::table('products')->where('id',$id)->update(['name'=>$d['name'],'slug'=>$d['slug'],'description'=>$d['description']??null,'active'=>$r->boolean('active'),'sort_order'=>$d['sort_order']??0,'updated_at'=>now()]);
  return back()->with('success','محصول بروزرسانی شد.');
 }
 public function storePlan(Request $r){
  $d=$r->validate(['product_id'=>'required|uuid|exists:products,id','name'=>'required|max:120','traffic_gb'=>'nullable|integer|min:0','duration_days'=>'nullable|integer|min:0','device_limit'=>'nullable|integer|min:0','base_price'=>'required|numeric|min:0','sort_order'=>'nullable|integer|min:0']);
  DB::table('plans')->insert(['id'=>(string)Str::uuid(),'product_id'=>$d['product_id'],'name'=>$d['name'],'traffic_gb'=>$d['traffic_gb']??null,'duration_days'=>$d['duration_days']??null,'device_limit'=>$d['device_limit']??null,'base_price'=>$d['base_price'],'currency'=>'IRR','unlimited_traffic'=>$r->boolean('unlimited_traffic'),'unlimited_duration'=>$r->boolean('unlimited_duration'),'renewable'=>$r->boolean('renewable'),'upgradeable'=>true,'active'=>$r->boolean('active'),'trial_enabled'=>$r->boolean('trial_enabled'),'sort_order'=>$d['sort_order']??0,'created_at'=>now(),'updated_at'=>now()]);
  return back()->with('success','پلن ساخته شد.');
 }
 public function updatePlan(Request $r,string $id){
  $d=$r->validate(['name'=>'required|max:120','traffic_gb'=>'nullable|integer|min:0','duration_days'=>'nullable|integer|min:0','device_limit'=>'nullable|integer|min:0','base_price'=>'required|numeric|min:0','sort_order'=>'nullable|integer|min:0']);
  DB::table('plans')->where('id',$id)->update(['name'=>$d['name'],'traffic_gb'=>$d['traffic_gb']??null,'duration_days'=>$d['duration_days']??null,'device_limit'=>$d['device_limit']??null,'base_price'=>$d['base_price'],'unlimited_traffic'=>$r->boolean('unlimited_traffic'),'unlimited_duration'=>$r->boolean('unlimited_duration'),'renewable'=>$r->boolean('renewable'),'active'=>$r->boolean('active'),'trial_enabled'=>$r->boolean('trial_enabled'),'sort_order'=>$d['sort_order']??0,'updated_at'=>now()]);
  return back()->with('success','پلن بروزرسانی شد.');
 }

 public function coupons(){
  $coupons=DB::table('coupons')->orderByDesc('created_at')->paginate(50);
  return view('admin.coupons',compact('coupons'));
 }
 public function storeCoupon(Request $r){
  $d=$r->validate(['code'=>'required|max:60|unique:coupons,code','name'=>'required|max:120','type'=>'required|in:percent,fixed','value'=>'required|numeric|min:0','max_discount'=>'nullable|numeric|min:0','min_order'=>'nullable|numeric|min:0','max_uses'=>'nullable|integer|min:1','max_uses_per_user'=>'required|integer|min:1','starts_at'=>'nullable|date','ends_at'=>'nullable|date|after:starts_at']);
  DB::table('coupons')->insert(['id'=>(string)Str::uuid(),'code'=>strtoupper($d['code']),'name'=>$d['name'],'type'=>$d['type'],'value'=>$d['value'],'max_discount'=>$d['max_discount']??null,'min_order'=>$d['min_order']??null,'max_uses'=>$d['max_uses']??null,'max_uses_per_user'=>$d['max_uses_per_user'],'starts_at'=>$d['starts_at']??null,'ends_at'=>$d['ends_at']??null,'active'=>$r->boolean('active'),'created_at'=>now(),'updated_at'=>now()]);
  return back()->with('success','کد تخفیف ساخته شد.');
 }
 public function toggleCoupon(string $id){
  $c=DB::table('coupons')->where('id',$id)->firstOrFail();
  DB::table('coupons')->where('id',$id)->update(['active'=>!$c->active,'updated_at'=>now()]);
  return back()->with('success','وضعیت کد تخفیف تغییر کرد.');
 }

 public function tickets(){
  $tickets=DB::table('tickets')->join('users','users.id','=','tickets.user_id')->select('tickets.*','users.email')->orderByDesc('tickets.last_message_at')->paginate(50);
  return view('admin.tickets.index',compact('tickets'));
 }
 public function ticket(string $id){
  $ticket=DB::table('tickets')->join('users','users.id','=','tickets.user_id')->select('tickets.*','users.email')->where('tickets.id',$id)->firstOrFail();
  $messages=DB::table('ticket_messages')->where('ticket_id',$id)->orderBy('created_at')->get();
  return view('admin.tickets.show',compact('ticket','messages'));
 }
 public function replyTicket(Request $r,string $id,NotificationService $notifications,TelegramNotifier $telegram){
  $ticket=DB::table('tickets')->where('id',$id)->firstOrFail();
  $d=$r->validate(['message'=>'required|string|max:8000','status'=>'required|in:open,answered,closed']);
  DB::transaction(function() use($r,$id,$d){
   DB::table('ticket_messages')->insert(['id'=>(string)Str::uuid(),'ticket_id'=>$id,'user_id'=>$r->user()->id,'sender_type'=>'admin','message'=>$d['message'],'created_at'=>now(),'updated_at'=>now()]);
   DB::table('tickets')->where('id',$id)->update(['status'=>$d['status'],'last_message_at'=>now(),'updated_at'=>now()]);
  });
  $notifications->create($ticket->user_id,'پاسخ جدید پشتیبانی','برای تیکت '.$ticket->number.' پاسخ جدید ثبت شد.','info',route('app.tickets.show',$ticket->id));
  $user=\App\Models\User::find($ticket->user_id);
  $telegram->send($user,"💬 <b>پاسخ جدید پشتیبانی</b>\nتیکت: <code>{$ticket->number}</code>",false);
  return back()->with('success','پاسخ ارسال شد.');
 }
}