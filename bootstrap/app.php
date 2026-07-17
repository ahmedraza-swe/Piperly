<?php

use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

return Illuminate\Foundation\Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // No web/session middleware — survives missing DB / sessions table
            Route::get('/healthz', function () {
                $database = 'down';
                $error = null;

                try {
                    DB::connection()->getPdo();
                    $database = 'ok';
                } catch (\Throwable $e) {
                    $error = $e->getMessage();
                }

                return response()->json([
                    'status' => $database === 'ok' ? 'ok' : 'degraded',
                    'app' => 'ok',
                    'database' => $database,
                    'db_error' => $error,
                    'session_driver' => config('session.driver'),
                    'cache_driver' => config('cache.default'),
                    'app_url' => config('app.url'),
                    'timestamp' => now()->toIso8601String(),
                ], $database === 'ok' ? 200 : 503);
            })->name('healthz');
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Render / Railway terminate TLS — trust X-Forwarded-* so asset URLs use https
        $middleware->trustProxies(at: '*');

        $middleware->appendToGroup('web', [
            App\Http\Middleware\BlockedUser::class,
            App\Http\Middleware\UpdateUserLastSeenAt::class,
        ]);

        $middleware->alias([
            'sitemapped' => \App\Http\Middleware\Sitemapped::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {})->create();
