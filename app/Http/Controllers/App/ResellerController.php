<?php
namespace App\Http\Controllers\App;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class ResellerController extends Controller {
 public function dashboard(Request $r){
  $profile=DB::table('reseller_profiles')->where('user_id',$r->user()->id)->where('active',true)->firstOrFail();
  $keys=DB::table('api_keys')->where('user_id',$r->user()->id)->orderByDesc('created_at')->get();
  $orders=DB::table('orders')->where('user_id',$r->user()->id)->orderByDesc('created_at')->limit(20)->get();
  $wallet=DB::table('wallets')->where('user_id',$r->user()->id)->where('currency','IRR')->first();
  return view('app.reseller',compact('profile','keys','orders','wallet'));
 }

 public function createKey(Request $r){
  $profile=DB::table('reseller_profiles')->where('user_id',$r->user()->id)->where('active',true)->where('api_enabled',true)->firstOrFail();
  $d=$r->validate(['name'=>'required|string|max:120','abilities'=>'nullable|array']);
  $abilities=$d['abilities']??['catalog.read','balance.read','orders.read','orders.write','services.read'];
  $raw='bg_live_'.Str::random(48);
  DB::table('api_keys')->insert([
   'id'=>(string)Str::uuid(),'user_id'=>$r->user()->id,'name'=>$d['name'],
   'token_hash'=>hash('sha256',$raw),'token_prefix'=>substr($raw,0,16),
   'abilities'=>json_encode(array_values(array_unique($abilities))),
   'created_at'=>now(),'updated_at'=>now()
  ]);
  return back()->with('api_key_plain',$raw)->with('success','API Key ساخته شد؛ فقط همین یک‌بار نمایش داده می‌شود.');
 }

 public function revokeKey(Request $r,string $id){
  DB::table('api_keys')->where('id',$id)->where('user_id',$r->user()->id)->update(['revoked_at'=>now(),'updated_at'=>now()]);
  return back()->with('success','API Key باطل شد.');
 }
}