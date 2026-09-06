<?php
namespace App\Http\Controllers\Admin;
use App\Domain\Nodes\Node;
use App\Domain\Provisioning\Providers\ThreeXUIProvider;
use App\Domain\Wallet\WalletService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class OperationsController extends Controller {
 public function nodes(){ return view('admin.nodes',['nodes'=>Node::with('location')->latest()->get(),'locations'=>DB::table('locations')->where('active',true)->get()]); }
 public function storeNode(Request $r){
  $d=$r->validate(['name'=>'required|max:120','slug'=>'required|max:120|unique:nodes,slug','panel_url'=>'required|url','username'=>'required','password'=>'required','location_id'=>'nullable|uuid','public_host'=>'nullable|max:255']);
  Node::create(['id'=>(string)Str::uuid(),'name'=>$d['name'],'slug'=>$d['slug'],'panel_url'=>rtrim($d['panel_url'],'/'),
   'provider_type'=>'3xui','credentials'=>['username'=>$d['username'],'password'=>$d['password']],
   'location_id'=>$d['location_id']?:null,'metadata'=>['public_host'=>$d['public_host']?:parse_url($d['panel_url'],PHP_URL_HOST)],'status'=>'unknown','sales_enabled'=>false,'weight'=>100]);
  return back()->with('success','نود ذخیره شد. حالا Sync/Health را اجرا کن.');
 }
 public function health(Node $node, ThreeXUIProvider $provider){
  $h=$provider->healthCheck($node); $node->update(['status'=>($h['ok']??false)?'online':'offline','last_heartbeat_at'=>now(),'metadata'=>array_merge($node->metadata??[],['last_health'=>$h])]);
  return back()->with(($h['ok']??false)?'success':'error',($h['ok']??false)?'اتصال به 3x-ui موفق بود.':'اتصال ناموفق بود: '.($h['error']??$h['status']??'unknown'));
 }
 public function sync(Node $node, ThreeXUIProvider $provider){
  try{
   $items=$provider->listInbounds($node);
   foreach($items as $i){
    DB::table('inbounds')->updateOrInsert(['node_id'=>$node->id,'provider_inbound_id'=>(string)($i['id']??'')],[
     'id'=>(string)Str::uuid(),'protocol'=>$i['protocol']??'unknown','port'=>$i['port']??null,'name'=>$i['remark']??('Inbound '.($i['id']??'')),
     'active'=>(bool)($i['enable']??true),'settings'=>json_encode($i),'updated_at'=>now(),'created_at'=>now()
    ]);
   }
   $node->update(['status'=>'online','sales_enabled'=>true,'last_heartbeat_at'=>now()]);
   return back()->with('success',count($items).' inbound سینک شد و فروش نود فعال شد.');
  }catch(\Throwable $e){ report($e); $node->update(['status'=>'offline']); return back()->with('error','Sync ناموفق: '.$e->getMessage());}
 }
 public function wallets(){ return view('admin.wallets',['users'=>User::orderByDesc('id')->limit(100)->get()]); }
 public function credit(Request $r, WalletService $wallets){
  $d=$r->validate(['email'=>'required|email','amount'=>'required|numeric|min:1']);
  $user=User::where('email',$d['email'])->firstOrFail(); $wallet=$wallets->walletFor($user->id);
  $wallets->credit($wallet,(float)$d['amount'],'admin_credit','شارژ دستی توسط مدیریت');
  return back()->with('success','کیف پول کاربر شارژ شد.');
 }
}