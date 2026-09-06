<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class ApiRequestLogMiddleware {
 public function handle(Request $request,Closure $next): Response {
  $start=microtime(true);
  $response=$next($request);
  try{
   $key=$request->attributes->get('api_key');
   DB::table('api_request_logs')->insert([
    'api_key_id'=>$key->id??null,'user_id'=>$request->user()?->id,
    'method'=>$request->method(),'path'=>'/'.$request->path(),'status_code'=>$response->getStatusCode(),
    'ip'=>$request->ip(),'duration_ms'=>(int)round((microtime(true)-$start)*1000),
    'created_at'=>now(),'updated_at'=>now()
   ]);
  }catch(\Throwable $e){ report($e); }
  return $response;
 }
}