<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');

        if (! in_array($locale, ['ar', 'fr'])) {
            $locale = 'ar';
        }

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        if ($request->cookie('locale') !== $locale) {
            cookie()->queue(cookie()->forever('locale', $locale));
        }

        return $next($request);
    }
}
