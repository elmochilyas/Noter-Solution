@extends('layouts.error')

@section('title', __('errors.503_title') . ' — Sana Bouhamidi')

@section('content')
    <div class="mb-4 text-brass-500">
        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto size-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
    </div>
    <h1 class="text-3xl font-semibold text-ink mb-4">
        {{ __('errors.503_title') }}
    </h1>
    <p class="text-stone-500 mb-8">
        {{ __('errors.503_description') }}
    </p>
    <p class="text-sm text-stone-400">
        {{ __('errors.urgent_contact') }}
        <br>
        <bdi dir="ltr" class="font-medium text-stone-700">05 28 38 07 19</bdi>
        <span class="mx-2 text-stone-300">·</span>
        <bdi dir="ltr" class="font-medium text-stone-700">06 66 12 06 61</bdi>
    </p>
@endsection
