@extends('layouts.portal')

@section('title', __('portal.preferences_title').' — '.config('app.name'))

@section('content')
<section class="mx-auto max-w-2xl px-4 py-10 md:px-6 lg:px-8">
    <h1 class="text-2xl md:text-3xl font-semibold text-ink mb-8 reveal-up">{{ __('portal.preferences_title') }}</h1>

    <form method="POST" action="/{{ app()->getLocale() }}/portal/preferences" class="space-y-6">
        @csrf

        <div class="card-premium p-6 space-y-5 reveal-up" style="animation-delay:60ms">
            <h2 class="text-xs font-semibold text-stone-400 uppercase tracking-wider">{{ __('portal.personal_info') }}</h2>

            <div>
                <label for="full_name" class="mb-1.5 block text-sm font-medium text-stone-700">{{ __('booking.full_name') }}</label>
                <input type="text" name="full_name" id="full_name" value="{{ old('full_name', $client->full_name) }}"
                       class="block h-11 w-full rounded-xl border border-stone-200 bg-white px-4 text-sm focus:ring-2 focus:ring-brass-400/30 focus:border-brass-400 outline-none transition-fast @error('full_name') border-danger @enderror">
                @error('full_name') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="phone" class="mb-1.5 block text-sm font-medium text-stone-700">{{ __('booking.phone') }}</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone', $client->phone) }}"
                       class="block h-11 w-full rounded-xl border border-stone-200 bg-white px-4 text-sm focus:ring-2 focus:ring-brass-400/30 focus:border-brass-400 outline-none transition-fast">
                @error('phone') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="card-premium p-6 space-y-5 reveal-up" style="animation-delay:100ms">
            <h2 class="text-xs font-semibold text-stone-400 uppercase tracking-wider">{{ __('portal.preferences') }}</h2>

            <div>
                <label for="preferred_locale" class="mb-1.5 block text-sm font-medium text-stone-700">{{ __('portal.language') }}</label>
                <select name="preferred_locale" id="preferred_locale"
                        class="block h-11 w-full rounded-xl border border-stone-200 bg-white px-4 text-sm focus:ring-2 focus:ring-brass-400/30 focus:border-brass-400 outline-none transition-fast">
                    <option value="fr" {{ $client->preferred_locale === 'fr' ? 'selected' : '' }}>{{ __('common.french') }}</option>
                    <option value="ar" {{ $client->preferred_locale === 'ar' ? 'selected' : '' }}>{{ __('common.arabic') }}</option>
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-stone-700">{{ __('booking.preferred_channel') }}</label>
                <div class="flex flex-wrap gap-3">
                    @foreach (['email', 'sms', 'whatsapp'] as $channel)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="preferred_channel" value="{{ $channel }}"
                                   {{ $client->preferred_channel === $channel ? 'checked' : '' }}
                                   class="text-brass-500 focus:ring-brass-400 focus:ring-offset-0">
                            <span class="text-sm text-stone-700">{{ __('booking.channels.' . $channel) }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <button type="submit" class="btn-brass w-full h-12 rounded-xl text-sm font-semibold scale-on-press reveal-up" style="animation-delay:120ms">
            {{ __('portal.save_preferences') }}
        </button>
    </form>

    <div class="mt-12 pt-8 border-t border-stone-100 reveal-up" style="animation-delay:140ms">
        <h2 class="text-sm font-semibold text-danger uppercase tracking-wider mb-2">{{ __('portal.delete_account_title') }}</h2>
        <p class="text-sm text-stone-500 mb-4">{{ __('portal.delete_account_description') }}</p>
        <a href="/{{ app()->getLocale() }}/portal/account/delete"
           class="inline-flex items-center gap-2 rounded-xl border border-danger/30 bg-danger-bg/50 px-4 py-2.5 text-sm font-semibold text-danger hover:bg-danger-bg transition-fast scale-on-press">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            {{ __('portal.delete_account_button') }}
        </a>
    </div>
</section>
@endsection
