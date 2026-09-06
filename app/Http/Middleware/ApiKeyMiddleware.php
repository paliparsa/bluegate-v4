<?php
namespace App\Http\Middleware;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class ApiKeyMiddleware {
 public function handle(Request $request,Closure $next): Response {
  $raw=$request->bearerToken();
  if(!$raw || !str_starts_with($raw,'bg_live_')) return response()->json(['message'=>'Invalid API key'],401);
  $hash=hash('sha256',$raw);
  $key=DB::table('api_keys')->where('token_hash',$hash)->whereNull('revoked_at')->first();
  if(!$key || ($key->expires_at && now()->gt($key->expires_at))) return response()->json(['message'=>'Invalid or expired API key'],401);
  $profile=DB::table('reseller_profiles')->where('user_id',$key->user_id)->where('active',true)->where('api_enabled',true)->first();
  if(!$profile) return response()->json(['message'=>'Reseller API access disabled'],403);
  $user=User::find($key->user_id); if(!$user || $user->status!=='active') return response()->json(['message'=>'Account disabled'],403);
  $request->setUserResolver(fn()=>$user);
  $request->attributes->set('api_key',$key);
  $request->attributes->set('reseller_profile',$profile);
  DB::table('api_keys')->where('id',$key->id)->update(['last_used_at'=>now(),'updated_at'=>now()]);
  return $next($request);
 }
}