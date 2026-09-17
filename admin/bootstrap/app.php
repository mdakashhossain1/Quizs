<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\EnsurePasswordChanged;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'password.changed' => EnsurePasswordChanged::class,
        ]);

        // api/* has no session/login-redirect flow of its own (only
        // admin/* does, under the 'web' guard). Must be set here, in the
        // withMiddleware() callback, not in withExceptions(): Laravel's
        // ApplicationBuilder always applies its own default
        // redirectGuestsTo(route('login')) immediately before invoking
        // this callback, so setting it any earlier gets silently
        // overwritten back to the crashing default.
        $middleware->redirectGuestsTo(fn () => null);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Without this, an unauthenticated api/* request that doesn't send
        // "Accept: application/json" (curl, some HTTP clients, webhooks)
        // falls back to Handler::unauthenticated()'s own route('login')
        // fallback — which still doesn't exist in this app — instead of a
        // clean 401.
        $exceptions->shouldRenderJsonWhen(fn ($request, Throwable $e) => $request->is('api/*') || $request->expectsJson());
    })->create();
