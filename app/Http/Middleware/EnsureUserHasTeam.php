<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to ensure the authenticated user belongs to a team.
 * 
 * This is critical for multi-tenant security. It prevents users from accessing
 * resources outside their team scope.
 */
class EnsureUserHasTeam
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Allow unauthenticated requests to pass (auth middleware will handle)
        if (!$user) {
            return $next($request);
        }

        // Ensure user has a team assigned
        if (!$user->team_id) {
            abort(403, 'User must be assigned to a team to access this resource.');
        }

        // Set the team context for this request
        // This can be used by Spatie Permission for team-scoped permissions
        app()->instance('current_team_id', $user->team_id);

        return $next($request);
    }
}
