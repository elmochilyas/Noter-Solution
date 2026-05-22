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
        <header class="sticky top-0 z-40 border-b border-stone-200 bg-parchment/95 backdrop-blur-sm">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 md:px-6 lg:px-8">
                <a href="/{{ app()->getLocale() }}" class="text-xl font-semibold text-ink">
                    Sana Bouhamidi
                </a>
                <nav class="flex items-center gap-6 text-sm font-medium">
                    <a href="/{{ app()->getLocale() }}/services" class="text-stone-500 hover:text-ink transition-fast">
                        {{ __('nav.services') }}
                    </a>
                    <a href="/{{ app()->getLocale() }}/consultation" class="text-stone-500 hover:text-ink transition-fast">
                        {{ __('nav.consultation') }}
                    </a>
                    <a href="/{{ app()->getLocale() }}/faq" class="text-stone-500 hover:text-ink transition-fast">
                        {{ __('nav.faq') }}
                    </a>
                    <a href="/{{ app()->getLocale() }}/contact" class="text-stone-500 hover:text-ink transition-fast">
                        {{ __('nav.contact') }}
                    </a>
                    <x-locale-switcher />
                    <a href="/{{ app()->getLocale() }}/portal/login" class="inline-flex items-center justify-center rounded-md bg-brass-500 px-5 py-2.5 text-sm font-medium text-parchment hover:bg-brass-600 transition-fast">
                        {{ __('nav.cta') }}
                    </a>
                </nav>
            </div>
        </header>

        <main>
            @yield('content')
        </main>

        <footer class="border-t border-stone-200 bg-ink text-parchment">
            <div class="mx-auto max-w-7xl px-4 py-12 md:px-6 lg:px-8">
                <div class="grid gap-8 md:grid-cols-3">
                    <div>
                        <h4 class="mb-3 text-sm font-semibold uppercase tracking-wider text-brass-400">
                            {{ __('footer.practice') }}
                        </h4>
                        <p class="text-sm text-stone-300">
                            Sana Bouhamidi<br>
                            {{ __('footer.notaire') }}<br>
                            ICE: {{ config('practice.ice') }}<br>
                            IF: {{ config('practice.if') }}<br>
                            RC: {{ config('practice.rc') }}
                        </p>
                    </div>
                    <div>
                        <h4 class="mb-3 text-sm font-semibold uppercase tracking-wider text-brass-400">
                            {{ __('footer.contact') }}
                        </h4>
                        <p class="text-sm text-stone-300">
                            <bdi dir="ltr">+212 6XX XX XX XX</bdi><br>
                            <bdi dir="ltr">sana@noter.ma</bdi><br>
                            {{ __('footer.address') }}
                        </p>
                    </div>
                    <div>
                        <h4 class="mb-3 text-sm font-semibold uppercase tracking-wider text-brass-400">
                            {{ __('footer.legal') }}
                        </h4>
                        <ul class="space-y-2 text-sm text-stone-300">
                            <li><a href="/{{ app()->getLocale() }}/mentions-legales" class="hover:text-parchment transition-fast">{{ __('footer.legal_notices') }}</a></li>
                            <li><a href="/{{ app()->getLocale() }}/confidentialite" class="hover:text-parchment transition-fast">{{ __('footer.privacy') }}</a></li>
                        </ul>
                    </div>
                </div>
                <div class="mt-10 border-t border-stone-700 pt-6 text-center text-xs text-stone-500">
                    &copy; {{ date('Y') }} Sana Bouhamidi. {{ __('footer.rights') }}
                </div>
            </div>
        </footer>
    </body>
</html>
