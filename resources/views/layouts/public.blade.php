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
    <style>[x-cloak] { display: none !important; }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-parchment text-stone-700 antialiased overflow-x-hidden">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:start-4 focus:z-50 focus:rounded-lg focus:bg-brass-500 focus:px-4 focus:py-2 focus:text-parchment focus:shadow-lg">
        {{ __('common.skip_to_content') }}
    </a>

    <div x-data="{ mobileMenuOpen: false }" class="flex min-h-screen flex-col">

        {{-- ============================================================
             HEADER
        ============================================================= --}}
        <header
            x-data="{ scrolled: false }"
            x-on:scroll.window="scrolled = window.scrollY > 60"
            :class="scrolled ? 'py-2 shadow-sm' : 'py-4'"
            class="sticky top-0 z-40 border-b border-stone-200/60 bg-parchment/90 backdrop-blur-xl backdrop-saturate-150 transition-all duration-300"
        >
            {{-- Thin brass top accent (visible when scrolled) --}}
            <div
                x-show="scrolled"
                x-cloak
                x-transition:enter="transition duration-500"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                class="absolute top-0 inset-x-0 h-[2px] bg-gradient-to-r from-transparent via-brass-400 to-transparent"
            ></div>

            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 md:px-6 lg:px-8">
                {{-- Logo --}}
                <a href="/{{ app()->getLocale() }}" class="group flex flex-col">
                    <span class="text-lg md:text-xl font-semibold text-ink leading-tight tracking-tight transition-colors duration-200 group-hover:text-brass-600">
                        Sana Bouhamidi
                    </span>
                    <span class="text-[0.6rem] font-semibold uppercase tracking-[0.18em] text-brass-500 leading-none mt-0.5">
                        Notaire Adoul · Agadir
                    </span>
                </a>

                {{-- Desktop nav --}}
                <nav class="hidden lg:flex items-center gap-1 text-sm font-medium">
                    @php $currentPath = request()->path(); @endphp

                    <a href="/{{ app()->getLocale() }}/maitre-bouhamidi"
                       @class(['nav-link px-3 py-2 rounded-md transition-fast', 'text-ink' => str_contains($currentPath, 'maitre-bouhamidi'), 'text-stone-500 hover:text-ink' => !str_contains($currentPath, 'maitre-bouhamidi')])
                       @if(str_contains($currentPath, 'maitre-bouhamidi')) aria-current="page" @endif>
                        {{ __('nav.about') }}
                    </a>

                    <div x-data="{ open: false }" class="relative" @mouseenter="open = true" @mouseleave="open = false">
                        <button @click.prevent="open = !open"
                                class="nav-link flex items-center gap-1 px-3 py-2 rounded-md text-stone-500 hover:text-ink transition-fast"
                                :class="open && 'text-ink'"
                                aria-haspopup="true" :aria-expanded="open">
                            {{ __('nav.services') }}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transition-transform duration-200" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" x-cloak @click.outside="open = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                             class="absolute start-0 top-full mt-2 w-60 rounded-xl border border-stone-200/70 bg-white/95 backdrop-blur-sm p-1.5 shadow-xl shadow-ink/8">
                            @php
                                $serviceNavItems = [
                                    ['slug' => 'actes-familiaux', 'key' => 'nav.services_family'],
                                    ['slug' => 'immobilier', 'key' => 'nav.services_realestate'],
                                    ['slug' => 'entreprise', 'key' => 'nav.services_business'],
                                    ['slug' => 'contentieux', 'key' => 'nav.services_contracts'],
                                ];
                            @endphp
                            @foreach ($serviceNavItems as $item)
                                <a href="/{{ app()->getLocale() }}/services/{{ $item['slug'] }}"
                                   @class(['flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm transition-fast', 'text-brass-700 bg-brass-50 font-medium' => str_contains($currentPath, 'services/'.$item['slug']), 'text-stone-700 hover:bg-stone-50 hover:text-ink' => !str_contains($currentPath, 'services/'.$item['slug'])])
                                   @if(str_contains($currentPath, 'services/'.$item['slug'])) aria-current="page" @endif>
                                    <span class="size-1.5 rounded-full bg-brass-400 shrink-0"></span>
                                    {{ __($item['key']) }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <a href="/{{ app()->getLocale() }}/consultation"
                       @class(['nav-link px-3 py-2 rounded-md transition-fast', 'text-ink' => str_contains($currentPath, 'consultation'), 'text-stone-500 hover:text-ink' => !str_contains($currentPath, 'consultation')])
                       @if(str_contains($currentPath, 'consultation')) aria-current="page" @endif>
                        {{ __('nav.consultation') }}
                    </a>
                    <a href="/{{ app()->getLocale() }}/faq"
                       @class(['nav-link px-3 py-2 rounded-md transition-fast', 'text-ink' => str_contains($currentPath, 'faq'), 'text-stone-500 hover:text-ink' => !str_contains($currentPath, 'faq')])
                       @if(str_contains($currentPath, 'faq')) aria-current="page" @endif>
                        {{ __('nav.faq') }}
                    </a>
                    <a href="/{{ app()->getLocale() }}/cabinet"
                       @class(['nav-link px-3 py-2 rounded-md transition-fast', 'text-ink' => str_contains($currentPath, 'cabinet'), 'text-stone-500 hover:text-ink' => !str_contains($currentPath, 'cabinet')])
                       @if(str_contains($currentPath, 'cabinet')) aria-current="page" @endif>
                        {{ __('nav.cabinet') }}
                    </a>
                    <a href="/{{ app()->getLocale() }}/contact"
                       @class(['nav-link px-3 py-2 rounded-md transition-fast', 'text-ink' => str_contains($currentPath, 'contact'), 'text-stone-500 hover:text-ink' => !str_contains($currentPath, 'contact')])
                       @if(str_contains($currentPath, 'contact')) aria-current="page" @endif>
                        {{ __('nav.contact') }}
                    </a>
                </nav>

                {{-- Actions --}}
                <div class="flex items-center gap-2">
                    <x-locale-switcher />
                    <a href="/{{ app()->getLocale() }}/book"
                       class="hidden lg:inline-flex btn-brass text-sm px-5 py-2.5 rounded-lg scale-on-press">
                        {{ __('nav.cta') }}
                    </a>
                    <button x-on:click="mobileMenuOpen = true"
                            class="lg:hidden flex items-center justify-center size-9 rounded-lg text-ink hover:bg-stone-100 transition-fast"
                            aria-label="Menu">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </header>

        {{-- ============================================================
             MOBILE MENU
        ============================================================= --}}
        <div x-show="mobileMenuOpen" x-cloak class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true">
            {{-- Backdrop --}}
            <div x-on:click="mobileMenuOpen = false"
                 x-transition:enter="transition duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-ink/50 backdrop-blur-sm"></div>

            {{-- Panel --}}
            <div x-on:click.outside="mobileMenuOpen = false"
                 x-transition:enter="transition ease-out duration-250"
                 x-transition:enter-start="opacity-0 translate-x-full"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-180"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 translate-x-full"
                 class="fixed inset-y-0 end-0 w-full max-w-xs bg-parchment shadow-2xl overflow-y-auto flex flex-col">

                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-5 border-b border-stone-200">
                    <div class="flex flex-col">
                        <span class="font-semibold text-ink">Sana Bouhamidi</span>
                        <span class="text-[0.6rem] font-semibold uppercase tracking-widest text-brass-500 mt-0.5">Notaire Adoul</span>
                    </div>
                    <button x-on:click="mobileMenuOpen = false"
                            class="flex items-center justify-center size-9 rounded-lg text-stone-500 hover:bg-stone-100 transition-fast"
                            aria-label="{{ __('common.close') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Nav links --}}
                <nav class="flex-1 px-4 py-6 space-y-1">
                    @php $currentPath = request()->path(); @endphp
                    <a href="/{{ app()->getLocale() }}/maitre-bouhamidi"
                       @class(['flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-fast', 'bg-brass-50 text-brass-700' => str_contains($currentPath, 'maitre-bouhamidi'), 'text-stone-700 hover:bg-stone-100 hover:text-ink' => !str_contains($currentPath, 'maitre-bouhamidi')])
                       x-on:click="mobileMenuOpen = false"
                       @if(str_contains($currentPath, 'maitre-bouhamidi')) aria-current="page" @endif>
                        {{ __('nav.about') }}
                    </a>

                    <div class="pt-2 pb-1">
                        <p class="px-3 text-[0.65rem] font-bold uppercase tracking-widest text-stone-400 mb-1">{{ __('nav.services') }}</p>
                        @foreach ($serviceNavItems as $item)
                            <a href="/{{ app()->getLocale() }}/services/{{ $item['slug'] }}"
                               @class(['flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-fast', 'bg-brass-50 text-brass-700' => str_contains($currentPath, 'services/'.$item['slug']), 'text-stone-600 hover:bg-stone-100 hover:text-ink' => !str_contains($currentPath, 'services/'.$item['slug'])])
                               x-on:click="mobileMenuOpen = false"
                               @if(str_contains($currentPath, 'services/'.$item['slug'])) aria-current="page" @endif>
                                <span class="size-1.5 rounded-full bg-brass-400 shrink-0 ms-1"></span>
                                {{ __($item['key']) }}
                            </a>
                        @endforeach
                    </div>

                    @foreach ([
                        ['path' => 'consultation', 'key' => 'nav.consultation'],
                        ['path' => 'faq',          'key' => 'nav.faq'],
                        ['path' => 'cabinet',      'key' => 'nav.cabinet'],
                        ['path' => 'contact',      'key' => 'nav.contact'],
                    ] as $link)
                        <a href="/{{ app()->getLocale() }}/{{ $link['path'] }}"
                           @class(['flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-fast', 'bg-brass-50 text-brass-700' => str_contains($currentPath, $link['path']), 'text-stone-700 hover:bg-stone-100 hover:text-ink' => !str_contains($currentPath, $link['path'])])
                           x-on:click="mobileMenuOpen = false"
                           @if(str_contains($currentPath, $link['path'])) aria-current="page" @endif>
                            {{ __($link['key']) }}
                        </a>
                    @endforeach
                </nav>

                {{-- Bottom actions --}}
                <div class="px-4 pb-8 pt-4 border-t border-stone-200 space-y-3">
                    <x-locale-switcher />
                    <a href="/{{ app()->getLocale() }}/book"
                       class="btn-brass w-full justify-center py-3 text-sm rounded-lg scale-on-press"
                       x-on:click="mobileMenuOpen = false">
                        {{ __('nav.cta') }}
                    </a>
                </div>
            </div>
        </div>

        {{-- ============================================================
             MAIN CONTENT
        ============================================================= --}}
        <main id="main-content" class="flex-1 page-enter">
            @yield('content')
            {!! $slot ?? '' !!}
        </main>

        {{-- ============================================================
             FOOTER
        ============================================================= --}}
        <footer class="relative overflow-hidden bg-ink text-parchment">
            {{-- Top decorative accent --}}
            <div class="h-px w-full bg-gradient-to-r from-transparent via-brass-500 to-transparent"></div>
            <div class="h-px w-full bg-gradient-to-r from-transparent via-brass-600/40 to-transparent mt-px"></div>

            {{-- Background glow --}}
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute top-0 start-1/2 -translate-x-1/2 w-[800px] h-[400px] rounded-full bg-brass-500/5 blur-3xl"></div>
                <div class="absolute bottom-0 start-0 w-[300px] h-[300px] rounded-full bg-brass-600/5 blur-3xl"></div>
            </div>

            <div class="relative mx-auto max-w-7xl px-4 pt-16 pb-10 md:px-6 lg:px-8">
                <div class="grid gap-12 md:grid-cols-2 lg:grid-cols-4">

                    {{-- Brand --}}
                    <div class="lg:col-span-1">
                        <div class="mb-5">
                            <h4 class="text-xl font-semibold text-parchment leading-tight">Sana Bouhamidi</h4>
                            <p class="text-[0.65rem] font-bold uppercase tracking-[0.18em] text-brass-400 mt-1">Notaire Adoul · Agadir</p>
                        </div>
                        <div class="h-px w-10 bg-brass-500 rounded-full mb-5"></div>
                        <p class="text-sm text-stone-400 leading-relaxed">
                            {{ __('footer.notaire') }}<br>
                            {{ __('footer.address') }}
                        </p>
                        <a href="https://wa.me/212666120661" target="_blank" rel="noopener noreferrer"
                           class="mt-5 inline-flex items-center gap-2 rounded-xl border border-brass-500/25 bg-brass-500/10 px-4 py-2.5 text-sm font-medium text-brass-300 transition-all duration-200 hover:bg-brass-500/20 hover:border-brass-400/40 hover:text-brass-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/>
                            </svg>
                            {{ __('common.whatsapp') }}
                        </a>
                    </div>

                    {{-- Services --}}
                    <div>
                        <h5 class="mb-5 text-[0.65rem] font-bold uppercase tracking-[0.18em] text-brass-400">{{ __('nav.services') }}</h5>
                        <ul class="space-y-3">
                            @foreach ([
                                ['href' => '/book',         'key' => 'nav.cta'],
                                ['href' => '/services',     'key' => 'nav.services'],
                                ['href' => '/consultation', 'key' => 'nav.consultation'],
                                ['href' => '/faq',          'key' => 'nav.faq'],
                                ['href' => '/cabinet',      'key' => 'nav.cabinet'],
                            ] as $link)
                                <li>
                                    <a href="/{{ app()->getLocale() }}{{ $link['href'] }}"
                                       class="group inline-flex items-center gap-2.5 text-sm text-stone-400 transition-all duration-200 hover:text-parchment hover:ps-0.5">
                                        <span class="size-1 rounded-full bg-brass-500 shrink-0 group-hover:bg-brass-400 transition-colors"></span>
                                        {{ __($link['key']) }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Legal --}}
                    <div>
                        <h5 class="mb-5 text-[0.65rem] font-bold uppercase tracking-[0.18em] text-brass-400">{{ __('footer.legal') }}</h5>
                        <ul class="space-y-3">
                            @foreach ([
                                ['href' => '/mentions-legales',        'key' => 'footer.legal_notices'],
                                ['href' => '/politique-confidentialite','key' => 'footer.privacy'],
                                ['href' => '/conditions-utilisation',  'key' => 'footer.terms'],
                            ] as $link)
                                <li>
                                    <a href="/{{ app()->getLocale() }}{{ $link['href'] }}"
                                       class="group inline-flex items-center gap-2.5 text-sm text-stone-400 transition-all duration-200 hover:text-parchment hover:ps-0.5">
                                        <span class="size-1 rounded-full bg-brass-500 shrink-0 group-hover:bg-brass-400 transition-colors"></span>
                                        {{ __($link['key']) }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Contact --}}
                    <div>
                        <h5 class="mb-5 text-[0.65rem] font-bold uppercase tracking-[0.18em] text-brass-400">{{ __('footer.contact') }}</h5>
                        <div class="space-y-3.5">
                            <a href="tel:+212528380719" class="group flex items-center gap-3 text-sm text-stone-400 transition-all hover:text-parchment">
                                <span class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-stone-800 text-brass-400 group-hover:bg-stone-700 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
                                    </svg>
                                </span>
                                <bdi dir="ltr">05 28 38 07 19</bdi>
                            </a>
                            <a href="tel:+212666120661" class="group flex items-center gap-3 text-sm text-stone-400 transition-all hover:text-parchment">
                                <span class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-stone-800 text-brass-400 group-hover:bg-stone-700 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/>
                                    </svg>
                                </span>
                                <bdi dir="ltr">06 66 12 06 61</bdi>
                            </a>
                            <a href="mailto:sana.bouhamidi@gmail.com" class="group flex items-center gap-3 text-sm text-stone-400 transition-all hover:text-parchment">
                                <span class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-stone-800 text-brass-400 group-hover:bg-stone-700 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                                    </svg>
                                </span>
                                <bdi dir="ltr" class="text-xs">sana.bouhamidi@gmail.com</bdi>
                            </a>
                        </div>
                        <div class="mt-5 pt-5 border-t border-stone-800">
                            <p class="text-xs text-stone-500 leading-relaxed">{{ __('footer.hours') }}</p>
                        </div>
                        <a href="/{{ app()->getLocale() }}/contact"
                           class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brass-500 px-4 py-2.5 text-sm font-semibold text-parchment transition-all hover:bg-brass-600">
                            {{ __('common.contact_us') }}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 rtl:scale-x-[-1]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Copyright bar --}}
                <div class="mt-14 flex flex-col items-center gap-3 border-t border-stone-800 pt-6 sm:flex-row sm:justify-between">
                    <p class="text-xs text-stone-600">
                        &copy; {{ date('Y') }} Sana Bouhamidi. {{ __('footer.rights') }}
                    </p>
                    <button x-on:click="window.scrollTo({ top: 0, behavior: 'smooth' })"
                            class="group inline-flex items-center gap-2 text-xs text-stone-600 transition-all hover:text-brass-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transition-transform group-hover:-translate-y-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/>
                        </svg>
                        {{ __('common.back_to_top') }}
                    </button>
                </div>
            </div>
        </footer>
    </div>

    @include('components.chatbot-placeholder')
    @include('components.cookie-banner')

    @if(config('services.plausible.domain'))
        <script defer nonce="{{ csp_nonce() }}" data-domain="{{ config('services.plausible.domain') }}" src="https://{{ config('services.plausible.server', 'plausible.io') }}/js/script.js"></script>
    @endif

    <script nonce="{{ csp_nonce() }}">
        document.addEventListener('alpine:init', () => {
            Alpine.data('faqItem', () => ({
                open: false,
                toggle() { this.open = !this.open; }
            }));
        });
    </script>
    @livewireScripts
    @stack('scripts')
</body>
</html>
