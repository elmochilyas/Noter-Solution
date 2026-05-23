@extends('layouts.error')

@section('title', __('errors.500_title') . ' — Sana Bouhamidi')

@section('content')
    <div class="relative mb-6">
        <span class="text-[8rem] md:text-[10rem] font-bold leading-none select-none"
              style="font-family: var(--font-display-fr); background: linear-gradient(135deg, #E2DDD2 0%, #8C2A2A 40%, #6B1F1F 70%, #C9C3B8 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;">
            500
        </span>
    </div>

    <h1 class="text-2xl font-semibold text-ink mb-3">{{ __('errors.500_title') }}</h1>
    <p class="text-stone-500 mb-8 leading-relaxed max-w-sm mx-auto">
        {{ __('errors.500_description') }}
    </p>

    <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <a href="/{{ app()->getLocale() }}"
           class="btn-brass px-8 py-3 rounded-xl text-sm scale-on-press">
            {{ __('errors.back_home') }}
        </a>
    </div>

    <p class="mt-8 text-sm text-stone-500">
        {{ __('errors.or_call') }}
        <a href="tel:+212528380719" class="text-brass-600 font-semibold hover:text-brass-700 transition-fast">
            <bdi dir="ltr">05 28 38 07 19</bdi>
        </a>
    </p>
@endsection
