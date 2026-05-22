<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ThrottleWebhooks
{
    public function __construct(
        private readonly RateLimiter $limiter,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $key = 'webhook:'.($request->ip() ?? 'unknown');

        if ($this->limiter->tooManyAttempts($key, 1000)) {
            return response('Too Many Attempts.', 429);
        }

        $this->limiter->hit($key, 60);

        return $next($request);
    }
}
