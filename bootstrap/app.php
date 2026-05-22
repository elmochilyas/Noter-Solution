<?php

use App\Http\Middleware\AddRequestId;
use App\Http\Middleware\SetLocaleMiddleware;
use App\Http\Middleware\SetSessionLifetime;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'locale' => SetLocaleMiddleware::class,
            'request.id' => AddRequestId::class,
            'session.lifetime' => SetSessionLifetime::class,
        ]);

        $middleware->appendToGroup('web', AddRequestId::class);
        $middleware->appendToGroup('web', SetSessionLifetime::class);
    })
    ->withEvents(false)
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
