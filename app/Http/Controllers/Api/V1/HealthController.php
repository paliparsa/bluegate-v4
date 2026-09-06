<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

final class HealthController
{
    public function __invoke(): JsonResponse
    {
        $checks = ['database' => false, 'redis' => false];

        try { DB::select('select 1'); $checks['database'] = true; } catch (\Throwable) {}
        try { Redis::ping(); $checks['redis'] = true; } catch (\Throwable) {}

        $ok = !in_array(false, $checks, true);

        return response()->json([
            'ok' => $ok,
            'service' => 'bluegate-api',
            'checks' => $checks,
            'time' => now()->toIso8601String(),
        ], $ok ? 200 : 503);
    }
}
