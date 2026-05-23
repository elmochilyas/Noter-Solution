@extends('layouts.public')

@section('title', __('plans.page_title') . ' — Sana Bouhamidi')
@section('meta_description', __('plans.page_intro'))

@section('content')
    <x-public.page-hero
        :label="__('nav.consultation')"
        :title="__('plans.page_title')"
        :description="__('plans.page_intro')"
    />

    {{-- Plans grid --}}
    <section class="pb-20 px-4 max-w-7xl mx-auto">
        @php $locale = app()->getLocale(); @endphp
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-4 items-stretch">
            @forelse ($plans as $idx => $plan)
                <div class="relative flex flex-col card-premium p-6 md:p-8 reveal-up @if($plan->is_recommended) ring-2 ring-brass-400 shadow-xl @endif" style="animation-delay:{{ $idx * 80 }}ms">
                    @if($plan->is_recommended)
                        <div class="absolute -top-3.5 start-1/2 -translate-x-1/2 bg-gradient-to-r from-brass-500 to-brass-600 text-parchment text-xs font-semibold uppercase tracking-wider px-5 py-1.5 rounded-full shadow-sm whitespace-nowrap">
                            {{ __('plans.recommended_badge') }}
                        </div>
                    @endif
                    <div class="flex flex-col h-full mt-2">
                        <h2 class="text-xl font-semibold text-ink mb-3">
                            {{ locale_string($plan->name_translations, $locale) }}
                        </h2>
                        <p class="text-sm text-stone-500 mb-6 leading-relaxed">
                            {{ locale_string($plan->description_translations, $locale) }}
                        </p>
                        <div class="mb-6 flex items-baseline gap-1 pb-6 border-b border-stone-100">
                            <span class="text-3xl font-semibold text-ink">
{{ $plan->price_centimes === 0 ? __('booking.free') : number_format($plan->price_centimes / 100, 2, ',', ' ') . ' ' . __('plans.currency') }}
                            </span>
                            @if($plan->duration_minutes)
                                <span class="text-sm text-stone-500">
                                    / {{ $plan->duration_minutes }} {{ __('plans.minutes') }}
                                </span>
                            @endif
                        </div>
                        <div class="mb-6 flex-1">
                            <span class="text-xs font-semibold uppercase tracking-wider text-stone-500 mb-3 block">{{ __('plans.features_title') }}</span>
                            <ul class="space-y-2.5">
                                @php $features = $plan->included_features[$locale] ?? $plan->included_features['fr'] ?? []; @endphp
                                @foreach ($features as $feature)
                                    <li class="flex items-start gap-2.5 text-sm text-stone-700">
                                        <div class="size-5 rounded-full bg-brass-50 border border-brass-100 flex items-center justify-center shrink-0 mt-0.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-brass-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                        <span>{{ $feature }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <span class="text-xs text-stone-400 mb-5 block">
                            @php
                                $formatKey = match($plan->format) {
                                    'video' => 'plans.format_video',
                                    'office' => 'plans.format_office',
                                    default => 'plans.format_both',
                                };
                            @endphp
                            {{ __($formatKey) }}
                        </span>
                        <a href="/{{ $locale }}/book" class="btn-brass mt-auto w-full h-11 inline-flex items-center justify-center rounded-xl text-sm scale-on-press">
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
        <section class="pb-20 px-4 max-w-7xl mx-auto reveal-up">
            <h2 class="text-2xl font-semibold text-ink mb-10 text-center">{{ __('plans.comparison_title') }}</h2>
            <div class="overflow-x-auto rounded-2xl border border-stone-100 bg-white shadow-sm">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-stone-100 bg-stone-50">
                            <th class="py-4 px-6 text-start font-semibold text-ink">{{ __('plans.comparison_plan') }}</th>
                            <th class="py-4 px-6 text-start font-semibold text-ink">{{ __('plans.comparison_price') }}</th>
                            <th class="py-4 px-6 text-start font-semibold text-ink">{{ __('plans.comparison_duration') }}</th>
                            <th class="py-4 px-6 text-start font-semibold text-ink">{{ __('plans.comparison_format') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-50">
                        @foreach ($plans as $plan)
                            <tr class="hover:bg-stone-50/50 transition-fast @if($plan->is_recommended) bg-brass-50/30 @endif">
                                <td class="py-4 px-6 font-medium text-ink">{{ locale_string($plan->name_translations, $locale) }}</td>
                                <td class="py-4 px-6 text-stone-700">{{ $plan->price_centimes === 0 ? __('booking.free') : number_format($plan->price_centimes / 100, 2, ',', ' ') . ' ' . __('plans.currency') }}</td>
                                <td class="py-4 px-6 text-stone-700">{{ $plan->duration_minutes }} {{ __('plans.minutes') }}</td>
                                <td class="py-4 px-6 text-stone-700">
                                    @php
                                        $rowFormatKey = match($plan->format) {
                                            'video' => 'plans.format_video',
                                            'office' => 'plans.format_office',
                                            default => 'plans.format_both',
                                        };
                                    @endphp
                                    {{ __($rowFormatKey) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    {{-- Disclaimer --}}
    <section class="pb-20 px-4 max-w-3xl mx-auto">
        <div class="glass-card p-6 text-xs text-stone-500 space-y-2 leading-relaxed reveal-up">
            <p>{{ __('plans.vat_disclaimer') }}</p>
            <p>{{ __('plans.act_fee_disclaimer') }}</p>
        </div>
    </section>

    {{-- Decorative divider --}}
    <div class="flex items-center justify-center gap-3 pb-8">
        <span class="w-16 h-px bg-gradient-to-r from-transparent to-brass-300 block"></span>
        <svg class="size-3 text-brass-400" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
        </svg>
        <span class="w-16 h-px bg-gradient-to-l from-transparent to-brass-300 block"></span>
    </div>

    <x-public.cta-section
        :title="__('home.cta_title')"
        :buttonText="__('home.cta_button')"
    />
@endsection
