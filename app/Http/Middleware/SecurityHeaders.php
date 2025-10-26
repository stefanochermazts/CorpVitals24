<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request and add security headers.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Content Security Policy (CSP)
        // Permette risorse solo da domini fidati
        $csp = $this->getContentSecurityPolicy();
        $response->headers->set('Content-Security-Policy', $csp);

        // Strict-Transport-Security (HSTS)
        // Forza HTTPS per 1 anno, include subdomain
        if ($request->secure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        // X-Content-Type-Options
        // Previene MIME-type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // X-Frame-Options
        // Previene clickjacking
        $response->headers->set('X-Frame-Options', 'DENY');

        // X-XSS-Protection
        // Abilita filtro XSS browser (legacy, ma utile per browser vecchi)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer-Policy
        // Controlla informazioni referrer inviate
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions-Policy (ex Feature-Policy)
        // Controlla feature browser disponibili
        $response->headers->set(
            'Permissions-Policy',
            'geolocation=(), microphone=(), camera=(), payment=()'
        );

        // X-Permitted-Cross-Domain-Policies
        // Previene cross-domain policy files
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        return $response;
    }

    /**
     * Get Content Security Policy directives.
     *
     * @return string
     */
    private function getContentSecurityPolicy(): string
    {
        $directives = [
            // Script sources: self + Vite dev server
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' http://localhost:5173",
            "style-src 'self' 'unsafe-inline' http://localhost:5173 https://fonts.bunny.net",
            "img-src 'self' data: https:",
            "font-src 'self' https://fonts.bunny.net",
            "connect-src 'self' ws://localhost:5173 http://localhost:5173",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "object-src 'none'",
        ];

        // In produzione, rimuovi unsafe-inline e unsafe-eval
        if (config('app.env') === 'production') {
            $directives = array_map(
                fn ($directive) => str_replace(
                    ["'unsafe-inline'", "'unsafe-eval'", 'http://localhost:5173', 'ws://localhost:5173'],
                    '',
                    $directive
                ),
                $directives
            );
        }

        return implode('; ', array_filter($directives));
    }
}

