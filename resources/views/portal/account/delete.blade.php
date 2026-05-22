@extends('layouts.portal')

@section('title', __('portal.delete_account_title').' — '.config('app.name'))

@section('content')
<section class="mx-auto max-w-2xl px-4 py-12 md:px-6 lg:px-8">
    <h1 class="text-3xl font-semibold text-danger mb-4">{{ __('portal.delete_account_title') }}</h1>

    <div class="rounded-xl border border-danger/20 bg-danger/5 p-6 mb-8">
        <p class="text-sm text-stone-700 mb-4">{{ __('portal.deletion_warning') }}</p>
        <ul class="list-disc list-inside text-sm text-stone-600 space-y-1 mb-4">
            <li>{{ __('portal.deletion_effect_1') }}</li>
            <li>{{ __('portal.deletion_effect_2') }}</li>
            <li>{{ __('portal.deletion_effect_3') }}</li>
        </ul>
        <p class="text-sm text-stone-500">{{ __('portal.deletion_irreversible') }}</p>
    </div>

    @if ($errors->any())
        <div class="rounded-md bg-danger/10 border border-danger/30 p-4 mb-6">
            @foreach ($errors->all() as $error)
                <p class="text-sm text-danger">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="/{{ app()->getLocale() }}/portal/account/delete" class="space-y-4">
        @csrf

        <div>
            <label for="confirmation" class="mb-1.5 block text-sm font-medium text-stone-700">
                {{ app()->getLocale() === 'ar' ? __('portal.type_to_confirm_ar') : __('portal.type_to_confirm') }}
            </label>
            <input type="text" name="confirmation" id="confirmation" required autocomplete="off"
                   placeholder="{{ app()->getLocale() === 'ar' ? 'حذف' : 'SUPPRIMER' }}"
                   class="block h-11 w-full rounded-md border border-stone-200 bg-white px-3 text-base focus:ring-2 focus:ring-danger focus:border-danger">
        </div>

        <button type="submit"
                class="inline-flex w-full items-center justify-center rounded-md bg-danger px-6 py-3 text-sm font-medium text-white hover:bg-danger/90 transition-fast">
            {{ __('portal.confirm_delete') }}
        </button>

        <a href="/{{ app()->getLocale() }}/portal/preferences"
           class="block text-center text-sm text-stone-500 hover:text-ink transition-fast">
            {{ __('portal.cancel') }}
        </a>
    </form>
</section>
@endsection
