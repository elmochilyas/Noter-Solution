@extends('layouts.portal')

@section('title', __('portal.cancel_title').' — '.config('app.name'))

@section('content')
<section class="mx-auto max-w-2xl px-4 py-10 md:px-6 lg:px-8">
    <nav class="text-sm text-stone-500 mb-8 flex items-center gap-2">
        <a href="{{ route('portal.bookings.index', ['locale' => app()->getLocale()]) }}" class="hover:text-brass-500 transition-fast">{{ __('portal.nav_bookings') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-stone-300 rtl:scale-x-[-1]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <a href="/{{ app()->getLocale() }}/portal/bookings/{{ $booking->reference }}" class="hover:text-brass-500 transition-fast">{{ $booking->reference }}</a>
    </nav>

    <div class="flex items-center gap-3 mb-8 reveal-up">
        <div class="size-12 rounded-2xl bg-danger-bg border border-danger/20 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-danger" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
        </div>
        <h1 class="text-2xl font-semibold text-ink">{{ __('portal.cancel_title') }}</h1>
    </div>

    <div class="card-premium p-6 mb-6 reveal-up" style="animation-delay:60ms">
        <h2 class="font-semibold text-ink mb-1">{{ locale_string($booking->plan?->name_translations ?? [], app()->getLocale()) ?: ($booking->plan?->name ?? '-') }}</h2>
        <p class="text-sm text-stone-500 mb-5">
            {{ $booking->starts_at->locale(app()->getLocale())->isoFormat('dddd D MMMM YYYY [à] HH:mm') }}
            <span class="mx-1.5 text-stone-300">·</span>
            <span class="font-mono text-xs">{{ $booking->reference }}</span>
        </p>

        <div class="rounded-xl bg-stone-50 border border-stone-100 p-4 text-sm">
            @if ($refundPercentage > 0)
                <p class="text-stone-600">
                    {{ __('portal.refund_policy', ['percentage' => $refundPercentage]) }}
                </p>
                <p class="font-semibold text-ink mt-2">
                    {{ __('portal.refund_amount', ['amount' => number_format($refundAmount / 100, 2, ',', ' ') . ' ' . __('portal.currency')]) }}
                </p>
            @else
                <p class="text-stone-500">{{ __('portal.no_refund') }}</p>
            @endif
        </div>
    </div>

    <form method="POST" action="/{{ app()->getLocale() }}/portal/bookings/{{ $booking->reference }}/cancel" class="reveal-up" style="animation-delay:80ms">
        @csrf

        <div class="mb-6">
            <label for="reason" class="mb-1.5 block text-sm font-medium text-stone-700">{{ __('portal.cancel_reason') }}</label>
            <textarea name="reason" id="reason" rows="3"
                      class="block w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm focus:ring-2 focus:ring-brass-400/30 focus:border-brass-400 outline-none transition-fast resize-none"></textarea>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <button type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-danger px-6 py-3 text-sm font-semibold text-white hover:bg-danger/90 transition-fast scale-on-press">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                {{ __('portal.confirm_cancel') }}
            </button>
            <a href="/{{ app()->getLocale() }}/portal/bookings/{{ $booking->reference }}"
               class="btn-ghost inline-flex items-center justify-center gap-2 rounded-xl px-6 py-3 text-sm font-semibold scale-on-press">
                {{ __('portal.keep_booking') }}
            </a>
        </div>
    </form>
</section>
@endsection
