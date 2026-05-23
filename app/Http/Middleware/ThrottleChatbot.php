<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ThrottleChatbot
{
    private const SESSION_LIMIT = 30;

    private const SESSION_WINDOW = 60;

    private const IP_LIMIT = 100;

    private const IP_WINDOW = 1440;

    public function handle(Request $request, Closure $next): Response
    {
        $sessionKey = 'chatbot_rate_session:'.session()->getId();
        $ip = $request->ip();
        $ipKey = 'chatbot_rate_ip:'.$ip;

        $sessionCount = (int) Cache::get($sessionKey, 0);
        $ipCount = (int) Cache::get($ipKey, 0);

        if ($sessionCount >= self::SESSION_LIMIT) {
            Log::warning('Chatbot rate limit hit (session)', ['session' => session()->getId()]);

            return response()->json([
                'error' => 'rate_limited',
                'message' => __('chatbot.rate_limit_exceeded'),
                'retry_after' => self::SESSION_WINDOW,
            ], 429);
        }

        if ($ipCount >= self::IP_LIMIT) {
            Log::warning('Chatbot rate limit hit (IP)', ['ip' => $ip]);

            return response()->json([
                'error' => 'rate_limited',
                'message' => __('chatbot.rate_limit_exceeded'),
                'retry_after' => self::IP_WINDOW,
            ], 429);
        }

        $response = $next($request);

        if ($response->isSuccessful()) {
            Cache::put($sessionKey, $sessionCount + 1, now()->addMinutes(self::SESSION_WINDOW));
            Cache::put($ipKey, $ipCount + 1, now()->addMinutes(self::IP_WINDOW));
        }

        return $response;
    }
}
