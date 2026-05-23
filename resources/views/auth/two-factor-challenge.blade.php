@extends('layouts.public')

@section('title', __('auth.2fa_title') . ' — Sana Bouhamidi')

@section('content')
<section class="mx-auto max-w-md px-4 py-24 md:px-6 lg:px-8">
    <div class="text-center mb-10 reveal-up">
        <div class="size-16 rounded-2xl bg-brass-50 border border-brass-100 flex items-center justify-center mx-auto mb-5">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-brass-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
        </div>
        <h1 class="text-2xl font-semibold text-ink mb-2">{{ __('auth.2fa_title') }}</h1>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-xl bg-danger-bg border border-danger/30 p-4 text-sm text-danger reveal-up">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="card-premium p-6 mb-6 reveal-up" style="animation-delay:60ms">
        <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-5">
            @csrf
            <div>
                <label for="code" class="mb-1.5 block text-sm font-medium text-stone-700">
                    {{ __('auth.2fa_code') }}
                </label>
                <input
                    type="text"
                    name="code"
                    id="code"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    required
                    autofocus
                    class="block h-12 w-full rounded-xl border border-stone-200 bg-white px-4 text-base text-center tracking-[0.5em] font-mono focus:ring-2 focus:ring-brass-400/30 focus:border-brass-400 outline-none transition-fast"
                    placeholder="• • • • • •"
                >
            </div>
            <button type="submit" class="btn-brass w-full h-12 rounded-xl text-sm font-semibold scale-on-press">
                {{ __('auth.2fa_verify') }}
            </button>
        </form>
    </div>

    <div class="relative flex items-center gap-4 mb-6 reveal-up" style="animation-delay:80ms">
        <span class="flex-1 h-px bg-stone-100"></span>
        <span class="text-xs text-stone-400 font-medium">{{ __('auth.2fa_or_recovery') ?? 'ou' }}</span>
        <span class="flex-1 h-px bg-stone-100"></span>
    </div>

    <div class="card-premium p-6 reveal-up" style="animation-delay:100ms">
        <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-5">
            @csrf
            <div>
                <label for="recovery_code" class="mb-1.5 block text-sm font-medium text-stone-700">
                    {{ __('auth.2fa_recovery') }}
                </label>
                <input
                    type="text"
                    name="recovery_code"
                    id="recovery_code"
                    autocomplete="off"
                    class="block h-11 w-full rounded-xl border border-stone-200 bg-white px-4 text-sm font-mono focus:ring-2 focus:ring-brass-400/30 focus:border-brass-400 outline-none transition-fast"
                >
            </div>
            <button type="submit" class="btn-ghost w-full h-11 rounded-xl text-sm font-semibold scale-on-press">
                {{ __('auth.2fa_use_recovery') }}
            </button>
        </form>
    </div>
</section>
@endsection
