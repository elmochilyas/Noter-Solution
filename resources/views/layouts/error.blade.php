<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', config('app.name', 'Sana Bouhamidi - Notaire'))</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-parchment text-stone-700 antialiased overflow-hidden">
        {{-- Decorative background blobs --}}
        <div class="pointer-events-none fixed inset-0 overflow-hidden">
            <div class="absolute -top-32 -end-32 w-[500px] h-[500px] rounded-full bg-brass-100/60 blur-3xl float-slow"></div>
            <div class="absolute -bottom-32 -start-32 w-[400px] h-[400px] rounded-full bg-brass-200/40 blur-3xl float-delayed"></div>
            <div class="absolute top-1/2 start-1/2 -translate-x-1/2 -translate-y-1/2 w-[200px] h-[200px] rounded-full bg-brass-300/10 blur-2xl breathe"></div>
        </div>

        <main class="relative flex min-h-screen items-center justify-center px-4">
            <div class="max-w-lg w-full text-center page-enter">
                {{-- Logo --}}
                <div class="mb-10">
                    <a href="/{{ app()->getLocale() }}" class="inline-flex flex-col items-center gap-1 group">
                        <span class="text-2xl font-semibold text-ink group-hover:text-brass-600 transition-colors duration-200">
                            Sana Bouhamidi
                        </span>
                        <span class="text-[0.6rem] font-bold uppercase tracking-[0.2em] text-brass-500">Notaire Adoul · Agadir</span>
                    </a>
                    <div class="mx-auto mt-4 h-px w-16 bg-gradient-to-r from-transparent via-brass-400 to-transparent rounded-full"></div>
                </div>

                @yield('content')

                {{-- Decorative ornament --}}
                <div class="mt-10 flex items-center justify-center gap-3 text-brass-300">
                    <div class="h-px w-12 bg-gradient-to-r from-transparent to-brass-300"></div>
                    <svg class="h-3 w-3 text-brass-400" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
                    </svg>
                    <div class="h-px w-12 bg-gradient-to-l from-transparent to-brass-300"></div>
                </div>
            </div>
        </main>
    </body>
</html>
