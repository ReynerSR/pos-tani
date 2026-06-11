<?php

use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\UpdateUserActivity;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);

        $middleware->appendToGroup('web', \Illuminate\Session\Middleware\AuthenticateSession::class);
        $middleware->appendToGroup('web', UpdateUserActivity::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
