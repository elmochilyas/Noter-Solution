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
        <main class="flex min-h-screen items-center justify-center px-4">
            <div class="max-w-md text-center">
                <div class="mb-6">
                    <a href="/{{ app()->getLocale() }}" class="text-xl font-semibold text-ink">
                        Sana Bouhamidi
                    </a>
                </div>
                @yield('content')
            </div>
        </main>
    </body>
</html>
