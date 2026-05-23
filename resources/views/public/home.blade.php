@extends('layouts.public')

@section('title', 'Sana Bouhamidi — Notaire Adoul à Agadir')
@section('meta_description', __('common.tagline'))

@php $practiceInfo = \App\Models\Setting::practiceInfo(); @endphp

@section('structured_data')
<script type="application/ld+json" nonce="{{ csp_nonce() }}">
{
  "@@context": "https://schema.org",
  "@@type": ["LegalService", "LocalBusiness"],
  "@id": "{{ url('/') }}",
  "name": "Sana Bouhamidi — Notaire Adoul",
  "description": "{{ __('common.tagline') }}",
  "url": "{{ url('/' . app()->getLocale()) }}",
  "telephone": "{{ $practiceInfo['phone'] ?: '+212528380719' }}",
  "email": "{{ $practiceInfo['email'] ?: 'sana.bouhamidi@gmail.com' }}",
  "image": "{{ url('/images/sana-portrait.jpg') }}",
  "address": {
    "@@type": "PostalAddress",
    "streetAddress": "{{ $practiceInfo['address'] ?: 'Bensergao, près du Tribunal de Première Instance' }}",
    "addressLocality": "Agadir",
    "addressCountry": "MA"
  },
  "openingHoursSpecification": [
    { "@@type": "OpeningHoursSpecification", "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday"], "opens": "09:00", "closes": "17:00" }
  ],
  "sameAs": ["https://wa.me/{{ $practiceInfo['whatsapp'] ?: '212666120661' }}"]
}
</script>
@endsection

@section('content')

{{-- ============================================================
     HERO
============================================================= --}}
@php $isFr = app()->getLocale() === 'fr'; @endphp

