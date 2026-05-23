@extends('layouts.public')

@section('title', __('auth.link_sent_title') . ' — Sana Bouhamidi')

@section('content')
<section class="mx-auto max-w-md px-4 py-24 md:px-6 lg:px-8 text-center">
    <div class="size-20 rounded-2xl bg-success-bg border border-success/20 flex items-center justify-center mx-auto mb-6 reveal-scale">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9 text-success" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
    </div>

    <h1 class="text-2xl font-semibold text-ink mb-3 reveal-up">{{ __('auth.link_sent_title') }}</h1>
    <p class="text-stone-500 max-w-sm mx-auto text-sm leading-relaxed reveal-up" style="animation-delay:60ms">
        {{ __('auth.link_sent_description') }}
    </p>

    <a href="{{ route('portal.login', ['locale' => request()->route('locale')]) }}" class="mt-8 btn-ghost inline-flex items-center justify-center rounded-xl px-6 py-3 text-sm font-semibold scale-on-press reveal-up" style="animation-delay:100ms">
        {{ __('auth.back_to_login') }}
    </a>
</section>
@endsection
