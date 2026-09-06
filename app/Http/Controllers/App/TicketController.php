<?php
namespace App\Http\Controllers\App;
use App\Domain\Notifications\TelegramNotifier;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class TicketController extends Controller {
 public function index(Request $r){
  $tickets=DB::table('tickets')->where('user_id',$r->user()->id)->orderByDesc('last_message_at')->paginate(25);
  return view('app.tickets.index',compact('tickets'));
 }
 public function store(Request $r,TelegramNotifier $tg){
  $d=$r->validate(['subject'=>'required|string|max:190','department'=>'required|in:support,sales,technical,billing','priority'=>'required|in:low,normal,high','message'=>'required|string|max:8000']);
  $id=(string)Str::uuid(); $number='T'.now()->format('ymd').strtoupper(Str::random(5));
  DB::transaction(function() use($r,$d,$id,$number){
   DB::table('tickets')->insert(['id'=>$id,'number'=>$number,'user_id'=>$r->user()->id,'subject'=>$d['subject'],'department'=>$d['department'],'priority'=>$d['priority'],'status'=>'open','last_message_at'=>now(),'created_at'=>now(),'updated_at'=>now()]);
   DB::table('ticket_messages')->insert(['id'=>(string)Str::uuid(),'ticket_id'=>$id,'user_id'=>$r->user()->id,'sender_type'=>'user','message'=>$d['message'],'created_at'=>now(),'updated_at'=>now()]);
  });
  $tg->send(null,"🎫 <b>تیکت جدید</b>\n{$number}\n".$d['subject'],true);
  return redirect()->route('app.tickets.show',$id)->with('success','تیکت ثبت شد.');
 }
 public function show(Request $r,string $id){
  $ticket=DB::table('tickets')->where('id',$id)->where('user_id',$r->user()->id)->firstOrFail();
  $messages=DB::table('ticket_messages')->where('ticket_id',$id)->orderBy('created_at')->get();
  return view('app.tickets.show',compact('ticket','messages'));
 }
 public function reply(Request $r,string $id){
  $ticket=DB::table('tickets')->where('id',$id)->where('user_id',$r->user()->id)->firstOrFail();
  if($ticket->status==='closed') return back()->with('error','این تیکت بسته شده است.');
  $d=$r->validate(['message'=>'required|string|max:8000']);
  DB::transaction(function() use($r,$id,$d){
   DB::table('ticket_messages')->insert(['id'=>(string)Str::uuid(),'ticket_id'=>$id,'user_id'=>$r->user()->id,'sender_type'=>'user','message'=>$d['message'],'created_at'=>now(),'updated_at'=>now()]);
   DB::table('tickets')->where('id',$id)->update(['status'=>'open','last_message_at'=>now(),'updated_at'=>now()]);
  });
  return back()->with('success','پیام ارسال شد.');
 }
}