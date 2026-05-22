@extends('layouts.public')

@section('title', __('portal.account_deleted_title').' — '.config('app.name'))

@section('content')
<section class="mx-auto max-w-md px-4 py-24 md:px-6 lg:px-8 text-center">
    <div class="mb-6 inline-flex items-center justify-center size-16 rounded-full bg-stone/10 text-stone-500">
        <svg xmlns="http://www.w3.org/2000/svg" class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </div>

    <h1 class="text-3xl font-semibold text-ink mb-4">{{ __('portal.account_deleted_title') }}</h1>
    <p class="text-stone-500 max-w-sm mx-auto">{{ __('portal.account_deleted_message') }}</p>

    <a href="/{{ app()->getLocale() }}" class="mt-8 inline-flex items-center justify-center rounded-md bg-brass-500 px-6 py-3 text-sm font-medium text-parchment hover:bg-brass-600 transition-fast">
        {{ __('portal.back_to_home') }}
    </a>
</section>
@endsection
