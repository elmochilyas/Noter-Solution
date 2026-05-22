@extends('layouts.public')

@section('title', __('auth.2fa_title') . ' — Sana Bouhamidi')

@section('content')
<section class="mx-auto max-w-md px-4 py-24 md:px-6 lg:px-8">
    <h1 class="text-3xl font-semibold text-ink mb-8 text-center">
        {{ __('auth.2fa_title') }}
    </h1>

    @if ($errors->any())
        <div class="mb-6 rounded-md bg-danger/10 border border-danger p-4 text-sm text-danger">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-6">
        @csrf

        <div>
            <label for="code" class="mb-1.5 text-sm font-medium text-stone-700">
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
                class="block h-11 w-full rounded-md border border-stone-200 bg-white px-3 text-base text-center tracking-widest focus:ring-2 focus:ring-brass-500 focus:border-brass-500"
                placeholder="000000"
            >
        </div>

        <button type="submit" class="inline-flex w-full items-center justify-center rounded-md bg-brass-500 px-6 py-3 text-sm font-medium text-parchment hover:bg-brass-600 transition-fast">
            {{ __('auth.2fa_verify') }}
        </button>
    </form>

    <hr class="my-8 border-stone-200">

    <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="recovery_code" class="mb-1.5 text-sm font-medium text-stone-700">
                {{ __('auth.2fa_recovery') }}
            </label>
            <input
                type="text"
                name="recovery_code"
                id="recovery_code"
                autocomplete="off"
                class="block h-11 w-full rounded-md border border-stone-200 bg-white px-3 text-base focus:ring-2 focus:ring-brass-500 focus:border-brass-500"
            >
        </div>

        <button type="submit" class="inline-flex w-full items-center justify-center rounded-md border border-ink px-6 py-3 text-sm font-medium text-ink hover:bg-ink hover:text-parchment transition-fast">
            {{ __('auth.2fa_use_recovery') }}
        </button>
    </form>
</section>
@endsection
