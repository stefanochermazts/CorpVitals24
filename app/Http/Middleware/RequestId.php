<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RequestId
{
    /**
     * Handle an incoming request and assign a unique request ID.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Generate or use existing request ID
        $requestId = $request->header('X-Request-ID') ?? (string) Str::uuid();

        // Set request ID in request attributes for later use
        $request->attributes->set('request-id', $requestId);

        // Execute request
        $response = $next($request);

        // Add request ID to response headers for debugging
        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }
}
