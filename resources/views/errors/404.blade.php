@extends('layouts.error')

@section('title', __('errors.404_title') . ' — Sana Bouhamidi')

@section('content')
    {{-- Large number --}}
    <div class="relative mb-6">
        <span class="text-[8rem] md:text-[10rem] font-bold leading-none select-none"
              style="font-family: var(--font-display-fr); background: linear-gradient(135deg, #E2DDD2 0%, #CDA45B 40%, #B68A3E 70%, #E8D9BA 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;">
            404
        </span>
    </div>

    <h1 class="text-2xl font-semibold text-ink mb-3">{{ __('errors.404_title') }}</h1>
    <p class="text-stone-500 mb-8 leading-relaxed max-w-sm mx-auto">
        {{ __('errors.404_description') }}
    </p>

    <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <a href="/{{ app()->getLocale() }}"
           class="btn-brass px-8 py-3 rounded-xl text-sm scale-on-press">
            {{ __('errors.back_home') }}
        </a>
        <a href="/{{ app()->getLocale() }}/contact"
           class="btn-ink px-8 py-3 rounded-xl text-sm scale-on-press">
            {{ __('nav.contact') }}
        </a>
    </div>

    <p class="mt-8 text-sm text-stone-500">
        {{ __('errors.or_call') }}
        <a href="tel:+212528380719" class="text-brass-600 font-semibold hover:text-brass-700 transition-fast">
            <bdi dir="ltr">05 28 38 07 19</bdi>
        </a>
    </p>
@endsection