<section class="relative overflow-hidden {{ $isFr ? 'min-h-[85vh]' : 'min-h-[92vh]' }} flex items-center px-4 pt-8 pb-20">
    {{-- Animated background blobs --}}
    <div class="pointer-events-none absolute inset-0 overflow-hidden">
        <div class="absolute -top-20 end-0 w-[700px] h-[700px] rounded-full bg-brass-100/55 blur-3xl float-slow"></div>
        <div class="absolute bottom-0 start-0 w-[450px] h-[450px] rounded-full bg-brass-200/35 blur-3xl float-delayed"></div>
        <div class="absolute top-1/3 start-1/3 w-[280px] h-[280px] rounded-full bg-stone-100/60 blur-2xl breathe"></div>
        {{-- Subtle grid --}}
        <div class="absolute inset-0 opacity-[0.018]"
             style="background-image: linear-gradient(#B68A3E 1px, transparent 1px), linear-gradient(90deg, #B68A3E 1px, transparent 1px); background-size: 48px 48px;"></div>
    </div>

    <div class="max-w-7xl mx-auto w-full relative">
        <div class="grid lg:grid-cols-5 gap-12 lg:gap-16 items-center">

            {{-- Content 3/5 --}}
            <div class="lg:col-span-3 flex flex-col {{ $isFr ? 'gap-6' : 'gap-8' }}">
                <div class="section-label reveal-left">{{ __('home.hero_label') }}</div>

                <h1 class="{{ $isFr ? 'text-3xl md:text-4xl lg:text-5xl' : 'text-4xl md:text-5xl lg:text-6xl' }} font-semibold text-ink {{ $isFr ? 'leading-[1.2]' : 'leading-[1.15]' }} tracking-tight text-balance reveal-up" style="animation-delay:80ms">
                    {{ __('home.hero_title') }}
                </h1>

                <p class="{{ $isFr ? 'text-lg' : 'text-xl' }} text-stone-500 max-w-md leading-relaxed reveal-up" style="animation-delay:160ms">
                    {{ __('home.hero_subtitle') }}
                </p>

                <div class="flex flex-wrap gap-3 reveal-up" style="animation-delay:240ms">
                    <a href="/{{ app()->getLocale() }}/book"
                       class="btn-brass h-13 px-9 text-[0.9375rem] rounded-xl scale-on-press">
                        {{ __('home.hero_cta_booking') }}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 rtl:scale-x-[-1]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                        </svg>
                    </a>
                    <a href="/{{ app()->getLocale() }}/maitre-bouhamidi"
                       class="btn-ink h-13 px-9 text-[0.9375rem] rounded-xl scale-on-press">
                        {{ __('home.hero_cta_chat') }}
                    </a>
                </div>

                {{-- Trust pills --}}
                <div class="flex flex-wrap gap-2 reveal-up" style="animation-delay:320ms">
                    @foreach ([
                        ['icon' => 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z', 'text' => 'home.trust_1_label'],
                        ['icon' => 'M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z', 'text' => 'home.trust_2_label'],
                        ['icon' => 'M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z', 'text' => 'home.trust_3_label'],
                        ['icon' => 'M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z', 'text' => 'home.trust_4_label'],
                    ] as $trust)
                        <span class="chip">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $trust['icon'] }}"/>
                            </svg>
                            {{ __($trust['text']) }}
                        </span>
                    @endforeach
                </div>

                {{-- Quick links --}}
                <div class="flex flex-wrap gap-x-5 gap-y-2 pt-2 border-t border-stone-200/70 reveal-up" style="animation-delay:400ms">
                    @foreach ([
                        ['href' => '#services', 'text' => 'nav.services'],
                        ['href' => '#process',  'text' => 'home.process_title'],
                        ['href' => '#about',    'text' => 'home.about_teaser_title'],
                        ['href' => '#faq',      'text' => 'nav.faq'],
                    ] as $link)
                        <a href="{{ $link['href'] }}"
                           class="text-[0.75rem] font-semibold uppercase tracking-wider text-stone-400 hover:text-brass-500 transition-fast">
                            {{ __($link['text']) }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Portrait 2/5 --}}
            <div class="lg:col-span-2 flex justify-center lg:justify-end reveal-scale" style="animation-delay:200ms">
                <div class="relative w-full max-w-[340px]">
                    {{-- Decorative rotating ring --}}
                    <div class="absolute -inset-4 rounded-2xl border border-brass-200/40 spin-slow opacity-60"></div>
                    {{-- Second ring --}}
                    <div class="absolute -inset-2 rounded-2xl border border-brass-300/30" style="transform: rotate(-3deg)"></div>

                    {{-- Portrait card --}}
                    <div class="relative rounded-2xl overflow-hidden aspect-[3/4] shadow-2xl shadow-ink/15">
                        <div class="absolute inset-0 bg-gradient-to-br from-brass-100 via-brass-200 to-brass-300"></div>
                        {{-- Pattern overlay --}}
                        <div class="absolute inset-0 opacity-[0.04]"
                             style="background-image: repeating-linear-gradient(45deg, #B68A3E 0, #B68A3E 1px, transparent 0, transparent 50%); background-size: 12px 12px;"></div>
                        {{-- Monogram --}}
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-[7rem] font-bold text-brass-600/30 select-none leading-none"
                                  style="font-family: var(--font-display-fr);">SB</span>
                        </div>
                        {{-- Bottom gradient overlay --}}
                        <div class="absolute bottom-0 inset-x-0 h-28 bg-gradient-to-t from-brass-700/20 to-transparent"></div>
                    </div>

                    {{-- Floating badge --}}
                    <div class="absolute -bottom-5 -start-5 glass-card rounded-2xl px-4 py-3 shadow-xl float" style="animation-delay: 1s">
                        <p class="text-[0.6rem] font-bold uppercase tracking-[0.18em] text-brass-600">Notaire Adoul</p>
                        <p class="text-lg font-bold text-ink leading-tight">Agadir, MA</p>
                    </div>

                    {{-- Star badge --}}
                    <div class="absolute -top-4 -end-4 size-14 rounded-full bg-brass-500 flex items-center justify-center shadow-lg glow-pulse hidden md:flex" aria-hidden="true">
                        <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
                        </svg>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ============================================================
     STATS STRIP
