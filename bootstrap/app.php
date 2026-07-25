<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'manage-users' => \App\Http\Middleware\EnsureCanManageUsers::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // "api/*" siempre responde JSON; fuera de ahí, se respeta lo que el
        // cliente pida (Accept: application/json) — así los fetch() de
        // tickets/kanban, tickets/search y admin/categorías (que no viven
        // bajo /api) reciben errores de validación como JSON en vez de una
        // redirección HTML con los errores en la sesión.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
