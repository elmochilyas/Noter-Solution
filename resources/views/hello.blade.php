@extends('layouts.public')

@section('title', __('common.hello') . ' — Sana Bouhamidi')

@section('content')
<section class="mx-auto max-w-7xl px-4 py-24 md:px-6 lg:px-8">
    <div class="mx-auto max-w-3xl text-center">
        <h1 class="text-5xl md:text-6xl font-semibold text-ink leading-tight">
            {{ __('common.hello') }}
        </h1>
        <p class="mt-4 text-lg text-stone-500 max-w-prose mx-auto">
            {{ __('common.tagline') }}
        </p>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-16 md:px-6 lg:px-8">
    <h2 class="text-3xl font-semibold text-ink mb-8 text-center">
        {{ __('common.color_palette') }}
    </h2>
    <div class="flex flex-wrap justify-center gap-4">
        <div class="flex flex-col items-center">
            <div class="h-16 w-16 rounded-lg shadow-sm bg-ink"></div>
            <span class="mt-2 text-xs text-stone-500">ink</span>
        </div>
        <div class="flex flex-col items-center">
            <div class="h-16 w-16 rounded-lg shadow-sm bg-parchment border border-stone-200"></div>
            <span class="mt-2 text-xs text-stone-500">parchment</span>
        </div>
        <div class="flex flex-col items-center">
            <div class="h-16 w-16 rounded-lg shadow-sm bg-brass-500"></div>
            <span class="mt-2 text-xs text-stone-500">brass-500</span>
        </div>
        <div class="flex flex-col items-center">
            <div class="h-16 w-16 rounded-lg shadow-sm bg-brass-400"></div>
            <span class="mt-2 text-xs text-stone-500">brass-400</span>
        </div>
        <div class="flex flex-col items-center">
            <div class="h-16 w-16 rounded-lg shadow-sm bg-brass-600"></div>
            <span class="mt-2 text-xs text-stone-500">brass-600</span>
        </div>
        <div class="flex flex-col items-center">
            <div class="h-16 w-16 rounded-lg shadow-sm bg-brass-100"></div>
            <span class="mt-2 text-xs text-stone-500">brass-100</span>
        </div>
        <div class="flex flex-col items-center">
            <div class="h-16 w-16 rounded-lg shadow-sm bg-stone-900"></div>
            <span class="mt-2 text-xs text-stone-500">stone-900</span>
        </div>
        <div class="flex flex-col items-center">
            <div class="h-16 w-16 rounded-lg shadow-sm bg-stone-700"></div>
            <span class="mt-2 text-xs text-stone-500">stone-700</span>
        </div>
        <div class="flex flex-col items-center">
            <div class="h-16 w-16 rounded-lg shadow-sm bg-stone-500"></div>
            <span class="mt-2 text-xs text-stone-500">stone-500</span>
        </div>
        <div class="flex flex-col items-center">
            <div class="h-16 w-16 rounded-lg shadow-sm bg-stone-200"></div>
            <span class="mt-2 text-xs text-stone-500">stone-200</span>
        </div>
        <div class="flex flex-col items-center">
            <div class="h-16 w-16 rounded-lg shadow-sm bg-stone-100"></div>
            <span class="mt-2 text-xs text-stone-500">stone-100</span>
        </div>
        <div class="flex flex-col items-center">
            <div class="h-16 w-16 rounded-lg shadow-sm bg-success"></div>
            <span class="mt-2 text-xs text-stone-500">success</span>
        </div>
        <div class="flex flex-col items-center">
            <div class="h-16 w-16 rounded-lg shadow-sm bg-warning"></div>
            <span class="mt-2 text-xs text-stone-500">warning</span>
        </div>
        <div class="flex flex-col items-center">
            <div class="h-16 w-16 rounded-lg shadow-sm bg-danger"></div>
            <span class="mt-2 text-xs text-stone-500">danger</span>
        </div>
        <div class="flex flex-col items-center">
            <div class="h-16 w-16 rounded-lg shadow-sm bg-info"></div>
            <span class="mt-2 text-xs text-stone-500">info</span>
        </div>
    </div>
</section>

<section class="bg-white py-16">
    <div class="mx-auto max-w-7xl px-4 md:px-6 lg:px-8">
        <h2 class="text-3xl font-semibold text-ink mb-8 text-center">
            {{ __('common.typography') }}
        </h2>
        <div class="grid gap-12 md:grid-cols-2">
            <div>
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-brass-500">
                    {{ __('common.french') }}
                </h3>
                <p class="text-6xl font-semibold mb-6">
                    Fraunces
                </p>
                <p class="text-base leading-relaxed max-w-prose">
                    Sana Bouhamidi est notaire de droit marocain. Son cabinet accompagne les particuliers et les entreprises dans leurs démarches juridiques et notariales.
                </p>
            </div>
            <div dir="rtl">
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-brass-500">
                    {{ __('common.arabic') }}
                </h3>
                <p class="text-6xl font-semibold mb-6" style="font-family: var(--font-display-ar);">
                    رییم کوفي
                </p>
                <p class="text-base leading-relaxed max-w-prose text-end" style="font-family: var(--font-body-ar);">
                    سناء بوحميدي موثقة وفق القانون المغربي. يرافق مكتبها الأفراد والشركات في إجراءاتهم القانونية والتوثيقية.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-16 md:px-6 lg:px-8">
    <div class="flex flex-wrap items-center justify-center gap-4">
        <a href="#" class="inline-flex items-center justify-center rounded-md bg-brass-500 px-6 py-3 text-sm font-medium text-parchment hover:bg-brass-600 transition-fast">
            {{ __('common.primary_cta') }}
        </a>
        <a href="#" class="inline-flex items-center justify-center rounded-md border border-ink px-6 py-3 text-sm font-medium text-ink hover:bg-ink hover:text-parchment transition-fast">
            {{ __('common.secondary_cta') }}
        </a>
        <a href="#" class="inline-flex items-center justify-center rounded-md px-5 py-2 text-sm font-medium text-brass-500 hover:underline transition-fast">
            {{ __('common.tertiary_cta') }}
        </a>
    </div>
</section>
@endsection
