<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function __invoke()
    {
        $dbOk = true;
        $cacheOk = true;
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $dbOk = false;
        }
        try {
            Cache::put('health:check', 'ok', 5);
            $cacheOk = Cache::get('health:check') === 'ok';
        } catch (\Throwable $e) {
            $cacheOk = false;
        }

        return response()->json([
            'ok' => $dbOk && $cacheOk,
            'db' => $dbOk,
            'cache' => $cacheOk,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}


