@extends('layouts.public')

@section('title', locale_string($service->title_translations, app()->getLocale()) . ' — Sana Bouhamidi')
@section('meta_description', strip_tags(locale_string($service->intro_translations, app()->getLocale())))

@php $practiceInfo = \App\Models\Setting::practiceInfo(); @endphp

@section('structured_data')
<script type="application/ld+json" nonce="{{ csp_nonce() }}">
{
  "@@context": "https://schema.org",
  "@@type": "Service",
  "name": "{{ locale_string($service->title_translations, app()->getLocale()) }}",
  "description": "{{ strip_tags(locale_string($service->intro_translations, app()->getLocale())) }}",
  "provider": {
    "@@type": "LegalService",
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
            <a href="/{{ app()->getLocale() }}" class="hover:text-brass-500 transition-fast">{{ __('services.breadcrumb_home') }}</a>
            <span class="text-stone-300">/</span>
            <a href="/{{ app()->getLocale() }}/services" class="hover:text-brass-500 transition-fast">{{ __('services.breadcrumb_services') }}</a>
            <span class="text-stone-300">/</span>
            <span class="text-ink font-medium">{{ locale_string($service->title_translations, app()->getLocale()) }}</span>
        </nav>
    </div>

    {{-- Hero --}}
    <section class="max-w-7xl mx-auto px-4 py-16 md:py-24">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16">
            <div class="lg:col-span-7 flex flex-col justify-center reveal-left">
                <span class="section-label mb-6 block">{{ __('services.detail_service_label') }}</span>
                <h1 class="text-4xl md:text-5xl font-semibold text-ink mb-8 leading-tight">
                    {{ locale_string($service->title_translations, app()->getLocale()) }}
                </h1>
                <p class="text-lg text-stone-500 max-w-2xl leading-relaxed">
                    {{ locale_string($service->intro_translations, app()->getLocale()) }}
                </p>
            </div>
            <div class="lg:col-span-5 reveal-right">
                <div class="sticky top-32 glass-card p-8 relative overflow-hidden">
                    <div class="absolute top-0 start-0 end-0 h-[3px] bg-gradient-to-r from-transparent via-brass-500 to-transparent"></div>
                    <div class="mb-8">
                        <span class="block text-xs font-semibold uppercase tracking-widest text-stone-500 mb-2">{{ __('services.detail_pricing_label') }}</span>
                        <div class="text-3xl font-semibold text-ink">
                            {{ __('services.detail_pricing_from') }} 250 {{ __('plans.currency') }}
                        </div>
                        <div class="text-sm text-stone-500 mt-2 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ __('services.detail_consultation_range') }}
                        </div>
                    </div>
                    <div class="space-y-4">
                        <a href="/{{ app()->getLocale() }}/book" class="btn-brass w-full flex items-center justify-center h-12 rounded-xl text-sm scale-on-press">
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
    <section class="bg-gradient-to-b from-white to-parchment/40 py-16 md:py-24 border-t border-stone-100">
        <div class="max-w-7xl mx-auto px-4">
            <h2 class="text-3xl font-semibold text-ink mb-12 reveal-up">{{ __('services.detail_transactions_title') }}</h2>
            @php
                $transactions = $service->transactions_translations[app()->getLocale()] ?? $service->transactions_translations['fr'] ?? [];
                $documents = $service->required_documents_translations[app()->getLocale()] ?? $service->required_documents_translations['fr'] ?? [];
            @endphp
            @if (!empty($transactions) && is_array($transactions))
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach ($transactions as $idx => $transaction)
                        <div class="card-premium p-6 md:p-8 reveal-up" style="animation-delay:{{ $idx * 60 }}ms">
                            <div class="flex items-start gap-4">
                                <div class="size-8 rounded-lg bg-brass-50 border border-brass-100 flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-brass-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <p class="text-base text-ink font-medium leading-relaxed">{{ $transaction }}</p>
                            </div>
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
                <div class="lg:col-span-5 reveal-left">
                    <h2 class="text-3xl font-semibold text-ink mb-6">{{ __('services.detail_documents_title') }}</h2>
                    <p class="text-base text-stone-500 mb-8 leading-relaxed">{{ __('services.detail_documents_intro') }}</p>
                </div>
                <div class="lg:col-span-7 reveal-right">
                    <div class="glass-card p-8 md:p-12">
                        <ul class="flex flex-col gap-4">
                            @foreach ($documents as $doc)
                                <li class="flex items-start gap-4">
                                    <div class="size-6 rounded-full bg-brass-50 border border-brass-100 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-brass-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <span class="text-base text-ink">{{ $doc }}</span>
                                </li>
                                @if (!$loop->last)<li class="w-full h-px bg-stone-100"></li>@endif
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- Process --}}
    <section class="bg-gradient-to-b from-parchment/30 to-white py-16 md:py-24 border-t border-stone-100">
        <div class="max-w-7xl mx-auto px-4">
            <p class="section-label text-center mb-4 reveal-up">{{ __('services.breadcrumb_services') }}</p>
            <h2 class="text-3xl font-semibold text-ink text-center mb-16 reveal-up" style="animation-delay:60ms">{{ __('services.detail_process_title') }}</h2>
            <div class="flex flex-col md:flex-row gap-8 relative">
                <div class="hidden md:block absolute top-[28px] start-[10%] end-[10%] h-px bg-gradient-to-r from-transparent via-brass-200 to-transparent z-0"></div>
                @for ($i = 1; $i <= 4; $i++)
                    <div class="flex-1 flex flex-col items-center text-center relative z-10 reveal-up" style="animation-delay:{{ $i * 80 }}ms">
                        <div class="size-14 rounded-2xl border border-brass-300 bg-white flex items-center justify-center text-xl font-semibold text-brass-500 mb-6 shadow-sm shadow-brass-100">
                            {{ $i }}
                        </div>
                        <h3 class="text-base font-semibold text-ink mb-3">{{ __('services.process_step' . $i) }}</h3>
                        <p class="text-sm text-stone-500 leading-relaxed">{{ __('services.process_step' . $i . '_desc') }}</p>
                    </div>
                @endfor
            </div>
        </div>
    </section>

    {{-- FAQ --}}
    @if ($faqs->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 py-16 md:py-24">
            <h2 class="text-3xl font-semibold text-ink mb-12 text-center reveal-up">{{ __('services.detail_faq_title') }}</h2>
            <div class="max-w-3xl mx-auto divide-y divide-stone-100">
                @foreach ($faqs as $faq)
                    <div x-data="{ open: false }" class="group">
                        <button x-on:click="open = !open" class="w-full py-6 flex justify-between items-center text-start" :aria-expanded="open">
                            <span class="text-base font-medium text-ink group-hover:text-brass-600 transition-fast pe-6">{{ locale_string($faq->question_translations, app()->getLocale()) }}</span>
                            <div class="shrink-0 size-8 rounded-full bg-brass-50 border border-brass-100 flex items-center justify-center transition-fast" :class="open ? 'bg-brass-100 rotate-180' : ''">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-brass-500 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </button>
                        <div x-show="open"
                             x-transition:enter="transition-all ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition-all ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-2"
                             class="pb-6">
                            <p class="text-sm text-stone-500 leading-relaxed">{{ locale_string($faq->answer_translations, app()->getLocale()) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- CTA --}}
    <x-public.cta-section
        :title="__('services.detail_cta')"
        :description="__('home.cta_desc')"
        :buttonText="__('services.detail_cta')"
    />
@endsection
