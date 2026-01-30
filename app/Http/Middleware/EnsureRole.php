<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class EnsureRole.
 */
class EnsureRole
{
    /**
     * Handle the request.
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
