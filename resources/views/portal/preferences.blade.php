@extends('layouts.portal')

@section('title', __('portal.preferences_title').' — '.config('app.name'))

@section('content')
<section class="mx-auto max-w-2xl px-4 py-12 md:px-6 lg:px-8">
    <h1 class="text-3xl font-semibold text-ink mb-8">{{ __('portal.preferences_title') }}</h1>

    <form method="POST" action="/{{ app()->getLocale() }}/portal/preferences" class="space-y-6">
        @csrf

        <div class="rounded-xl border border-stone-200 bg-white p-6 space-y-5">
            <h2 class="text-sm font-semibold text-stone-500 uppercase tracking-wide">{{ __('portal.personal_info') }}</h2>

            <div>
                <label for="full_name" class="mb-1.5 block text-sm font-medium text-stone-700">{{ __('booking.full_name') }}</label>
                <input type="text" name="full_name" id="full_name" value="{{ old('full_name', $client->full_name) }}"
                       class="block h-11 w-full rounded-md border border-stone-200 bg-white px-3 text-base focus:ring-2 focus:ring-brass-500 focus:border-brass-500 @error('full_name') border-danger @enderror">
                @error('full_name') <p class="mt-1 text-sm text-danger">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="phone" class="mb-1.5 block text-sm font-medium text-stone-700">{{ __('booking.phone') }}</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone', $client->phone) }}"
                       class="block h-11 w-full rounded-md border border-stone-200 bg-white px-3 text-base focus:ring-2 focus:ring-brass-500 focus:border-brass-500">
                @error('phone') <p class="mt-1 text-sm text-danger">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="rounded-xl border border-stone-200 bg-white p-6 space-y-5">
            <h2 class="text-sm font-semibold text-stone-500 uppercase tracking-wide">{{ __('portal.preferences') }}</h2>

            <div>
                <label for="preferred_locale" class="mb-1.5 block text-sm font-medium text-stone-700">{{ __('portal.language') }}</label>
                <select name="preferred_locale" id="preferred_locale"
                        class="block h-11 w-full rounded-md border border-stone-200 bg-white px-3 text-base focus:ring-2 focus:ring-brass-500 focus:border-brass-500">
                    <option value="fr" {{ $client->preferred_locale === 'fr' ? 'selected' : '' }}>Français</option>
                    <option value="ar" {{ $client->preferred_locale === 'ar' ? 'selected' : '' }}>العربية</option>
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-stone-700">{{ __('booking.preferred_channel') }}</label>
                <div class="flex flex-wrap gap-4">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="radio" name="preferred_channel" value="email"
                               {{ $client->preferred_channel === 'email' ? 'checked' : '' }}
                               class="text-brass-500 focus:ring-brass-500">
                        {{ __('portal.channel_email') }}
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="radio" name="preferred_channel" value="sms"
                               {{ $client->preferred_channel === 'sms' ? 'checked' : '' }}
                               class="text-brass-500 focus:ring-brass-500">
                        SMS
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="radio" name="preferred_channel" value="whatsapp"
                               {{ $client->preferred_channel === 'whatsapp' ? 'checked' : '' }}
                               class="text-brass-500 focus:ring-brass-500">
                        WhatsApp
                    </label>
                </div>
            </div>
        </div>

        <button type="submit"
                class="inline-flex w-full items-center justify-center rounded-md bg-brass-500 px-6 py-3 text-sm font-medium text-parchment hover:bg-brass-600 transition-fast">
            {{ __('portal.save_preferences') }}
        </button>
    </form>

    <div class="mt-12 pt-8 border-t border-stone-200">
        <h2 class="text-sm font-semibold text-danger uppercase tracking-wide mb-2">{{ __('portal.delete_account_title') }}</h2>
        <p class="text-sm text-stone-500 mb-4">{{ __('portal.delete_account_description') }}</p>
        <a href="/{{ app()->getLocale() }}/portal/account/delete"
           class="inline-flex items-center rounded-md border border-danger/30 px-4 py-2 text-sm font-medium text-danger hover:bg-danger/5 transition-fast">
            {{ __('portal.delete_account_button') }}
        </a>
    </div>
</section>
@endsection
