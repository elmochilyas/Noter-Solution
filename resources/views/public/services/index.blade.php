@extends('layouts.public')

@section('title', __('services.page_title') . ' — Sana Bouhamidi')
@section('meta_description', __('services.page_intro'))

@section('content')
    <x-public.page-hero
        :label="__('nav.services')"
        :title="__('services.page_title_long')"
        :description="__('services.page_intro')"
    />

    {{-- Services grid --}}
    <section class="pb-24 px-4 max-w-7xl mx-auto space-y-20">
        @foreach ($services as $i => $service)
            @php $imageRight = $i % 2 === 0; @endphp
            <div class="grid md:grid-cols-2 gap-12 items-center reveal-up" style="animation-delay:{{ $i * 80 }}ms">
                @if ($imageRight)
                    <div class="aspect-[4/3] rounded-2xl overflow-hidden bg-gradient-to-br from-brass-50 via-brass-100 to-brass-200 border border-brass-100 flex items-center justify-center shadow-lg">
                        <svg class="w-20 h-20 text-brass-400/30 float-slow" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
                        </svg>
                    </div>
                @endif
                <div class="card-premium p-8 md:p-10 relative">
                    <div class="absolute top-0 start-8 end-8 h-[2px] bg-gradient-to-r from-transparent via-brass-400 to-transparent rounded-t opacity-60"></div>
                    <h2 class="text-2xl font-semibold text-ink mb-4">{{ locale_string($service->title_translations, app()->getLocale()) }}</h2>
                    <p class="text-sm text-stone-500 mb-6 leading-relaxed">{{ locale_string($service->intro_translations, app()->getLocale()) }}</p>
                    <a href="/{{ app()->getLocale() }}/services/{{ $service->slug }}" class="inline-flex items-center gap-2 text-sm font-semibold text-brass-600 hover:text-brass-700 transition-fast group">
                        {{ __('common.discover') }}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 rtl:scale-x-[-1] group-hover:translate-x-1 rtl:group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
                @if (!$imageRight)
                    <div class="aspect-[4/3] rounded-2xl overflow-hidden bg-gradient-to-br from-brass-50 via-brass-100 to-brass-200 border border-brass-100 flex items-center justify-center shadow-lg">
                        <svg class="w-20 h-20 text-brass-400/30 float-slow" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
                        </svg>
                    </div>
                @endif
            </div>
        @endforeach
    </section>

    <x-public.cta-section
        :title="__('home.cta_title')"
        :description="__('home.cta_desc')"
        :buttonText="__('home.cta_button')"
    />
@endsection
