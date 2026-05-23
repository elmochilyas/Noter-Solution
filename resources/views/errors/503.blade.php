@extends('layouts.error')

@section('title', __('errors.503_title') . ' — Sana Bouhamidi')

@section('content')
    {{-- Icon --}}
    <div class="mb-6 flex justify-center">
        <div class="size-20 rounded-2xl bg-warning-bg border border-warning/20 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-9 text-warning" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
            </svg>
        </div>
    </div>

    <h1 class="text-2xl font-semibold text-ink mb-3">{{ __('errors.503_title') }}</h1>
    <p class="text-stone-500 mb-8 leading-relaxed max-w-sm mx-auto">
        {{ __('errors.503_description') }}
    </p>

    <div class="glass-card rounded-2xl p-5 max-w-xs mx-auto text-sm text-stone-600">
        <p class="mb-2 font-medium text-stone-700">{{ __('errors.urgent_contact') }}</p>
        <div class="space-y-1">
            <a href="tel:+212528380719" class="flex items-center gap-2 hover:text-brass-600 transition-fast">
                <bdi dir="ltr" class="font-semibold text-brass-600">05 28 38 07 19</bdi>
            </a>
            <a href="tel:+212666120661" class="flex items-center gap-2 hover:text-brass-600 transition-fast">
                <bdi dir="ltr" class="font-semibold text-brass-600">06 66 12 06 61</bdi>
            </a>
        </div>
    </div>
@endsection
