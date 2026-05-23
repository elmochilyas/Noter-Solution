<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = base64_encode(random_bytes(16));

        view()->share('cspNonce', $nonce);
        $request->attributes->set('csp_nonce', $nonce);

        $response = $next($request);

        $isLocal = app()->environment('local');
        $viteUrl = $isLocal ? 'http://localhost:5173' : '';
        $viteWs = $isLocal ? 'ws://localhost:5173' : '';

        $scriptSrc = "'nonce-{$nonce}' 'self' https://js.stripe.com https://maps.googleapis.com";
        $styleSrc = "'self' 'unsafe-inline' https://fonts.googleapis.com";
        $imgSrc = "'self' data: https://*.stripe.com https://maps.gstatic.com";
        $connectSrc = "'self' https://api.stripe.com https://*.supabase.co";
        $fontSrc = "'self' https://fonts.gstatic.com";

        if ($isLocal) {
            $scriptSrc .= " {$viteUrl} 'unsafe-eval'";
            $styleSrc .= " {$viteUrl}";
            $connectSrc .= " {$viteUrl} {$viteWs}";
            $imgSrc .= ' https://via.placeholder.com';
        }

        $cspParts = [
            "default-src 'self'",
            "script-src {$scriptSrc}",
            "style-src {$styleSrc}",
            "frame-src 'self' https://js.stripe.com https://hooks.stripe.com",
            "img-src {$imgSrc}",
            "connect-src {$connectSrc}",
            "font-src {$fontSrc}",
            "form-action 'self'",
            "base-uri 'self'",
            "frame-ancestors 'none'",
        ];

        $response->headers->set('Content-Security-Policy', implode('; ', $cspParts));

        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        return $response;
    }
}
