@extends('layouts.public')

@php
    $locale = app()->getLocale();
    $keyPrefix = match ($page) {
        'mentions-legales' => 'mentions',
        'politique-confidentialite' => 'privacy',
        'conditions-utilisation' => 'terms',
        default => 'mentions',
    };
    $titleKey = "legal.{$keyPrefix}_title";
    $introKey = "legal.{$keyPrefix}_intro";
@endphp

@section('title', __($titleKey) . ' — Sana Bouhamidi')
@section('meta_description', __($introKey))

@section('content')
    <x-public.page-hero
        :label="__($titleKey)"
        :title="__($titleKey)"
        :description="__($introKey)"
        accented
    />

    {{-- Content --}}
    <section class="pb-24 px-4 max-w-4xl mx-auto reveal-up">
        <div class="glass-card p-8 md:p-14">
            <div class="prose prose-stone max-w-none
                prose-headings:text-ink prose-headings:font-semibold
                prose-h2:text-xl prose-h2:mt-10 prose-h2:mb-4
                prose-h3:text-lg prose-h3:mt-6 prose-h3:mb-3
                prose-p:text-stone-500 prose-p:leading-relaxed
                prose-a:text-brass-600 prose-a:no-underline hover:prose-a:underline
                prose-ul:text-stone-500 prose-li:leading-relaxed">
                @if ($page === 'mentions-legales')
                    <p class="text-sm text-stone-400 mb-8 pb-6 border-b border-stone-100">{{ __('legal.last_updated') }} : {{ now()->format('d/m/Y') }}</p>
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
                        <p>{{ __('legal.rc') }} {{ $practiceInfo['rc'] }}</p>
                    @endif
                    @if (!empty($practiceInfo['patente']))
                        <p>{{ __('legal.patente') }} {{ $practiceInfo['patente'] }}</p>
                    @endif
                    <p>{{ __('legal.mentions_body_hosting') }}</p>
                @elseif ($page === 'politique-confidentialite')
                    <p class="text-sm text-stone-400 mb-8 pb-6 border-b border-stone-100">{{ __('legal.last_updated') }} : {{ now()->format('d/m/Y') }}</p>
                    <p>{{ __('legal.privacy_body_intro') }}</p>
                    <h2>{{ __('legal.privacy_data_title') }}</h2>
                    <p>{{ __('legal.privacy_data_text') }}</p>
                    <h2>{{ __('legal.privacy_purpose_title') }}</h2>
                    <p>{{ __('legal.privacy_purpose_text') }}</p>
                    <h2>{{ __('legal.privacy_recipients_title') }}</h2>
                    <p>{{ __('legal.privacy_recipients_text') }}</p>
                    <h2>{{ __('legal.privacy_transfers_title') }}</h2>
                    <p>{{ __('legal.privacy_transfers_text') }}</p>
                    <h2>{{ __('legal.privacy_retention_title') }}</h2>
                    <p>{{ __('legal.privacy_retention_text') }}</p>
                    <h2>{{ __('legal.privacy_rights_title') }}</h2>
                    <p>{{ __('legal.privacy_rights_text') }}</p>
                    <h2>{{ __('legal.privacy_cookie_title') }}</h2>
                    <p>{{ __('legal.privacy_cookie_text') }}</p>
                    <h2>{{ __('legal.privacy_cndp_title') }}</h2>
                    <p>{{ __('legal.privacy_cndp_text') }}</p>
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
