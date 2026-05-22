@extends('layouts.error')

@section('title', __('errors.404_title') . ' — Sana Bouhamidi')

@section('content')
    <h1 class="text-6xl font-semibold text-ink mb-4">404</h1>
    <p class="text-lg text-stone-500 mb-8">
        {{ __('errors.404_description') }}
    </p>
    <a href="/{{ app()->getLocale() }}" class="inline-flex items-center justify-center rounded-md bg-brass-500 px-6 py-3 text-sm font-medium text-parchment hover:bg-brass-600 transition-fast">
        {{ __('errors.back_home') }}
    </a>
    <p class="mt-6 text-sm text-stone-400">
        {{ __('errors.or_call') }}
        <bdi dir="ltr" class="text-stone-700">05 28 38 07 19</bdi>
    </p>
@endsection