============================================================= --}}
<section class="relative overflow-hidden border-y border-stone-200/60 bg-white">
    <div class="pointer-events-none absolute inset-0 bg-gradient-to-r from-brass-50/50 via-transparent to-brass-50/50"></div>
    <div class="relative max-w-7xl mx-auto px-4 py-10 md:px-6 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-px bg-stone-100 rounded-2xl overflow-hidden shadow-sm">
            @php
                $trustItems = [
                    ['icon' => 'M4.26 10.147a60.438 60.438 0 0 0-.419 1.123M4.26 10.147c.017.075.036.15.057.224m0 0 .133.5M4.26 10.147l-1.02.204a1.125 1.125 0 0 0-.504 1.874l1.222 1.222c.215.215.34.508.34.814v.054c0 .364.184.703.498.881l.464.264a1.125 1.125 0 0 1 .69 1.013v.363a1.125 1.125 0 0 0 1.124 1.125h.963a1.125 1.125 0 0 1 .999.614l.344.688a1.124 1.124 0 0 0 1.254.57l.18-.045A1.125 1.125 0 0 0 12 19.311v-.54a1.125 1.125 0 0 1 .472-.899l5.007-2.78a1.5 1.5 0 0 0 .771-1.328v-5.132a1.5 1.5 0 0 0-.707-1.28l-5.636-3.593a1.125 1.125 0 0 0-1.214 0l-5.637 3.593a1.5 1.5 0 0 0-.56 1.2z', 'label' => 1],
                    ['icon' => 'M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z', 'label' => 2],
                    ['icon' => 'M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z', 'label' => 3],
                    ['icon' => 'M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z', 'label' => 4],
                ];
            @endphp
            @foreach ($trustItems as $item)
                <div class="flex items-center gap-4 bg-white px-6 py-6">
                    <div class="shrink-0 flex size-11 items-center justify-center rounded-xl bg-brass-50 border border-brass-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-brass-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                        </svg>
                    </div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-stone-700 leading-snug">
                        {{ __('home.trust_' . $item['label'] . '_label') }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================
     SERVICES
============================================================= --}}
@php
    $serviceIconPaths = [
        'heart'       => 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z',
        'home'        => 'M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5.5l-1.5-.5M6.75 7.364V3h-3v18m3-13.636l10.5-3.819',
        'briefcase'   => 'M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0',
        'scale'       => 'M3 3v1.5M3 21v-6m0 0l2.77-.693a9 9 0 016.208.682l.108.054a9 9 0 006.086.71l3.114-.732a48.524 48.524 0 01-.005-10.499l-3.11.732a9 9 0 01-6.085-.711l-.108-.054a9 9 0 00-6.208-.682L3 4.5M3 15V4.5',
        'file-text'   => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9Z',
        'handshake'   => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9Z',
    ];
@endphp

<section class="py-28 md:py-36 px-4" id="services">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col items-center text-center gap-5 mb-16">
            <div class="section-label">{{ __('nav.services') }}</div>
            <h2 class="text-4xl md:text-5xl font-semibold text-ink max-w-2xl leading-tight">
                {{ __('home.services_title') }}
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 lg:gap-6">
            @forelse ($services as $i => $service)
                <div class="card-premium p-7 md:p-9 relative overflow-hidden group"
                     style="animation-delay: {{ $i * 80 }}ms">
                    {{-- Gradient top border --}}
                    <div class="absolute top-0 start-0 end-0 h-[3px] bg-gradient-to-r from-brass-300 via-brass-500 to-brass-300 opacity-80 group-hover:opacity-100 transition-opacity duration-300"></div>

                    {{-- Background glow on hover --}}
                    <div class="absolute top-0 start-0 w-40 h-40 rounded-full bg-brass-50/80 blur-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 -translate-x-1/2 -translate-y-1/2"></div>

                    <div class="relative flex flex-col h-full gap-5">
                        {{-- Icon + Title --}}
                        <div class="flex items-start gap-4">
                            <div class="shrink-0 flex size-12 items-center justify-center rounded-xl bg-brass-50 border border-brass-100 group-hover:bg-brass-100 transition-colors duration-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-brass-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $serviceIconPaths[$service->icon] ?? $serviceIconPaths['file-text'] }}"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-ink pt-1.5">
                                {{ locale_string($service->title_translations, app()->getLocale()) }}
                            </h3>
                        </div>

                        <p class="text-[0.9375rem] text-stone-500 leading-relaxed line-clamp-3">
                            {{ locale_string($service->intro_translations, app()->getLocale()) }}
                        </p>

                        <a href="/{{ app()->getLocale() }}/services/{{ $service->slug }}"
                           class="mt-auto inline-flex items-center gap-1.5 text-sm font-semibold text-brass-600 hover:text-brass-700 transition-fast w-max group/link">
                            {{ __('home.services_link') }}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 rtl:scale-x-[-1] transition-transform group-hover/link:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            @empty
                <p class="text-stone-500 col-span-2 text-center py-12">{{ __('common.empty') }}</p>
            @endforelse
        </div>
    </div>
