<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', config('app.name', 'Sana Bouhamidi - Notaire'))</title>
        <style>[x-cloak] { display: none !important; }</style>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="bg-stone-50 text-stone-700 antialiased">
        <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:start-4 focus:z-50 focus:rounded-lg focus:bg-brass-500 focus:px-4 focus:py-2 focus:text-parchment focus:shadow-lg">
            {{ __('common.skip_to_content') }}
        </a>

        <div x-data="{ mobileMenuOpen: false }" class="flex min-h-screen flex-col">

            {{-- Header --}}
            <header
                x-data="{ scrolled: false }"
                x-on:scroll.window="scrolled = window.scrollY > 40"
                :class="scrolled ? 'py-2 shadow-sm' : 'py-3'"
                class="sticky top-0 z-40 border-b border-stone-200/70 bg-white/90 backdrop-blur-xl backdrop-saturate-150 transition-all duration-300"
            >
                <div class="mx-auto flex max-w-6xl items-center justify-between px-4 md:px-6">
                    {{-- Logo --}}
                    <a href="/{{ app()->getLocale() }}/portal/dashboard" class="group flex flex-col">
                        <span class="text-base font-semibold text-ink leading-tight transition-colors group-hover:text-brass-600">
                            {{ config('app.name') }}
                        </span>
                        <span class="text-[0.58rem] font-bold uppercase tracking-[0.16em] text-brass-500 leading-none mt-0.5">
                            Espace Client
                        </span>
                    </a>

                    @auth('client')
                        {{-- Desktop nav --}}
                        <nav class="hidden md:flex items-center gap-1">
                            <a href="/{{ app()->getLocale() }}/portal/bookings"
                               @class(['portal-nav-link text-sm', 'active' => request()->routeIs('*bookings*')])>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                                </svg>
                                {{ __('portal.nav_bookings') }}
                            </a>
                            <a href="/{{ app()->getLocale() }}/portal/receipts"
                               @class(['portal-nav-link text-sm', 'active' => request()->routeIs('*receipts*')])>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>
                                </svg>
                                {{ __('portal.nav_receipts') }}
                            </a>
                            <a href="/{{ app()->getLocale() }}/portal/preferences"
                               @class(['portal-nav-link text-sm', 'active' => request()->routeIs('*preferences*')])>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ __('portal.nav_preferences') }}
                            </a>
                        </nav>

                        {{-- Desktop actions --}}
                        <div class="hidden md:flex items-center gap-2">
                            <a href="/{{ app()->getLocale() }}/book"
                               class="btn-brass text-xs px-4 py-2 rounded-lg scale-on-press">
                                {{ __('portal.nav_book_appointment') }}
                            </a>
                            <a href="/{{ app()->getLocale() === 'ar' ? 'fr' : 'ar' }}/portal/dashboard"
                               class="portal-nav-link text-xs uppercase tracking-widest font-bold">
                                {{ app()->getLocale() === 'ar' ? 'FR' : 'AR' }}
                            </a>
                            <form method="POST" action="/{{ app()->getLocale() }}/portal/logout">
                                @csrf
                                <button type="submit" class="portal-nav-link text-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
                                    </svg>
                                    {{ __('auth.logout') }}
                                </button>
                            </form>
                        </div>

                        {{-- Mobile trigger --}}
                        <div class="md:hidden flex items-center gap-2">
                            <a href="/{{ app()->getLocale() === 'ar' ? 'fr' : 'ar' }}/portal/dashboard"
                               class="text-[0.65rem] font-bold uppercase tracking-widest text-stone-500 hover:text-ink transition-fast">
                                {{ app()->getLocale() === 'ar' ? 'FR' : 'AR' }}
                            </a>
                            <button x-on:click="mobileMenuOpen = true"
                                    class="flex items-center justify-center size-9 rounded-lg text-stone-600 hover:bg-stone-100 transition-fast"
                                    aria-label="Menu">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                            </button>
                        </div>
                    @endauth
                </div>
            </header>

            {{-- Mobile menu --}}
            @auth('client')
                <div x-show="mobileMenuOpen" x-cloak class="fixed inset-0 z-50 md:hidden" role="dialog" aria-modal="true">
                    <div x-on:click="mobileMenuOpen = false"
                         x-transition:enter="transition duration-200"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         class="fixed inset-0 bg-ink/50 backdrop-blur-sm"></div>
                    <div x-on:click.outside="mobileMenuOpen = false"
                         x-transition:enter="transition ease-out duration-250"
                         x-transition:enter-start="opacity-0 translate-x-full"
                         x-transition:enter-end="opacity-100 translate-x-0"
                         class="fixed inset-y-0 end-0 w-72 bg-white shadow-2xl overflow-y-auto flex flex-col">
                        <div class="flex items-center justify-between px-5 py-4 border-b border-stone-100">
                            <span class="font-semibold text-sm text-ink">{{ config('app.name') }}</span>
                            <button x-on:click="mobileMenuOpen = false"
                                    class="flex items-center justify-center size-8 rounded-lg text-stone-500 hover:bg-stone-100 transition-fast">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <nav class="flex-1 px-4 py-5 space-y-1">
                            <a href="/{{ app()->getLocale() }}/portal/bookings"
                               class="portal-nav-link text-sm w-full"
                               x-on:click="mobileMenuOpen = false">
                                {{ __('portal.nav_bookings') }}
                            </a>
                            <a href="/{{ app()->getLocale() }}/portal/receipts"
                               class="portal-nav-link text-sm w-full"
                               x-on:click="mobileMenuOpen = false">
                                {{ __('portal.nav_receipts') }}
                            </a>
                            <a href="/{{ app()->getLocale() }}/portal/preferences"
                               class="portal-nav-link text-sm w-full"
                               x-on:click="mobileMenuOpen = false">
                                {{ __('portal.nav_preferences') }}
                            </a>
                        </nav>
                        <div class="px-4 pb-6 pt-4 border-t border-stone-100 space-y-2">
                            <a href="/{{ app()->getLocale() }}/book"
                               class="btn-brass w-full justify-center py-2.5 text-sm rounded-lg scale-on-press"
                               x-on:click="mobileMenuOpen = false">
                                {{ __('portal.nav_book_appointment') }}
                            </a>
                            <form method="POST" action="/{{ app()->getLocale() }}/portal/logout" x-on:click="mobileMenuOpen = false">
                                @csrf
                                <button type="submit"
                                        class="w-full rounded-lg border border-stone-200 px-4 py-2.5 text-sm font-medium text-stone-600 hover:bg-stone-50 transition-fast">
                                    {{ __('auth.logout') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endauth

            {{-- Flash messages --}}
            @if (session('success') || session('deleted'))
                <div class="mx-auto w-full max-w-5xl px-4 pt-5 md:px-6">
                    @if (session('success'))
                        <div class="alert alert-success">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif
                    @if (session('deleted'))
                        <div class="alert alert-info">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ __('portal.account_deleted_message') }}</span>
                        </div>
                    @endif
                </div>
            @endif

            <main id="main-content" class="flex-1">
                @yield('content')
            </main>

            <footer class="border-t border-stone-200 bg-white py-5">
                <div class="mx-auto max-w-6xl px-4 md:px-6">
                    <p class="text-center text-xs text-stone-400">
                        &copy; {{ date('Y') }} Sana Bouhamidi. {{ __('footer.rights') }}
                    </p>
                </div>
            </footer>
        </div>

        <livewire:chatbot />
        @livewireScripts
    </body>
</html>
