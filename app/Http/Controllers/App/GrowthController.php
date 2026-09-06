<?php
namespace App\Http\Controllers\App;
use App\Domain\Growth\ReferralService;
use App\Domain\Growth\TrialService;
use App\Domain\Notifications\NotificationService;
use App\Domain\Provisioning\ProvisionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class GrowthController extends Controller {
 public function referral(Request $r,ReferralService $refs){
  $code=$refs->ensureCode($r->user());
  $stats=[
   'invited'=>DB::table('users')->where('referred_by_user_id',$r->user()->id)->count(),
   'earned'=>DB::table('referral_commissions')->where('referrer_user_id',$r->user()->id)->sum('commission'),
  ];
  return view('app.referral',compact('code','stats'));
 }
 public function trial(Request $r,TrialService $trials,ProvisionService $provisioner,NotificationService $notifications){
  try{
   $serviceId=$trials->claim($r,$provisioner);
   $notifications->create($r->user()->id,'تست رایگان فعال شد','سرویس تست شما آماده استفاده است.','success',route('app.services.show',$serviceId));
   return redirect()->route('app.services.show',$serviceId)->with('success','سرویس تست با موفقیت ساخته شد.');
  }catch(\Throwable $e){ report($e); return back()->with('error',$e->getMessage()); }
 }
 public function notifications(Request $r){
  $items=DB::table('user_notifications')->where('user_id',$r->user()->id)->orderByDesc('created_at')->paginate(30);
  return view('app.notifications',compact('items'));
 }
 public function readNotification(Request $r,string $id){
  DB::table('user_notifications')->where('id',$id)->where('user_id',$r->user()->id)->update(['read_at'=>now(),'updated_at'=>now()]);
  return back();
 }
}