</section>

{{-- ============================================================
     PROCESS
============================================================= --}}
<section class="relative overflow-hidden py-28 md:py-36 px-4" id="process">
    {{-- Background --}}
    <div class="absolute inset-0 bg-ink"></div>
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-brass-500/40 to-transparent"></div>
        <div class="absolute bottom-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-brass-500/40 to-transparent"></div>
        <div class="absolute top-1/2 start-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full bg-brass-500/5 blur-3xl"></div>
    </div>

    <div class="relative max-w-7xl mx-auto">
        <div class="flex flex-col items-center text-center gap-5 mb-16">
            <div class="section-label" style="color: var(--color-brass-400);">
                <span class="before:bg-brass-700 after:bg-brass-700"></span>
                {{ __('home.process_title') }}
            </div>
            <h2 class="text-4xl md:text-5xl font-semibold text-parchment max-w-2xl leading-tight">
                {{ __('home.process_title') }}
            </h2>
        </div>

        <div class="relative grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-4">
            {{-- Connecting line (desktop) --}}
            <div class="hidden lg:block absolute top-7 start-[12.5%] end-[12.5%] h-px bg-gradient-to-r from-brass-700 via-brass-500 to-brass-700 opacity-40"></div>

            @for ($i = 1; $i <= 4; $i++)
                <div class="flex flex-col gap-5 items-center text-center group relative">
                    <div class="step-badge group-hover:shadow-brass-glow transition-all duration-300">
                        0{{ $i }}
                    </div>
                    <div>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-parchment mb-2">
                            {{ __('home.process_step' . $i) }}
                        </h3>
                        <p class="text-sm text-stone-400 leading-relaxed max-w-[200px] mx-auto">
                            {{ __('home.process_step' . $i . '_desc') }}
                        </p>
                    </div>
                </div>
            @endfor
        </div>
    </div>
</section>

{{-- ============================================================
     VALUES
============================================================= --}}
@php
    $valueIconPaths = [
        'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z',
        'M3 3v1.5M3 21v-6m0 0l2.77-.693a9 9 0 016.208.682l.108.054a9 9 0 006.086.71l3.114-.732a48.524 48.524 0 01-.005-10.499l-3.11.732a9 9 0 01-6.085-.711l-.108-.054a9 9 0 00-6.208-.682L3 4.5M3 15V4.5',
        'M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z',
        'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0Z',
    ];
@endphp

<section class="py-28 md:py-36 px-4">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col items-center text-center gap-5 mb-16">
            <div class="section-label">{{ __('home.values_title') }}</div>
            <h2 class="text-4xl md:text-5xl font-semibold text-ink max-w-2xl leading-tight">
                {{ __('home.values_title') }}
            </h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @for ($i = 1; $i <= 4; $i++)
                <div class="card-premium p-7 group relative overflow-hidden">
                    {{-- Hover accent --}}
                    <div class="absolute top-0 start-0 end-0 h-0.5 bg-gradient-to-r from-brass-300 via-brass-500 to-brass-300 opacity-0 group-hover:opacity-100 transition-opacity duration-400"></div>

                    <div class="mb-5 size-12 rounded-xl bg-brass-50 border border-brass-100 flex items-center justify-center group-hover:bg-brass-100 transition-colors duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-brass-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $valueIconPaths[$i - 1] }}"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-ink mb-2.5">{{ __('home.value_' . $i . '_title') }}</h3>
                    <p class="text-sm text-stone-500 leading-relaxed">{{ __('home.value_' . $i . '_desc') }}</p>
                </div>
            @endfor
        </div>
    </div>
