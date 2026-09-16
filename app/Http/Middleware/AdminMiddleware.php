<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('admin.login')->with('error', 'Please login to access the admin panel.');
        }

        if (Auth::user()->role !== 'admin') {
            Auth::logout();
            return redirect()->route('admin.login')->with('error', 'Access denied. You must be an administrator.');
        }

        if (! Auth::user()->is_active) {
            Auth::logout();
            return redirect()->route('admin.login')->with('error', 'Your administrator account is inactive.');
        }

        return $next($request);
    }
}
