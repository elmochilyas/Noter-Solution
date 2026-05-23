@extends('layouts.portal')

@section('title', __('portal.delete_account_title').' — '.config('app.name'))

@section('content')
<section class="mx-auto max-w-2xl px-4 py-10 md:px-6 lg:px-8">
    <div class="flex items-center gap-3 mb-8 reveal-up">
        <div class="size-12 rounded-2xl bg-danger-bg border border-danger/20 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-danger" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
        </div>
        <h1 class="text-2xl font-semibold text-ink">{{ __('portal.delete_account_title') }}</h1>
    </div>

    <div class="rounded-2xl border border-danger/20 bg-danger-bg/50 p-6 mb-8 reveal-up" style="animation-delay:60ms">
        <p class="text-sm text-stone-700 font-medium mb-4">{{ __('portal.deletion_warning') }}</p>
        <ul class="space-y-2 mb-4">
            @foreach (['deletion_effect_1', 'deletion_effect_2', 'deletion_effect_3'] as $key)
                <li class="flex items-start gap-2.5 text-sm text-stone-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-danger/60 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    {{ __('portal.' . $key) }}
                </li>
            @endforeach
        </ul>
        <p class="text-sm text-stone-500">{{ __('portal.deletion_irreversible') }}</p>
    </div>

    @if ($errors->any())
        <div class="rounded-xl bg-danger-bg border border-danger/30 p-4 mb-6 reveal-up" style="animation-delay:80ms">
            @foreach ($errors->all() as $error)
                <p class="text-sm text-danger">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="/{{ app()->getLocale() }}/portal/account/delete" class="space-y-5 reveal-up" style="animation-delay:100ms">
        @csrf

        <div>
            <label for="confirmation" class="mb-1.5 block text-sm font-medium text-stone-700">
                {{ app()->getLocale() === 'ar' ? __('portal.type_to_confirm_ar') : __('portal.type_to_confirm') }}
            </label>
            <input type="text" name="confirmation" id="confirmation" required autocomplete="off"
                   placeholder="{{ app()->getLocale() === 'ar' ? 'حذف' : 'SUPPRIMER' }}"
                   class="block h-11 w-full rounded-xl border border-stone-200 bg-white px-4 text-sm font-mono focus:ring-2 focus:ring-danger/20 focus:border-danger outline-none transition-fast">
        </div>

        <button type="submit"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-danger px-6 py-3 text-sm font-semibold text-white hover:bg-danger/90 transition-fast scale-on-press">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            {{ __('portal.confirm_delete') }}
        </button>

        <a href="/{{ app()->getLocale() }}/portal/preferences"
           class="block text-center text-sm text-stone-500 hover:text-ink transition-fast py-1">
            {{ __('portal.cancel') }}
        </a>
    </form>
</section>
@endsection
