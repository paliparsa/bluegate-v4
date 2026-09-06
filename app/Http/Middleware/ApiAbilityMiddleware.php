<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ApiAbilityMiddleware {
 public function handle(Request $request,Closure $next,string $ability): Response {
  $key=$request->attributes->get('api_key');
  $abilities=(array)json_decode($key->abilities??'[]',true);
  if(!in_array('*',$abilities,true) && !in_array($ability,$abilities,true)){
   return response()->json(['message'=>'API key lacks required ability: '.$ability],403);
  }
  return $next($request);
 }
}