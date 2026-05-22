<?php

use App\Http\Middleware\AddRequestId;
use App\Http\Middleware\ContentSecurityPolicy;
use App\Http\Middleware\SetLocaleMiddleware;
use App\Http\Middleware\SetSessionLifetime;
use App\Http\Middleware\ThrottleWebhooks;
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
            'csp' => ContentSecurityPolicy::class,
            'throttle.webhooks' => ThrottleWebhooks::class,
        ]);

        $middleware->appendToGroup('web', AddRequestId::class);
        $middleware->appendToGroup('web', SetSessionLifetime::class);
        $middleware->appendToGroup('web', ContentSecurityPolicy::class);

        $middleware->validateCsrfTokens(except: [
            'webhooks/stripe',
            'webhooks/resend',
            'webhooks/twilio',
        ]);
    })
    ->withEvents(false)
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
