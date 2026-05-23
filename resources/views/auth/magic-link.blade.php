@extends('layouts.public')

@section('title', __('auth.magic_link_title') . ' — Sana Bouhamidi')

@section('content')
<section class="mx-auto max-w-md px-4 py-24 md:px-6 lg:px-8">
    <div class="text-center mb-10 reveal-up">
        <div class="size-16 rounded-2xl bg-brass-50 border border-brass-100 flex items-center justify-center mx-auto mb-5">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-brass-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
        </div>
        <h1 class="text-2xl font-semibold text-ink mb-2">{{ __('auth.magic_link_title') }}</h1>
        <p class="text-sm text-stone-500">{{ __('auth.magic_link_description') }}</p>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-xl bg-danger-bg border border-danger/30 p-4 text-sm text-danger reveal-up">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('portal.login.send', ['locale' => request()->route('locale')]) }}" class="space-y-5 reveal-up" style="animation-delay:80ms">
        @csrf

        <div>
            <label for="email" class="mb-1.5 block text-sm font-medium text-stone-700">
                {{ __('auth.email') }}
            </label>
            <input
                type="email"
                name="email"
                id="email"
                value="{{ old('email') }}"
                required
                autocomplete="email"
                autofocus
                class="block h-11 w-full rounded-xl border border-stone-200 bg-white px-4 text-sm focus:ring-2 focus:ring-brass-400/30 focus:border-brass-400 outline-none transition-fast"
            >
        </div>

        <button type="submit" class="btn-brass w-full h-12 rounded-xl text-sm font-semibold scale-on-press">
            {{ __('auth.send_magic_link') }}
        </button>
    </form>

    <p class="mt-6 text-center text-xs text-stone-400 reveal-up" style="animation-delay:120ms">
        {{ __('auth.magic_link_privacy') }}
    </p>
</section>
@endsection
