<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetSessionLifetime
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $lifetime = match (true) {
            Auth::guard('client')->check() => 120,
            Auth::guard('web')->check() => 30,
            default => (int) config('session.lifetime', 120),
        };

        config(['session.lifetime' => $lifetime]);

        return $response;
    }
}
