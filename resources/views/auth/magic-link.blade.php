@extends('layouts.public')

@section('title', __('auth.magic_link_title') . ' — Sana Bouhamidi')

@section('content')
<section class="mx-auto max-w-md px-4 py-24 md:px-6 lg:px-8">
    <h1 class="text-3xl font-semibold text-ink mb-4 text-center">
        {{ __('auth.magic_link_title') }}
    </h1>
    <p class="mb-8 text-center text-stone-500">
        {{ __('auth.magic_link_description') }}
    </p>

    @if ($errors->any())
        <div class="mb-6 rounded-md bg-danger/10 border border-danger p-4 text-sm text-danger">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('portal.login.send', ['locale' => request()->route('locale')]) }}" class="space-y-6">
        @csrf

        <div>
            <label for="email" class="mb-1.5 text-sm font-medium text-stone-700">
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
                class="block h-11 w-full rounded-md border border-stone-200 bg-white px-3 text-base focus:ring-2 focus:ring-brass-500 focus:border-brass-500"
            >
        </div>

        <button type="submit" class="inline-flex w-full items-center justify-center rounded-md bg-brass-500 px-6 py-3 text-sm font-medium text-parchment hover:bg-brass-600 transition-fast">
            {{ __('auth.send_magic_link') }}
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-stone-500">
        {{ __('auth.magic_link_privacy') }}
    </p>
</section>
@endsection
