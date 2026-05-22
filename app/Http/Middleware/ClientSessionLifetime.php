<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ClientSessionLifetime
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('client')->check()) {
            $client = Auth::guard('client')->user();

            $hardCapHours = 24;
            $idleMinutes = 120;

            if ($client->last_login_at && $client->last_login_at->diffInHours(now()) >= $hardCapHours) {
                Log::info('Client session expired (hard cap)', ['client_id' => $client->id]);
                Auth::guard('client')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('portal.login', ['locale' => $request->route('locale')]);
            }

            $lastActivity = $request->session()->get('client_last_activity');
            if ($lastActivity && now()->diffInMinutes($lastActivity) >= $idleMinutes) {
                Log::info('Client session expired (idle)', ['client_id' => $client->id]);
                Auth::guard('client')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('portal.login', ['locale' => $request->route('locale')]);
            }

            $request->session()->put('client_last_activity', now());
        }

        return $next($request);
    }
}
