<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class AdminMiddleware {
 public function handle(Request $request, Closure $next) {
  abort_unless($request->user() && in_array($request->user()->role,['admin','super_admin']),403);
  return $next($request);
 }
}
