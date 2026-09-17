<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    /**
     * Block API access until a user created with a temporary password has
     * set their own one. Only /auth/me, /auth/force-change-password and
     * /auth/logout are reachable while this flag is set.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->must_change_password) {
            return response()->json([
                'success' => false,
                'message' => 'You must set a new password before continuing.',
                'must_change_password' => true,
            ], 403);
        }

        return $next($request);
    }
}
