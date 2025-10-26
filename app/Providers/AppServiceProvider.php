<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\KpiRepositoryInterface;
use App\Repositories\KpiRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(KpiRepositoryInterface::class, KpiRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    /**
     * Configure rate limiting for different routes.
     */
    private function configureRateLimiting(): void
    {
        // API Rate Limiter: 60 req/min per authenticated user or IP
        RateLimiter::for('api', function (Request $request) {
            $key = optional($request->user())->getAuthIdentifier() ?: $request->ip();
            return Limit::perMinute(60)->by($key)->response(function () {
                return response()->json([
                    'type' => 'https://corpvitals24.test/rate-limit-exceeded',
                    'title' => 'Rate Limit Exceeded',
                    'status' => 429,
                    'detail' => 'Too many requests. Please try again later.',
                ], 429, ['Content-Type' => 'application/problem+json']);
            });
        });

        // Auth Rate Limiter: 5 login attempts per minute per IP
        // Previene brute force attacks
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip())->response(function () {
                return back()->withErrors([
                    'email' => 'Too many login attempts. Please try again in 1 minute.',
                ])->with('error', 'Rate limit exceeded. Please wait before trying again.');
            });
        });

        // Web Rate Limiter: 120 req/min per IP (più permissivo per navigazione)
        RateLimiter::for('web', function (Request $request) {
            return Limit::perMinute(120)->by($request->ip());
        });

        // Global Rate Limiter: 1000 req/hour per IP (protezione DDoS)
        RateLimiter::for('global', function (Request $request) {
            return Limit::perHour(1000)->by($request->ip())->response(function () {
                return response()->view('errors.429', [], 429);
            });
        });
    }
}
