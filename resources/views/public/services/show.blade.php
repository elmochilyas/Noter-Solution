@extends('layouts.public')

@section('title', ($service->title_translations[app()->getLocale()] ?? $service->title_translations['fr'] ?? '') . ' — Sana Bouhamidi')
@section('meta_description', strip_tags($service->intro_translations[app()->getLocale()] ?? $service->intro_translations['fr'] ?? ''))

@php $practiceInfo = \App\Models\Setting::practiceInfo(); @endphp

@section('structured_data')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Service",
  "name": "{{ $service->title_translations[app()->getLocale()] ?? $service->title_translations['fr'] ?? '' }}",
  "description": "{{ strip_tags($service->intro_translations[app()->getLocale()] ?? $service->intro_translations['fr'] ?? '') }}",
  "provider": {
    "@type": "LegalService",
    "name": "Sana Bouhamidi — Notaire Adoul",
    "url": "{{ url('/' . app()->getLocale()) }}",
    "telephone": "{{ $practiceInfo['phone'] ?: '+212528380719' }}"
  }
}
</script>
@endsection

@section('content')
    {{-- Breadcrumb --}}
    <div class="max-w-7xl mx-auto px-4 pt-8">
        <nav aria-label="Breadcrumb" class="text-xs text-stone-500 flex items-center gap-2">
            <a href="/{{ app()->getLocale() }}" class="hover:text-brass-500 transition-colors">{{ __('services.breadcrumb_home') }}</a>
            <span>/</span>
            <a href="/{{ app()->getLocale() }}/services" class="hover:text-brass-500 transition-colors">{{ __('services.breadcrumb_services') }}</a>
            <span>/</span>
            <span class="text-ink">{{ $service->title_translations[app()->getLocale()] ?? $service->title_translations['fr'] ?? '' }}</span>
        </nav>
    </div>

    {{-- Hero --}}
    <section class="max-w-7xl mx-auto px-4 py-16 md:py-24">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16">
            <div class="lg:col-span-7 flex flex-col justify-center">
                <span class="text-sm font-semibold uppercase tracking-widest text-brass-500 mb-6 block">{{ __('services.detail_service_label') }}</span>
                <h1 class="text-4xl md:text-5xl font-semibold text-ink mb-8" style="font-family: var(--font-display-fr);">
                    {{ $service->title_translations[app()->getLocale()] ?? $service->title_translations['fr'] ?? '' }}
                </h1>
                <p class="text-lg text-stone-500 max-w-2xl leading-relaxed">
                    {{ $service->intro_translations[app()->getLocale()] ?? $service->intro_translations['fr'] ?? '' }}
                </p>
            </div>
            <div class="lg:col-span-5">
                <div class="sticky top-32 bg-white border border-stone-200 rounded-lg p-8 relative overflow-hidden">
                    <div class="absolute top-0 start-0 end-0 h-[3px] bg-brass-500"></div>
                    <div class="mb-8">
                        <span class="block text-xs font-semibold uppercase tracking-widest text-stone-500 mb-2">{{ __('services.detail_pricing_label') }}</span>
                        <div class="text-3xl font-semibold text-ink">
                            {{ __('services.detail_pricing_from') }} 250 MAD
                        </div>
                        <div class="text-sm text-stone-500 mt-2 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ __('services.detail_consultation_range') }}
                        </div>
                    </div>
                    <div class="space-y-4">
                        <a href="/{{ app()->getLocale() }}/book" class="w-full h-12 bg-brass-500 text-parchment rounded-md flex items-center justify-center text-sm font-semibold hover:bg-brass-600 transition-colors duration-300">
                            {{ __('services.detail_cta') }}
                        </a>
                        <p class="text-xs text-stone-500 text-center">
                            {{ __('services.detail_pricing_note') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Transactions --}}
    <section class="bg-white py-16 md:py-24 border-t border-stone-200">
        <div class="max-w-7xl mx-auto px-4">
            <h2 class="text-3xl font-semibold text-ink mb-12">{{ __('services.detail_transactions_title') }}</h2>
            @php
                $transactions = $service->transactions_translations[app()->getLocale()] ?? $service->transactions_translations['fr'] ?? [];
                $documents = $service->required_documents_translations[app()->getLocale()] ?? $service->required_documents_translations['fr'] ?? [];
            @endphp
            @if (!empty($transactions) && is_array($transactions))
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    @foreach ($transactions as $transaction)
                        <div class="bg-parchment border border-stone-200 rounded-lg p-6 md:p-8 relative group hover:border-brass-500 transition-colors duration-300">
                            <div class="absolute top-0 start-0 end-0 h-[3px] bg-parchment group-hover:bg-brass-500 transition-colors duration-300"></div>
                            <p class="text-base text-ink font-medium">{{ $transaction }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-stone-500">{{ __('common.empty') }}</p>
            @endif
        </div>
    </section>

    {{-- Required documents --}}
    @if (!empty($documents) && is_array($documents))
        <section class="max-w-7xl mx-auto px-4 py-16 md:py-24">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                <div class="lg:col-span-5">
                    <h2 class="text-3xl font-semibold text-ink mb-6">{{ __('services.detail_documents_title') }}</h2>
                    <p class="text-base text-stone-500 mb-8 leading-relaxed">{{ __('services.detail_documents_intro') }}</p>
                </div>
                <div class="lg:col-span-7">
                    <div class="bg-white border border-stone-200 p-8 md:p-12 rounded-lg">
                        <ul class="flex flex-col gap-4">
                            @foreach ($documents as $doc)
                                <li class="flex items-start gap-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-brass-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span class="text-base text-ink">{{ $doc }}</span>
                                </li>
                                @if (!$loop->last)<li class="w-full h-px bg-stone-200/50"></li>@endif
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- Process --}}
    <section class="bg-white py-16 md:py-24 border-t border-stone-200">
        <div class="max-w-7xl mx-auto px-4">
            <h2 class="text-3xl font-semibold text-ink text-center mb-16">{{ __('services.detail_process_title') }}</h2>
            <div class="flex flex-col md:flex-row gap-8 relative">
                <div class="hidden md:block absolute top-[28px] start-[10%] end-[10%] h-px bg-stone-200 z-0"></div>
                @for ($i = 1; $i <= 4; $i++)
                    <div class="flex-1 flex flex-col items-center text-center relative z-10">
                        <div class="size-14 rounded-full border border-brass-500 bg-parchment flex items-center justify-center text-xl font-semibold text-brass-500 mb-6 shadow-[0_0_0_10px_#F7F3EC]">
                            {{ $i }}
                        </div>
                        <h3 class="text-lg font-semibold text-ink mb-3">{{ __('services.process_step' . $i) }}</h3>
                        <p class="text-sm text-stone-500 leading-relaxed">{{ __('services.process_step' . $i . '_desc') }}</p>
                    </div>
                @endfor
            </div>
        </div>
    </section>

    {{-- FAQ --}}
    @if ($faqs->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 py-16 md:py-24">
            <h2 class="text-3xl font-semibold text-ink mb-12 text-center">{{ __('services.detail_faq_title') }}</h2>
            <div class="max-w-3xl mx-auto border-t border-stone-200">
                @foreach ($faqs as $faq)
                    <div x-data="faqItem()" class="border-b border-stone-200">
                        <button x-on:click="toggle()" class="w-full py-6 flex justify-between items-center text-start group" :aria-expanded="open">
                            <span class="text-lg font-medium text-ink">{{ $faq->question_translations[app()->getLocale()] ?? $faq->question_translations['fr'] ?? '' }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-brass-500 shrink-0 transition-transform duration-300" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-collapse class="pb-6">
                            <p class="text-sm text-stone-500 leading-relaxed">{{ $faq->answer_translations[app()->getLocale()] ?? $faq->answer_translations['fr'] ?? '' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- CTA --}}
    <section class="bg-[#0E1B2C] py-16 md:py-24 px-4">
        <div class="max-w-3xl mx-auto text-center">
            <h2 class="text-3xl md:text-4xl font-semibold text-parchment mb-6">{{ __('services.detail_cta') }}</h2>
            <p class="text-lg text-stone-300 mb-10 max-w-2xl mx-auto">{{ __('home.cta_desc') }}</p>
            <a href="/{{ app()->getLocale() }}/book" class="inline-flex h-12 items-center justify-center rounded-md bg-brass-500 px-8 text-sm font-semibold text-parchment hover:bg-brass-600 transition-fast">
                {{ __('services.detail_cta') }}
            </a>
        </div>
    </section>
@endsection
