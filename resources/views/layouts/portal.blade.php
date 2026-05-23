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
                    {{ config('app.name') }}
                </a>

                @auth('client')
                    <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
                        <a href="/{{ app()->getLocale() }}/portal/bookings" class="text-stone-500 hover:text-ink transition-fast">
                            {{ __('portal.nav_bookings') }}
                        </a>
                        <a href="/{{ app()->getLocale() }}/portal/receipts" class="text-stone-500 hover:text-ink transition-fast">
                            {{ __('portal.nav_receipts') }}
                        </a>
                        <a href="/{{ app()->getLocale() }}/portal/preferences" class="text-stone-500 hover:text-ink transition-fast">
                            {{ __('portal.nav_preferences') }}
                        </a>
                        <a href="/{{ app()->getLocale() }}/book" class="rounded-md bg-brass-500 px-4 py-2 text-sm font-medium text-parchment hover:bg-brass-600 transition-fast">
                            {{ __('portal.nav_book_appointment') }}
                        </a>
                        <a href="/{{ app()->getLocale() === 'ar' ? 'fr' : 'ar' }}/portal/dashboard"
                           class="text-xs text-stone-400 hover:text-ink transition-fast uppercase tracking-wide">
                            {{ app()->getLocale() === 'ar' ? 'FR' : 'AR' }}
                        </a>
                        <form method="POST" action="/{{ app()->getLocale() }}/portal/logout">
                            @csrf
                            <button type="submit" class="text-sm font-medium text-stone-500 hover:text-ink transition-fast">
                                {{ __('auth.logout') }}
                            </button>
                        </form>
                    </nav>

                    <div class="md:hidden flex items-center gap-2">
                        <a href="/{{ app()->getLocale() === 'ar' ? 'fr' : 'ar' }}/portal/dashboard"
                           class="text-xs text-stone-400 hover:text-ink uppercase tracking-wide">
                            {{ app()->getLocale() === 'ar' ? 'FR' : 'AR' }}
                        </a>
                        <button type="button" id="mobile-menu-btn" class="text-stone-500 hover:text-ink p-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                    </div>
                @endauth
            </div>

            @auth('client')
                <div id="mobile-menu" class="hidden md:hidden border-t border-stone-200 px-4 py-3 space-y-3 text-sm">
                    <a href="/{{ app()->getLocale() }}/portal/bookings" class="block text-stone-600 hover:text-ink">
                        {{ __('portal.nav_bookings') }}
                    </a>
                    <a href="/{{ app()->getLocale() }}/portal/receipts" class="block text-stone-600 hover:text-ink">
                        {{ __('portal.nav_receipts') }}
                    </a>
                    <a href="/{{ app()->getLocale() }}/portal/preferences" class="block text-stone-600 hover:text-ink">
                        {{ __('portal.nav_preferences') }}
                    </a>
                    <a href="/{{ app()->getLocale() }}/book" class="block rounded-md bg-brass-500 px-4 py-2 text-center font-medium text-parchment hover:bg-brass-600 transition-fast">
                        {{ __('portal.nav_book_appointment') }}
                    </a>
                    <form method="POST" action="/{{ app()->getLocale() }}/portal/logout">
                        @csrf
                        <button type="submit" class="block w-full text-left text-stone-600 hover:text-ink">
                            {{ __('auth.logout') }}
                        </button>
                    </form>
                </div>
            @endauth
        </header>

        @if (session('success'))
            <div class="mx-auto max-w-4xl px-4 pt-6 md:px-6 lg:px-8">
                <div class="rounded-md bg-success/10 border border-success/30 p-4 text-sm text-success">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if (session('deleted'))
            <div class="mx-auto max-w-4xl px-4 pt-6 md:px-6 lg:px-8">
                <div class="rounded-md bg-info/10 border border-info/30 p-4 text-sm text-info">
                    {{ __('portal.account_deleted_message') }}
                </div>
            </div>
        @endif

        <main>
            @yield('content')
        </main>

        @auth('client')
            <script>
                document.getElementById('mobile-menu-btn')?.addEventListener('click', function () {
                    document.getElementById('mobile-menu')?.toggleClass('hidden');
                });
            </script>
        @endauth

        <livewire:chatbot />
    </body>
</html>
