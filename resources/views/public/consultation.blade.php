@extends('layouts.public')

@section('title', __('plans.page_title') . ' — Sana Bouhamidi')
@section('meta_description', __('plans.page_intro'))

@section('content')
    {{-- Page intro --}}
    <section class="pt-32 pb-16 md:pb-24 px-4 max-w-7xl mx-auto text-center">
        <span class="text-sm font-semibold uppercase tracking-widest text-brass-500 mb-6 block">{{ __('nav.consultation') }}</span>
        <h1 class="text-4xl md:text-5xl font-semibold text-ink mb-6 max-w-3xl mx-auto leading-tight" style="font-family: var(--font-display-fr);">
            {{ __('plans.page_title') }}
        </h1>
        <p class="text-lg text-stone-500 max-w-2xl mx-auto leading-relaxed">
            {{ __('plans.page_intro') }}
        </p>
    </section>

    {{-- Plans grid --}}
    <section class="pb-24 px-4 max-w-7xl mx-auto">
        @php $locale = app()->getLocale(); @endphp
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-4 items-start">
            @forelse ($plans as $plan)
                <div class="relative bg-white border border-stone-200 rounded-lg p-6 md:p-8 flex flex-col @if($plan->is_recommended) ring-2 ring-brass-500 shadow-lg @endif">
                    @if($plan->is_recommended)
                        <span class="absolute -top-3 start-1/2 -translate-x-1/2 bg-brass-500 text-parchment text-xs font-semibold uppercase tracking-wider px-4 py-1 rounded-full">
                            {{ __('plans.recommended_badge') }}
                        </span>
                    @endif
                    <div class="flex flex-col h-full">
                        <h2 class="text-xl font-semibold text-ink mb-3 mt-2">
                            {{ $plan->name_translations[$locale] ?? $plan->name_translations['fr'] ?? '' }}
                        </h2>
                        <p class="text-sm text-stone-500 mb-6 leading-relaxed">
                            {{ $plan->description_translations[$locale] ?? $plan->description_translations['fr'] ?? '' }}
                        </p>
                        <div class="mb-6 flex items-baseline gap-1">
                            <span class="text-3xl font-semibold text-ink">
                                {{ $plan->price_centimes === 0 ? 'Gratuit' : number_format($plan->price_centimes / 100, 2, ',', ' ') . ' MAD' }}
                            </span>
                            @if($plan->duration_minutes)
                                <span class="text-sm text-stone-500">
                                    / {{ $plan->duration_minutes }} {{ __('plans.minutes') }}
                                </span>
                            @endif
                        </div>
                        <div class="mb-6">
                            <span class="text-xs font-semibold uppercase tracking-wider text-stone-500 mb-3 block">{{ __('plans.features_title') }}</span>
                            <ul class="space-y-2">
                                @php $features = $plan->included_features[$locale] ?? $plan->included_features['fr'] ?? []; @endphp
                                @foreach ($features as $feature)
                                    <li class="flex items-start gap-2 text-sm text-stone-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-brass-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        <span>{{ $feature }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <span class="text-xs text-stone-400 mb-4">
                            @php
                                $formatKey = match($plan->format) {
                                    'video' => 'plans.format_video',
                                    'office' => 'plans.format_office',
                                    default => 'plans.format_both',
                                };
                            @endphp
                            {{ __($formatKey) }}
                        </span>
                        <a href="/{{ $locale }}/book" class="mt-auto w-full h-11 inline-flex items-center justify-center rounded-md bg-brass-500 text-sm font-semibold text-parchment hover:bg-brass-600 transition-fast">
                            {{ __('plans.cta') }}
                        </a>
                    </div>
                </div>
            @empty
                <p class="col-span-full text-center text-stone-500 py-12">{{ __('common.empty') }}</p>
            @endforelse
        </div>
    </section>

    {{-- Comparison table --}}
    @if($plans->isNotEmpty())
        <section class="pb-24 px-4 max-w-7xl mx-auto">
            <h2 class="text-3xl font-semibold text-ink mb-12 text-center">{{ __('plans.comparison_title') }}</h2>
            <div class="overflow-x-auto rounded-lg border border-stone-200 bg-white">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-stone-200 bg-stone-50">
                            <th class="py-4 px-6 text-start font-semibold text-ink">{{ __('plans.comparison_plan') }}</th>
                            <th class="py-4 px-6 text-start font-semibold text-ink">{{ __('plans.comparison_price') }}</th>
                            <th class="py-4 px-6 text-start font-semibold text-ink">{{ __('plans.comparison_duration') }}</th>
                            <th class="py-4 px-6 text-start font-semibold text-ink">{{ __('plans.comparison_format') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-200">
                        @foreach ($plans as $plan)
                            <tr class="@if($plan->is_recommended) bg-brass-500/5 @endif">
                                <td class="py-4 px-6 font-medium text-ink">{{ $plan->name_translations[$locale] ?? $plan->name_translations['fr'] ?? '' }}</td>
                                <td class="py-4 px-6 text-stone-700">{{ $plan->price_centimes === 0 ? 'Gratuit' : number_format($plan->price_centimes / 100, 2, ',', ' ') . ' MAD' }}</td>
                                <td class="py-4 px-6 text-stone-700">{{ $plan->duration_minutes }} {{ __('plans.minutes') }}</td>
                                <td class="py-4 px-6 text-stone-700">{{ __($formatKey) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    {{-- Disclaimer --}}
    <section class="pb-24 px-4 max-w-3xl mx-auto">
        <div class="bg-stone-50 border border-stone-200 rounded-lg p-6 text-xs text-stone-500 space-y-2 leading-relaxed">
            <p>{{ __('plans.vat_disclaimer') }}</p>
            <p>{{ __('plans.act_fee_disclaimer') }}</p>
        </div>
    </section>

    {{-- CTA --}}
    <section class="py-24 bg-[#0E1B2C] px-4">
        <div class="max-w-3xl mx-auto text-center flex flex-col items-center gap-8">
            <h2 class="text-3xl md:text-4xl font-semibold text-parchment">{{ __('home.cta_title') }}</h2>
            <a href="/{{ app()->getLocale() }}/book" class="inline-flex h-12 items-center justify-center rounded-md bg-brass-500 px-8 text-sm font-semibold text-parchment hover:bg-brass-600 transition-fast">
                {{ __('home.cta_button') }}
            </a>
        </div>
    </section>
@endsection
