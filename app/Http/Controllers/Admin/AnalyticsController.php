<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

final class AnalyticsController extends Controller {
 public function __invoke(){
  $days=collect(range(29,0))->map(fn($d)=>now()->subDays($d)->toDateString());
  $orders=DB::table('orders')->selectRaw("DATE(created_at) d, COUNT(*) orders, SUM(CASE WHEN status IN ('paid','active') THEN subtotal-discount ELSE 0 END) revenue")
   ->where('created_at','>=',now()->subDays(30))->groupByRaw('DATE(created_at)')->orderBy('d')->get()->keyBy('d');
  $series=$days->map(fn($d)=>['date'=>$d,'orders'=>(int)($orders[$d]->orders??0),'revenue'=>(float)($orders[$d]->revenue??0)]);
  $summary=[
   'revenue30'=>$series->sum('revenue'),
   'orders30'=>$series->sum('orders'),
   'active_services'=>DB::table('services')->where('status','active')->count(),
   'active_nodes'=>DB::table('nodes')->where('status','online')->count(),
   'resellers'=>DB::table('reseller_profiles')->where('active',true)->count(),
   'api_calls24'=>DB::table('api_request_logs')->where('created_at','>=',now()->subDay())->count(),
   'failovers30'=>DB::table('failover_operations')->where('status','completed')->where('created_at','>=',now()->subDays(30))->count(),
  ];
  $nodes=DB::table('nodes')->leftJoin('locations','locations.id','=','nodes.location_id')
   ->select('nodes.name','nodes.status','nodes.failure_count','nodes.last_heartbeat_at','locations.name as location')
   ->orderByDesc('nodes.failure_count')->get();
  return view('admin.analytics',compact('series','summary','nodes'));
 }
}