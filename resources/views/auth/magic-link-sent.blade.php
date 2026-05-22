@extends('layouts.public')

@section('title', __('auth.link_sent_title') . ' — Sana Bouhamidi')

@section('content')
<section class="mx-auto max-w-md px-4 py-24 md:px-6 lg:px-8 text-center">
    <div class="mb-6 inline-flex items-center justify-center size-16 rounded-full bg-success/10 text-success">
        <svg xmlns="http://www.w3.org/2000/svg" class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
    </div>

    <h1 class="text-3xl font-semibold text-ink mb-4">
        {{ __('auth.link_sent_title') }}
    </h1>
    <p class="text-stone-500 max-w-sm mx-auto">
        {{ __('auth.link_sent_description') }}
    </p>

    <a href="{{ route('portal.login', ['locale' => request()->route('locale')]) }}" class="mt-8 inline-flex items-center justify-center rounded-md bg-brass-500 px-6 py-3 text-sm font-medium text-parchment hover:bg-brass-600 transition-fast">
        {{ __('auth.back_to_login') }}
    </a>
</section>
@endsection
