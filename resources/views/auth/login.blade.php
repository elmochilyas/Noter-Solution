@extends('layouts.public')

@section('title', __('auth.login_title') . ' — Sana Bouhamidi')

@section('content')
<section class="relative overflow-hidden min-h-[85vh] flex items-center justify-center px-4 py-20">
    {{-- Background blobs --}}
    <div class="pointer-events-none absolute inset-0 overflow-hidden">
        <div class="absolute -top-24 -end-24 w-[480px] h-[480px] rounded-full bg-brass-100/50 blur-3xl float-slow"></div>
        <div class="absolute -bottom-24 -start-24 w-[360px] h-[360px] rounded-full bg-brass-200/35 blur-3xl float-delayed"></div>
        <div class="absolute inset-0 opacity-[0.015]"
             style="background-image: linear-gradient(#B68A3E 1px, transparent 1px), linear-gradient(90deg, #B68A3E 1px, transparent 1px); background-size: 40px 40px;"></div>
    </div>

    <div class="relative w-full max-w-md reveal-up">
        {{-- Card --}}
        <div class="glass-card rounded-2xl p-8 md:p-10 shadow-2xl shadow-ink/10">

            {{-- Header --}}
            <div class="text-center mb-8">
                <div class="inline-flex size-14 items-center justify-center rounded-2xl bg-brass-50 border border-brass-200 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-brass-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-semibold text-ink">{{ __('auth.login_title') }}</h1>
                <p class="mt-1.5 text-sm text-stone-500">Accédez à votre espace personnel</p>
            </div>

            {{-- Errors --}}
            @if ($errors->any())
                <div class="alert alert-danger mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                    </svg>
                    <div>
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block mb-1.5 text-sm font-medium text-stone-700">
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
                        class="field"
                        placeholder="votre@email.com"
                    >
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="text-sm font-medium text-stone-700">
                            {{ __('auth.password') }}
                        </label>
                        <a href="{{ route('password.request') }}"
                           class="text-xs text-brass-600 hover:text-brass-700 font-medium hover:underline transition-fast">
                            {{ __('auth.forgot_password') }}
                        </a>
                    </div>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        required
                        autocomplete="current-password"
                        class="field"
                        placeholder="••••••••"
                    >
                </div>

                <div class="flex items-center gap-2.5">
                    <input
                        type="checkbox"
                        name="remember"
                        id="remember"
                        class="rounded border-stone-300 text-brass-500 focus:ring-brass-500 focus:ring-offset-0 size-4"
                    >
                    <label for="remember" class="text-sm text-stone-600 cursor-pointer select-none">
                        {{ __('auth.remember') }}
                    </label>
                </div>

                <button type="submit"
                        class="btn-brass w-full justify-center py-3 rounded-xl text-sm scale-on-press mt-2">
                    {{ __('auth.login') }}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 rtl:scale-x-[-1]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                    </svg>
                </button>
            </form>

            {{-- Divider --}}
            <div class="my-6 flex items-center gap-4">
                <div class="flex-1 h-px bg-stone-200"></div>
                <span class="text-xs text-stone-400 font-medium">ou</span>
                <div class="flex-1 h-px bg-stone-200"></div>
            </div>

            {{-- Magic link hint --}}
            @if (Route::has('magic-link'))
                <a href="{{ route('magic-link') }}"
                   class="flex w-full items-center justify-center gap-2.5 rounded-xl border-1.5 border-stone-200 bg-white px-4 py-2.5 text-sm font-medium text-stone-700 hover:bg-stone-50 hover:border-stone-300 transition-all duration-200 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-brass-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 9v.906a2.25 2.25 0 01-1.183 1.981l-6.478 3.488M2.25 9v.906a2.25 2.25 0 001.183 1.981l6.478 3.488m8.839 2.51l-4.66-2.51m0 0l-1.023-.55a2.25 2.25 0 00-2.134 0l-1.022.55m0 0l-4.661 2.51m16.5 1.615a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V8.844a2.25 2.25 0 011.183-1.98l7.5-4.04a2.25 2.25 0 012.134 0l7.5 4.04a2.25 2.25 0 011.183 1.98V19.5z"/>
                    </svg>
                    Lien magique par e-mail
                </a>
            @endif
        </div>

        {{-- Back link --}}
        <p class="mt-6 text-center text-sm text-stone-500">
            <a href="/{{ app()->getLocale() }}" class="text-brass-600 font-medium hover:text-brass-700 hover:underline transition-fast">
                ← Retour au site
            </a>
        </p>
    </div>
</section>
@endsection
