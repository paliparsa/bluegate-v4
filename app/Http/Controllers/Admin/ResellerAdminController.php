<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class ResellerAdminController extends Controller {
 public function index(){
  $resellers=DB::table('reseller_profiles')->join('users','users.id','=','reseller_profiles.user_id')
   ->select('reseller_profiles.*','users.email','users.name')->orderByDesc('reseller_profiles.created_at')->paginate(50);
  return view('admin.resellers',compact('resellers'));
 }

 public function store(Request $r){
  $d=$r->validate(['email'=>'required|email|exists:users,email','discount_percent'=>'required|numeric|min:0|max:100','credit_limit'=>'nullable|numeric|min:0']);
  $user=User::where('email',$d['email'])->firstOrFail();
  DB::table('reseller_profiles')->updateOrInsert(['user_id'=>$user->id],[
   'discount_percent'=>$d['discount_percent'],'credit_limit'=>$d['credit_limit']??0,
   'api_enabled'=>$r->boolean('api_enabled'),'active'=>$r->boolean('active'),
   'updated_at'=>now(),'created_at'=>now()
  ]);
  $user->update(['role'=>'reseller']);
  return back()->with('success','Reseller فعال شد.');
 }

 public function update(Request $r,int $id){
  $d=$r->validate(['discount_percent'=>'required|numeric|min:0|max:100','credit_limit'=>'nullable|numeric|min:0']);
  DB::table('reseller_profiles')->where('id',$id)->update([
   'discount_percent'=>$d['discount_percent'],'credit_limit'=>$d['credit_limit']??0,
   'api_enabled'=>$r->boolean('api_enabled'),'active'=>$r->boolean('active'),'updated_at'=>now()
  ]);
  return back()->with('success','Reseller بروزرسانی شد.');
 }
}