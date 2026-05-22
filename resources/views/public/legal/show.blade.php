@extends('layouts.public')

@php
    $locale = app()->getLocale();
    $titleKey = "legal.{$page}_title";
    $introKey = "legal.{$page}_intro";
@endphp

@section('title', __($titleKey) . ' — Sana Bouhamidi')
@section('meta_description', __($introKey))

@section('content')
    {{-- Page intro --}}
    <section class="pt-32 pb-16 px-4 max-w-4xl mx-auto">
        <div class="flex flex-col items-center text-center">
            <span class="text-sm font-semibold uppercase tracking-[0.1em] text-brass-500 mb-6 inline-flex items-center gap-4">
                <span class="w-8 h-px bg-brass-500 block"></span>
                {{ __($titleKey) }}
                <span class="w-8 h-px bg-brass-500 block"></span>
            </span>
            <h1 class="text-4xl md:text-5xl font-semibold text-ink mb-6 leading-tight" style="font-family: var(--font-display-fr);">
                {{ __($titleKey) }}
            </h1>
            <p class="text-lg text-stone-500 leading-relaxed max-w-2xl">
                {{ __($introKey) }}
            </p>
        </div>
    </section>

    {{-- Content --}}
    <section class="pb-24 px-4 max-w-4xl mx-auto">
        <div class="bg-white border border-stone-200 rounded-lg p-8 md:p-12">
            <div class="prose prose-stone max-w-none prose-headings:text-ink prose-headings:font-semibold prose-h2:text-xl prose-h2:mt-8 prose-h2:mb-4 prose-h3:text-lg prose-p:text-stone-600 prose-p:leading-relaxed prose-a:text-brass-500 prose-a:no-underline hover:prose-a:underline">
                @if ($page === 'mentions-legales')
                    <p class="text-sm text-stone-500 mb-8">{{ __('legal.last_updated') }} : {{ now()->format('d/m/Y') }}</p>
                    <h2>{{ __('legal.mentions_body_title') }}</h2>
                    <p><strong>{{ __('legal.mentions_body_name') }}</strong></p>
                    <p>{{ __('legal.mentions_body_title_profession') }}</p>
                    <p>{{ __('legal.mentions_body_address') }}</p>
                    <p>{{ __('legal.mentions_body_phone') }}</p>
                    <p>{{ __('legal.mentions_body_email') }}</p>
                    @php $practiceInfo = \App\Models\Setting::practiceInfo(); @endphp
                    @if (!empty($practiceInfo['ice']))
                        <p>{{ __('legal.mentions_body_ice') }} {{ $practiceInfo['ice'] }}</p>
                    @endif
                    @if (!empty($practiceInfo['if']))
                        <p>{{ __('legal.mentions_body_if') }} {{ $practiceInfo['if'] }}</p>
                    @endif
                    @if (!empty($practiceInfo['rc']))
                        <p>RC : {{ $practiceInfo['rc'] }}</p>
                    @endif
                    @if (!empty($practiceInfo['patente']))
                        <p>Patente : {{ $practiceInfo['patente'] }}</p>
                    @endif
                    <p>{{ __('legal.mentions_body_hosting') }}</p>
                @elseif ($page === 'politique-confidentialite')
                    <p class="text-sm text-stone-500 mb-8">{{ __('legal.last_updated') }} : {{ now()->format('d/m/Y') }}</p>
                    <p>{{ __('legal.privacy_body_intro') }}</p>
                    <h2>{{ __('legal.privacy_data_title') }}</h2>
                    <p>{{ __('legal.privacy_data_text') }}</p>
                    <h2>{{ __('legal.privacy_purpose_title') }}</h2>
                    <p>{{ __('legal.privacy_purpose_text') }}</p>
                    <h2>{{ __('legal.privacy_retention_title') }}</h2>
                    <p>{{ __('legal.privacy_retention_text') }}</p>
                    <h2>{{ __('legal.privacy_rights_title') }}</h2>
                    <p>{{ __('legal.privacy_rights_text') }}</p>
                    <h2>{{ __('legal.privacy_cookie_title') }}</h2>
                    <p>{{ __('legal.privacy_cookie_text') }}</p>
                    <p>{{ __('legal.privacy_cndp') }}</p>
                @elseif ($page === 'conditions-utilisation')
                    <p>{{ __('legal.terms_body_intro') }}</p>
                    <h2>{{ __('legal.terms_content_title') }}</h2>
                    <p>{{ __('legal.terms_content_text') }}</p>
                    <h2>{{ __('legal.terms_booking_title') }}</h2>
                    <p>{{ __('legal.terms_booking_text') }}</p>
                    <h2>{{ __('legal.terms_fees_title') }}</h2>
                    <p>{{ __('legal.terms_fees_text') }}</p>
                    <h2>{{ __('legal.terms_responsibility_title') }}</h2>
                    <p>{{ __('legal.terms_responsibility_text') }}</p>
                @endif
            </div>
        </div>
    </section>
@endsection
