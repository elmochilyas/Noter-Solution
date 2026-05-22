@extends('layouts.portal')

@section('title', __('portal.dashboard_title').' — '.config('app.name'))

@section('content')
<section class="mx-auto max-w-4xl px-4 py-12 md:px-6 lg:px-8">
    <h1 class="text-3xl font-semibold text-ink mb-2">
        {{ __('portal.dashboard_greeting', ['name' => auth('client')->user()->full_name ?: __('portal.dear')]) }}
    </h1>
    <p class="text-stone-500 mb-10">{{ __('portal.dashboard_subtitle') }}</p>

    @php
        $upcoming = auth('client')->user()->bookings()
            ->whereIn('status', ['pending_payment', 'confirmed'])
            ->where('starts_at', '>=', now())
            ->with(['plan'])
            ->orderBy('starts_at')
            ->first();
    @endphp

    @if ($upcoming)
        <div class="rounded-xl border border-stone-200 bg-white p-6 mb-8">
            <h2 class="text-lg font-semibold text-ink mb-4">{{ __('portal.next_booking') }}</h2>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="space-y-1">
                    <p class="font-medium text-ink">{{ $upcoming->plan?->name_translations[app()->getLocale()] ?? $upcoming->plan?->name ?? '-' }}</p>
                    <p class="text-sm text-stone-500">
                        {{ $upcoming->starts_at->locale(app()->getLocale())->isoFormat('dddd D MMMM YYYY [à] HH:mm') }}
                        · {{ __('booking.status.'.$upcoming->status) }}
                        · {{ $upcoming->reference }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <a href="/{{ app()->getLocale() }}/portal/bookings/{{ $upcoming->reference }}"
                       class="rounded-md bg-brass-500 px-4 py-2 text-sm font-medium text-parchment hover:bg-brass-600 transition-fast">
                        {{ __('portal.view') }}
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="rounded-xl border border-stone-200 bg-white p-8 text-center mb-8">
            <p class="text-stone-500 mb-4">{{ __('portal.dashboard_empty') }}</p>
            <a href="/{{ app()->getLocale() }}/book"
               class="inline-flex items-center justify-center rounded-md bg-brass-500 px-6 py-3 text-sm font-medium text-parchment hover:bg-brass-600 transition-fast">
                {{ __('portal.nav_book_appointment') }}
            </a>
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="/{{ app()->getLocale() }}/portal/bookings"
           class="rounded-xl border border-stone-200 bg-white p-5 hover:border-brass-300 transition-fast text-center">
            <p class="text-lg font-semibold text-ink">{{ __('portal.nav_bookings') }}</p>
            <p class="text-sm text-stone-500 mt-1">{{ __('portal.see_all_bookings') }}</p>
        </a>
        <a href="/{{ app()->getLocale() }}/portal/receipts"
           class="rounded-xl border border-stone-200 bg-white p-5 hover:border-brass-300 transition-fast text-center">
            <p class="text-lg font-semibold text-ink">{{ __('portal.nav_receipts') }}</p>
            <p class="text-sm text-stone-500 mt-1">{{ __('portal.download_receipts') }}</p>
        </a>
        <a href="/{{ app()->getLocale() }}/portal/preferences"
           class="rounded-xl border border-stone-200 bg-white p-5 hover:border-brass-300 transition-fast text-center">
            <p class="text-lg font-semibold text-ink">{{ __('portal.nav_preferences') }}</p>
            <p class="text-sm text-stone-500 mt-1">{{ __('portal.manage_preferences') }}</p>
        </a>
    </div>
</section>
@endsection
