<?php

use App\Http\Middleware\ContentSecurityPolicy;
use App\Http\Middleware\ThrottleWebhooks;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation\Response;

test('CSP middleware adds security headers', function () {
    $request = Request::create('/test', 'GET');
    $middleware = new ContentSecurityPolicy;

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->headers->get('Content-Security-Policy'))->toContain("default-src 'self'");
    expect($response->headers->get('Content-Security-Policy'))->toContain('js.stripe.com');
    expect($response->headers->get('Content-Security-Policy'))->toContain('frame-src');
    expect($response->headers->get('Content-Security-Policy'))->toContain('script-src');
});

test('CSP middleware allows Stripe domains', function () {
    $request = Request::create('/test', 'GET');
    $middleware = new ContentSecurityPolicy;

    $response = $middleware->handle($request, fn () => new Response('OK'));

    $csp = $response->headers->get('Content-Security-Policy');
    expect($csp)->toContain('https://js.stripe.com');
    expect($csp)->toContain('https://api.stripe.com');
});

test('webhook throttle allows valid requests', function () {
    $request = Request::create('/webhooks/stripe', 'POST');
    $request->setRouteResolver(fn () => new Route('POST', '/webhooks/stripe', []));

    $middleware = app(ThrottleWebhooks::class);
    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getStatusCode())->toBe(200);
});
