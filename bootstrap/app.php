<?php

use App\Http\Middleware\Admin;
use App\Http\Middleware\Translater;
use App\Http\Middleware\UpdateLastSeenMiddleware;
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
        // Глобальные middleware (выполняются на каждом запросе)
        $middleware->append(UpdateLastSeenMiddleware::class);

        // Регистрируем middleware для использования в маршрутах (аналог $routeMiddleware)
        $middleware->alias([
            'admin' => \App\Http\Middleware\Admin::class,
            'translater' => \App\Http\Middleware\Translater::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
