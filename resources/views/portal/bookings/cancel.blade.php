@extends('layouts.portal')

@section('title', __('portal.cancel_title').' — '.config('app.name'))

@section('content')
<section class="mx-auto max-w-2xl px-4 py-12 md:px-6 lg:px-8">
    <h1 class="text-3xl font-semibold text-danger mb-4">{{ __('portal.cancel_title') }}</h1>

    <div class="rounded-xl border border-stone-200 bg-white p-6 mb-6">
        <h2 class="font-medium text-ink mb-2">{{ $booking->plan?->name_translations[app()->getLocale()] ?? $booking->plan?->name ?? '-' }}</h2>
        <p class="text-sm text-stone-500 mb-4">
            {{ $booking->starts_at->locale(app()->getLocale())->isoFormat('dddd D MMMM YYYY [à] HH:mm') }}
            · {{ $booking->reference }}
        </p>

        <div class="rounded-md bg-stone-50 p-4 text-sm">
            @if ($refundPercentage > 0)
                <p class="text-stone-700">
                    {{ __('portal.refund_policy', ['percentage' => $refundPercentage]) }}
                </p>
                <p class="font-medium text-ink mt-2">
                    {{ __('portal.refund_amount', ['amount' => number_format($refundAmount / 100, 2, ',', ' ')]) }} MAD
                </p>
            @else
                <p class="text-stone-500">{{ __('portal.no_refund') }}</p>
            @endif
        </div>
    </div>

    <form method="POST" action="/{{ app()->getLocale() }}/portal/bookings/{{ $booking->reference }}/cancel">
        @csrf

        <div class="mb-6">
            <label for="reason" class="mb-1.5 block text-sm font-medium text-stone-700">{{ __('portal.cancel_reason') }}</label>
            <textarea name="reason" id="reason" rows="3"
                      class="block w-full rounded-md border border-stone-200 bg-white px-3 py-2 text-base focus:ring-2 focus:ring-brass-500 focus:border-brass-500"></textarea>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <button type="submit"
                    class="inline-flex items-center justify-center rounded-md bg-danger px-6 py-3 text-sm font-medium text-white hover:bg-danger/90 transition-fast">
                {{ __('portal.confirm_cancel') }}
            </button>
            <a href="/{{ app()->getLocale() }}/portal/bookings/{{ $booking->reference }}"
               class="inline-flex items-center justify-center rounded-md border border-stone-300 px-6 py-3 text-sm font-medium text-ink hover:bg-stone-50 transition-fast">
                {{ __('portal.keep_booking') }}
            </a>
        </div>
    </form>
</section>
@endsection
