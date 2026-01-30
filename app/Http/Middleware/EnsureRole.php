<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authorize a request by requiring the authenticated user to have at least one role.
 *
 * Used on admin endpoints to restrict access without hard-coding user IDs.
 */
class EnsureRole
{
    /**
     * Allow the request when the user has any of the required roles.
     *
     * Aborts with 401 if unauthenticated and 403 if the user lacks the required role.
     * @param Request $request
     * @param Closure $next
     * @param mixed $roles
     * @return Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        abort(403);
    }
}
