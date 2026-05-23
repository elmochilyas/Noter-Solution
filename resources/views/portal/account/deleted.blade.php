@extends('layouts.public')

@section('title', __('portal.account_deleted_title').' — '.config('app.name'))

@section('content')
<section class="mx-auto max-w-md px-4 py-24 md:px-6 lg:px-8 text-center">
    <div class="size-20 rounded-2xl bg-stone-50 border border-stone-200 flex items-center justify-center mx-auto mb-6 reveal-scale">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9 text-stone-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
        </svg>
    </div>

    <h1 class="text-2xl font-semibold text-ink mb-3 reveal-up">{{ __('portal.account_deleted_title') }}</h1>
    <p class="text-stone-500 max-w-sm mx-auto text-sm leading-relaxed reveal-up" style="animation-delay:60ms">
        {{ __('portal.account_deleted_message') }}
    </p>

    <a href="/{{ app()->getLocale() }}" class="mt-8 btn-brass inline-flex items-center justify-center rounded-xl px-6 py-3 text-sm font-semibold scale-on-press reveal-up" style="animation-delay:100ms">
        {{ __('portal.back_to_home') }}
    </a>
</section>
@endsection
