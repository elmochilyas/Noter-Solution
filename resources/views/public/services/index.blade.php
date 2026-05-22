@extends('layouts.public')

@section('title', __('services.page_title') . ' — Sana Bouhamidi')
@section('meta_description', __('services.page_intro'))

@section('content')
    {{-- Page intro --}}
    <section class="pt-32 pb-24 px-4 max-w-7xl mx-auto text-center">
        <span class="text-sm font-semibold uppercase tracking-widest text-brass-500 mb-6 block">{{ __('nav.services') }}</span>
        <h1 class="text-4xl md:text-5xl font-semibold text-ink mb-6 max-w-3xl mx-auto leading-tight" style="font-family: var(--font-display-fr);">
            {{ __('services.page_title_long') }}
        </h1>
        <p class="text-lg text-stone-500 max-w-2xl mx-auto leading-relaxed">
            {{ __('services.page_intro') }}
        </p>
    </section>

    {{-- Services grid --}}
    <section class="pb-24 px-4 max-w-7xl mx-auto space-y-24">
        @foreach ($services as $i => $service)
            @php $imageRight = $i % 2 === 0; @endphp
            <div class="grid md:grid-cols-2 gap-12 items-center">
                @if ($imageRight)
                    <div class="aspect-[4/3] rounded-lg overflow-hidden bg-stone-100 border border-stone-200">
                        <img src="https://via.placeholder.com/600x450/EFEAE0/6B6660?text={{ urlencode(Str::limit($service->title_translations['fr'] ?? '', 20)) }}" alt="" class="w-full h-full object-cover" loading="lazy">
                    </div>
                @endif
                <div class="p-8 bg-white rounded-lg border border-stone-200 relative">
                    <div class="absolute top-0 start-8 end-8 h-[3px] bg-brass-500 rounded-t"></div>
                    <h2 class="text-2xl font-semibold text-ink mb-4">{{ $service->title_translations[app()->getLocale()] ?? $service->title_translations['fr'] ?? '' }}</h2>
                    <p class="text-sm text-stone-500 mb-6 leading-relaxed">{{ $service->intro_translations[app()->getLocale()] ?? $service->intro_translations['fr'] ?? '' }}</p>
                    <a href="/{{ app()->getLocale() }}/services/{{ $service->slug }}" class="text-sm font-semibold text-brass-500 hover:text-brass-600 inline-flex items-center gap-2 transition-fast">
                        {{ __('common.discover') }}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 rtl:scale-x-[-1]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
                @if (!$imageRight)
                    <div class="aspect-[4/3] rounded-lg overflow-hidden bg-stone-100 border border-stone-200">
                        <img src="https://via.placeholder.com/600x450/EFEAE0/6B6660?text={{ urlencode(Str::limit($service->title_translations['fr'] ?? '', 20)) }}" alt="" class="w-full h-full object-cover" loading="lazy">
                    </div>
                @endif
            </div>
        @endforeach
    </section>

    {{-- CTA --}}
    <section class="py-24 bg-[#0E1B2C] px-4 text-center">
        <div class="max-w-2xl mx-auto">
            <h2 class="text-3xl md:text-4xl font-semibold text-parchment mb-8">{{ __('home.cta_title') }}</h2>
            <p class="text-lg text-stone-300 mb-10">{{ __('home.cta_desc') }}</p>
            <a href="/{{ app()->getLocale() }}/book" class="inline-flex h-12 items-center justify-center rounded-md bg-brass-500 px-8 text-sm font-semibold text-parchment hover:bg-brass-600 transition-fast">
                {{ __('home.cta_button') }}
            </a>
        </div>
    </section>
@endsection
