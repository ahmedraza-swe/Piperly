<?php

use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Illuminate\Foundation\Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
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
