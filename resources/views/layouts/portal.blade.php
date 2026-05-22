<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', config('app.name', 'Sana Bouhamidi - Notaire'))</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-parchment text-stone-700 antialiased">
        <header class="sticky top-0 z-40 border-b border-stone-200 bg-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 md:px-6 lg:px-8">
                <a href="/{{ app()->getLocale() }}/portal/dashboard" class="text-lg font-semibold text-ink">
                    Sana Bouhamidi
                </a>
                @auth('client')
                    <form method="POST" action="/{{ app()->getLocale() }}/portal/logout">
                        @csrf
                        <button type="submit" class="text-sm font-medium text-stone-500 hover:text-ink transition-fast">
                            {{ __('auth.logout') }}
                        </button>
                    </form>
                @endauth
            </div>
        </header>

        <main>
            @yield('content')
        </main>
    </body>
</html>