</section>

{{-- ============================================================
     ABOUT TEASER
============================================================= --}}
<section class="relative overflow-hidden py-20 px-4" id="about">
    <div class="absolute inset-0 bg-gradient-to-r from-brass-50 to-stone-100/60"></div>
    <div class="absolute inset-0 border-y border-stone-200/60"></div>

    <div class="relative max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row gap-10 items-center">
            {{-- Avatar --}}
            <div class="shrink-0">
                <div class="relative">
                    <div class="absolute -inset-2 rounded-full border border-brass-300/40 float"></div>
                    <div class="size-28 md:size-36 rounded-full border-2 border-brass-300 overflow-hidden bg-gradient-to-br from-brass-100 to-brass-200 flex items-center justify-center shadow-lg">
                        <span class="text-4xl md:text-5xl font-bold text-brass-500/50 select-none"
                              style="font-family: var(--font-display-fr);">SB</span>
                    </div>
                </div>
            </div>

            {{-- Content --}}
            <div class="flex-1 text-center md:text-start">
                <div class="section-label mb-4 justify-center md:justify-start">{{ __('nav.about') }}</div>
                <h2 class="text-2xl md:text-3xl font-semibold text-ink mb-3">{{ __('home.about_teaser_title') }}</h2>
                <p class="text-[0.9375rem] text-stone-500 max-w-prose leading-relaxed">{{ __('home.about_teaser_desc') }}</p>
                <a href="/{{ app()->getLocale() }}/maitre-bouhamidi"
                   class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-brass-600 hover:text-brass-700 transition-fast group">
                    {{ __('home.about_teaser_link') }}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 rtl:scale-x-[-1] transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     FAQ TEASER
============================================================= --}}
<section class="py-28 md:py-36 px-4" id="faq">
    <div class="max-w-3xl mx-auto">
        <div class="flex flex-col items-center text-center gap-5 mb-14">
            <div class="section-label">FAQ</div>
            <h2 class="text-4xl md:text-5xl font-semibold text-ink leading-tight">
                {{ __('home.faq_teaser_title') }}
            </h2>
        </div>

        <div class="space-y-0 divide-y divide-stone-200/70">
            @forelse ($faqs as $faq)
                <div x-data="faqItem()" class="group">
                    <button x-on:click="toggle()"
                            class="w-full py-5 flex justify-between items-center text-start gap-4"
                            :aria-expanded="open">
                        <span class="text-[1rem] font-semibold text-ink group-hover:text-brass-700 transition-colors duration-200">
                            {{ locale_string($faq->question_translations, app()->getLocale()) }}
                        </span>
                        <span class="shrink-0 flex size-7 items-center justify-center rounded-full border border-stone-200 bg-white group-hover:border-brass-300 group-hover:bg-brass-50 transition-all duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-3.5 w-3.5 text-brass-500 transition-transform duration-300"
                                 :class="open && 'rotate-180'"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </span>
                    </button>
                    <div x-show="open"
                         x-transition:enter="transition-all ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition-all ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         class="pb-5">
                        <p class="text-[0.9375rem] text-stone-500 leading-relaxed">
                            {{ locale_string($faq->answer_translations, app()->getLocale()) }}
                        </p>
                    </div>
                </div>
            @empty
                <p class="py-8 text-stone-500 text-center">{{ __('common.empty') }}</p>
            @endforelse
        </div>

        <div class="mt-10 text-center">
            <a href="/{{ app()->getLocale() }}/faq"
               class="btn-ghost rounded-xl text-sm scale-on-press">
                {{ __('home.faq_teaser_link') }}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 rtl:scale-x-[-1]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>
</section>

<x-public.cta-section
    :title="__('home.cta_title')"
    :description="__('home.cta_desc')"
    :buttonText="__('home.cta_button')"
    accented
/>
@endsection
