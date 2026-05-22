<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sana Bouhamidi — Notaire Adoul à Agadir')</title>
    <meta name="description" content="@yield('meta_description', __('common.tagline'))">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="alternate" hreflang="ar" href="{{ url(preg_replace('/^\/(fr|ar)/', '/ar', request()->getPathInfo())) }}">
    <link rel="alternate" hreflang="fr" href="{{ url(preg_replace('/^\/(fr|ar)/', '/fr', request()->getPathInfo())) }}">
    <link rel="alternate" hreflang="x-default" href="{{ url(preg_replace('/^\/(fr|ar)/', '/ar', request()->getPathInfo())) }}">
    <meta property="og:title" content="@yield('title', 'Sana Bouhamidi — Notaire Adoul à Agadir')">
    <meta property="og:description" content="@yield('meta_description', __('common.tagline'))">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="{{ app()->getLocale() === 'ar' ? 'ar_MA' : 'fr_FR' }}">
    <meta property="og:type" content="website">
    <meta property="og:image" content="{{ url('/images/og-default.jpg') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    @if (app()->getLocale() === 'ar')
        <meta property="og:locale:alternate" content="fr_FR">
    @else
        <meta property="og:locale:alternate" content="ar_MA">
    @endif
    @hasSection('structured_data')
        @yield('structured_data')
    @endif
    <link rel="preload" href="/resources/fonts/fraunces-latin.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/resources/fonts/inter-latin.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/resources/fonts/reemkufi-arabic.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/resources/fonts/ibmplexsansarabic-arabic.woff2" as="font" type="font/woff2" crossorigin>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-parchment text-stone-700 font-body-fr antialiased overflow-x-hidden">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:start-4 focus:z-50 focus:rounded-md focus:bg-brass-500 focus:px-4 focus:py-2 focus:text-parchment focus:shadow-md">
        {{ __('common.skip_to_content') }}
    </a>

    <div x-data="{ mobileMenuOpen: false }" class="flex min-h-screen flex-col">
        <header x-data="{ scrolled: false }" x-on:scroll.window="scrolled = window.scrollY > 50" :class="scrolled ? 'shadow-md py-2' : 'py-4'" class="sticky top-0 z-40 border-b border-stone-200 bg-parchment/95 backdrop-blur-sm transition-all duration-200">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 md:px-6 lg:px-8">
                <a href="/{{ app()->getLocale() }}" class="text-xl md:text-headline-md font-semibold text-ink">
                    Sana Bouhamidi
                </a>
                <nav class="hidden lg:flex items-center gap-8 text-sm font-medium">
                    <a href="/{{ app()->getLocale() }}/maitre-bouhamidi" class="text-stone-500 hover:text-ink transition-fast">{{ __('nav.about') }}</a>
                    <div x-data="{ open: false }" class="relative" @mouseenter="open = true" @mouseleave="open = false">
                        <button class="flex items-center gap-1 text-stone-500 hover:text-ink transition-fast">
                            {{ __('nav.services') }}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-cloak @click.outside="open = false" class="absolute start-0 mt-2 w-56 rounded-lg border border-stone-200 bg-white p-2 shadow-lg">
                            @php
                                $serviceNavItems = [
                                    ['slug' => 'actes-familiaux', 'key' => 'nav.services_family'],
                                    ['slug' => 'immobilier', 'key' => 'nav.services_realestate'],
                                    ['slug' => 'entreprise', 'key' => 'nav.services_business'],
                                    ['slug' => 'contentieux', 'key' => 'nav.services_contracts'],
                                ];
                            @endphp
                            @foreach ($serviceNavItems as $item)
                                <a href="/{{ app()->getLocale() }}/services/{{ $item['slug'] }}" class="block rounded-md px-3 py-2 text-sm text-stone-700 hover:bg-stone-100 hover:text-ink transition-fast">
                                    {{ __($item['key']) }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                    <a href="/{{ app()->getLocale() }}/consultation" class="text-stone-500 hover:text-ink transition-fast">{{ __('nav.consultation') }}</a>
                    <a href="/{{ app()->getLocale() }}/faq" class="text-stone-500 hover:text-ink transition-fast">{{ __('nav.faq') }}</a>
                    <a href="/{{ app()->getLocale() }}/cabinet" class="text-stone-500 hover:text-ink transition-fast">{{ __('nav.cabinet') }}</a>
                    <a href="/{{ app()->getLocale() }}/contact" class="text-stone-500 hover:text-ink transition-fast">{{ __('nav.contact') }}</a>
                </nav>
                <div class="flex items-center gap-3">
                    <x-locale-switcher />
                    <a href="/{{ app()->getLocale() }}/book" class="hidden lg:inline-flex items-center justify-center rounded-md bg-brass-500 px-5 py-2.5 text-sm font-medium text-parchment hover:bg-brass-600 transition-fast">
                        {{ __('nav.cta') }}
                    </a>
                    <button x-on:click="mobileMenuOpen = true" class="lg:hidden flex items-center justify-center size-10 text-ink" aria-label="Menu">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>
        </header>

        <div x-show="mobileMenuOpen" x-cloak class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true">
            <div x-on:click="mobileMenuOpen = false" class="fixed inset-0 bg-ink/60 backdrop-blur-sm"></div>
            <div x-on:click.outside="mobileMenuOpen = false" class="fixed inset-y-0 end-0 w-full max-w-sm bg-parchment p-6 shadow-lg overflow-y-auto">
                <div class="flex items-center justify-between mb-8">
                    <span class="text-lg font-semibold text-ink">Sana Bouhamidi</span>
                    <button x-on:click="mobileMenuOpen = false" class="flex items-center justify-center size-10 text-ink" aria-label="{{ __('common.close') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <nav class="flex flex-col gap-4 text-base font-medium">
                    <a href="/{{ app()->getLocale() }}/maitre-bouhamidi" class="py-2 text-stone-700 hover:text-ink transition-fast" x-on:click="mobileMenuOpen = false">{{ __('nav.about') }}</a>
                    <div class="py-2">
                        <span class="block text-stone-500 font-semibold mb-2">{{ __('nav.services') }}</span>
                        <div class="flex flex-col gap-2 ps-4">
                            @foreach ($serviceNavItems as $item)
                                <a href="/{{ app()->getLocale() }}/services/{{ $item['slug'] }}" class="text-stone-600 hover:text-ink transition-fast" x-on:click="mobileMenuOpen = false">{{ __($item['key']) }}</a>
                            @endforeach
                        </div>
                    </div>
                    <a href="/{{ app()->getLocale() }}/consultation" class="py-2 text-stone-700 hover:text-ink transition-fast" x-on:click="mobileMenuOpen = false">{{ __('nav.consultation') }}</a>
                    <a href="/{{ app()->getLocale() }}/faq" class="py-2 text-stone-700 hover:text-ink transition-fast" x-on:click="mobileMenuOpen = false">{{ __('nav.faq') }}</a>
                    <a href="/{{ app()->getLocale() }}/cabinet" class="py-2 text-stone-700 hover:text-ink transition-fast" x-on:click="mobileMenuOpen = false">{{ __('nav.cabinet') }}</a>
                    <a href="/{{ app()->getLocale() }}/contact" class="py-2 text-stone-700 hover:text-ink transition-fast" x-on:click="mobileMenuOpen = false">{{ __('nav.contact') }}</a>
                    <hr class="my-2 border-stone-200">
                    <x-locale-switcher />
                    <a href="/{{ app()->getLocale() }}/book" class="mt-4 inline-flex items-center justify-center rounded-md bg-brass-500 px-5 py-3 text-sm font-medium text-parchment hover:bg-brass-600 transition-fast" x-on:click="mobileMenuOpen = false">
                        {{ __('nav.cta') }}
                    </a>
                </nav>
            </div>
        </div>

        <main id="main-content" class="flex-1">
            @yield('content')
        </main>

        <footer class="border-t border-stone-200 bg-ink text-parchment">
            <div class="mx-auto max-w-7xl px-4 py-12 md:px-6 lg:px-8">
                <div class="grid gap-8 md:grid-cols-4">
                    <div class="md:col-span-2">
                        <h4 class="mb-3 text-lg font-semibold text-parchment">Sana Bouhamidi</h4>
                        <p class="text-sm text-stone-300 leading-relaxed max-w-sm">
                            {{ __('footer.notaire') }}<br>
                            {{ __('footer.address') }}
                        </p>
                        <div class="mt-4 flex flex-col gap-1 text-sm text-stone-300">
                            <a href="tel:+212528380719" class="hover:text-parchment transition-fast inline-flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-brass-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                                <bdi dir="ltr">05 28 38 07 19</bdi>
                            </a>
                            <a href="tel:+212666120661" class="hover:text-parchment transition-fast inline-flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-brass-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/></svg>
                                <bdi dir="ltr">06 66 12 06 61</bdi>
                            </a>
                            <a href="mailto:sana.bouhamidi@gmail.com" class="hover:text-parchment transition-fast inline-flex items-center gap-2 mt-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-brass-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                                <bdi dir="ltr">sana.bouhamidi@gmail.com</bdi>
                            </a>
                        </div>
                    </div>
                    <div>
                        <h4 class="mb-3 text-sm font-semibold uppercase tracking-wider text-brass-400">
                            {{ __('footer.legal') }}
                        </h4>
                        <ul class="space-y-2 text-sm text-stone-300">
                            <li><a href="/{{ app()->getLocale() }}/mentions-legales" class="hover:text-parchment transition-fast">{{ __('footer.legal_notices') }}</a></li>
                            <li><a href="/{{ app()->getLocale() }}/politique-confidentialite" class="hover:text-parchment transition-fast">{{ __('footer.privacy') }}</a></li>
                            <li><a href="/{{ app()->getLocale() }}/conditions-utilisation" class="hover:text-parchment transition-fast">{{ __('footer.terms') }}</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="mb-3 text-sm font-semibold uppercase tracking-wider text-brass-400">
                            {{ __('footer.contact') }}
                        </h4>
                        <p class="text-sm text-stone-300">
                            {{ __('footer.hours') }}
                        </p>
                        <a href="/{{ app()->getLocale() }}/contact" class="mt-2 inline-block text-sm text-brass-400 hover:text-brass-300 transition-fast">
                            {{ __('common.contact_us') }}
                        </a>
                    </div>
                </div>
                <div class="mt-10 border-t border-stone-700 pt-6 text-center text-xs text-stone-500">
                    &copy; {{ date('Y') }} Sana Bouhamidi. {{ __('footer.rights') }}
                </div>
            </div>
        </footer>
    </div>

    @include('components.chatbot-placeholder')
    @include('components.cookie-banner')

    @if(config('services.plausible.domain'))
        <script defer data-domain="{{ config('services.plausible.domain') }}" src="https://{{ config('services.plausible.server', 'plausible.io') }}/js/script.js"></script>
    @endif

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('faqItem', () => ({
                open: false,
                toggle() { this.open = !this.open; }
            }));
        });
    </script>
    @stack('scripts')
</body>
</html>
