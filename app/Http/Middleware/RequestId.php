<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class RequestId
{
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = $request->headers->get('X-Request-Id', (string) Str::uuid());
        $response = $next($request);
        $response->headers->set('X-Request-Id', $requestId);
        Log::withContext(['request_id' => $requestId]);
        return $response;
    }
}


