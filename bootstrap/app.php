<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use App\Http\Middleware\RequestId;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Abilita Sanctum per richieste SPA
        $middleware->appendToGroup('web', EnsureFrontendRequestsAreStateful::class);

        // Correlazione richieste nei log
        $middleware->appendToGroup('api', RequestId::class);

        // Rate limiter per API: 60 req/min basato su utente/IP
        RateLimiter::for('api', function (Request $request) {
            $key = optional($request->user())->getAuthIdentifier() ?: $request->ip();
            return Limit::perMinute(60)->by($key);
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Problem Details JSON per errori comuni
        $exceptions->render(function (Throwable $e, Request $request) {
            if (!str_contains((string) $request->header('Accept'), 'json')) {
                return null;
            }

            $status = 500;
            $type = 'about:blank';
            $title = 'Internal Server Error';
            $detail = $e->getMessage();
            $errors = null;

            if ($e instanceof ValidationException) {
                $status = 422;
                $type = 'https://example.com/validation-error';
                $title = 'Validation Failed';
                $errors = $e->errors();
            } elseif ($e instanceof ModelNotFoundException) {
                $status = 404;
                $type = 'https://example.com/not-found';
                $title = 'Resource Not Found';
            } elseif ($e instanceof HttpExceptionInterface) {
                $status = $e->getStatusCode();
                $type = 'https://example.com/http-error';
                $title = $status >= 500 ? 'Server Error' : 'HTTP Error';
            }

            $problem = [
                'type' => $type,
                'title' => $title,
                'status' => $status,
                'detail' => $detail,
                'instance' => (string) $request->fullUrl(),
            ];
            if ($errors) {
                $problem['errors'] = $errors;
            }

            return response()->json($problem, $status)->withHeaders([
                'Content-Type' => 'application/problem+json',
            ]);
        });
    })->create();
