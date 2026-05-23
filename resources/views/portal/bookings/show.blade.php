@extends('layouts.portal')

@section('title', __('portal.booking_detail_title', ['reference' => $booking->reference]).' — '.config('app.name'))

@section('content')
<section class="mx-auto max-w-4xl px-4 py-10 md:px-6 lg:px-8">
    <nav class="text-sm text-stone-500 mb-8 flex items-center gap-2">
        <a href="{{ route('portal.bookings.index', ['locale' => app()->getLocale()]) }}" class="hover:text-brass-500 transition-fast">{{ __('portal.nav_bookings') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-stone-300 rtl:scale-x-[-1]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-ink font-medium">{{ $booking->reference }}</span>
    </nav>

    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-8 reveal-up">
        <div>
            <h1 class="text-2xl md:text-3xl font-semibold text-ink">{{ locale_string($booking->plan?->name_translations ?? [], app()->getLocale()) ?: ($booking->plan?->name ?? '-') }}</h1>
            <p class="text-stone-500 mt-1 text-sm">
                {{ $booking->starts_at->locale(app()->getLocale())->isoFormat('dddd D MMMM YYYY [à] HH:mm') }}
                <span class="mx-1.5 text-stone-300">·</span>
                {{ $booking->format === 'online' ? __('booking.online') : __('booking.in_office') }}
            </p>
        </div>
        <span @class([
            'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold shrink-0',
            'bg-success-bg text-success' => $booking->status === 'confirmed',
            'bg-warning-bg text-warning' => $booking->status === 'pending_payment',
            'bg-danger-bg text-danger'   => $booking->status === 'cancelled',
            'bg-info-bg text-info'       => $booking->status === 'completed',
            'bg-stone-100 text-stone-500'=> !in_array($booking->status, ['confirmed','pending_payment','cancelled','completed']),
        ])>
            {{ __('booking.status.'.$booking->status) }}
        </span>
    </div>

    @if ($showJoinLink && $booking->meeting_url)
        <div class="rounded-2xl border border-success/20 bg-success-bg p-5 mb-6 reveal-up" style="animation-delay:60ms">
            <p class="font-medium text-success mb-3">{{ __('portal.join_meeting') }}</p>
            <a href="{{ $booking->meeting_url }}" target="_blank"
               class="inline-flex items-center gap-2 rounded-xl bg-success px-5 py-2.5 text-sm font-semibold text-white hover:bg-success/90 transition-fast scale-on-press">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.069A1 1 0 0121 8.868v6.264a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/></svg>
                {{ __('portal.join_button') }}
            </a>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 reveal-up" style="animation-delay:80ms">
        @if ($booking->format === 'in_office')
            <div class="card-premium p-5">
                <h2 class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-2">{{ __('portal.office_address_title') }}</h2>
                <p class="text-ink text-sm">{{ __('notifications.booking_confirmed.office_address') }}</p>
            </div>
        @endif

        <div class="card-premium p-5">
            <h2 class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-2">{{ __('portal.reference') }}</h2>
            <p class="text-ink font-mono text-sm">{{ $booking->reference }}</p>
        </div>

        <div class="card-premium p-5">
            <h2 class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-2">{{ __('portal.description') }}</h2>
            <p class="text-ink text-sm">{{ $booking->description ?: '-' }}</p>
        </div>
    </div>

    @if ($booking->payment)
        <div class="card-premium p-5 mb-6 reveal-up" style="animation-delay:100ms">
            <h2 class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-4">{{ __('portal.payment_section') }}</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div>
                    <span class="text-stone-400 text-xs block mb-1">{{ __('portal.amount') }}</span>
                    <p class="font-semibold text-ink">{{ number_format($booking->payment->amount_centimes / 100, 2, ',', ' ') }} {{ __('portal.currency') }}</p>
                </div>
                <div>
                    <span class="text-stone-400 text-xs block mb-1">{{ __('portal.method') }}</span>
                    <p class="font-semibold text-ink">{{ \App\Enums\PaymentGatewayName::tryFrom($booking->payment->gateway)?->label() ?? $booking->payment->gateway }}</p>
                </div>
                <div>
                    <span class="text-stone-400 text-xs block mb-1">{{ __('portal.status') }}</span>
                    <p class="font-semibold text-ink">{{ \App\Enums\PaymentStatus::tryFrom($booking->payment->status)?->label() ?? $booking->payment->status }}</p>
                </div>
                @if ($booking->receipt)
                    <div>
                        <span class="text-stone-400 text-xs block mb-1">{{ __('portal.receipt') }}</span>
                        <a href="{{ route('portal.bookings.receipt.download', ['locale' => app()->getLocale(), 'reference' => $booking->reference, 'receipt' => $booking->receipt->id]) }}"
                           class="font-semibold text-brass-600 hover:text-brass-500 transition-fast inline-flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            {{ __('portal.download') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if ($booking->documents->isNotEmpty())
        <div class="card-premium p-5 mb-6 reveal-up" style="animation-delay:120ms">
            <h2 class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-4">{{ __('portal.documents') }}</h2>
            <ul class="divide-y divide-stone-50">
                @foreach ($booking->documents as $doc)
                    <li class="flex items-center justify-between py-2.5">
                        <span class="text-sm text-ink">{{ $doc->original_filename }}</span>
                        @if ($doc->scan_status === 'pending')
                            <span class="text-xs text-stone-400 bg-stone-50 px-2 py-1 rounded-full">{{ __('portal.scanning') }}</span>
                        @elseif ($doc->scan_status === 'infected')
                            <span class="text-xs text-danger bg-danger-bg px-2 py-1 rounded-full">{{ __('portal.infected') }}</span>
                        @else
                            <a href="{{ route('portal.bookings.documents.download', ['locale' => app()->getLocale(), 'reference' => $booking->reference, 'document' => $doc->id]) }}"
                               class="text-sm text-brass-600 hover:text-brass-500 transition-fast font-medium">
                                {{ __('portal.download') }}
                            </a>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex flex-wrap gap-3 reveal-up" style="animation-delay:140ms">
        @if ($canCancel)
            <a href="{{ route('portal.bookings.cancel.confirm', ['locale' => app()->getLocale(), 'reference' => $booking->reference]) }}"
               class="inline-flex items-center gap-2 rounded-xl border border-danger/30 bg-danger-bg/50 px-5 py-2.5 text-sm font-semibold text-danger hover:bg-danger-bg transition-fast scale-on-press">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                {{ __('portal.cancel_booking') }}
            </a>
        @endif

        @if ($canReschedule)
            <a href="{{ route('portal.bookings.reschedule.edit', ['locale' => app()->getLocale(), 'reference' => $booking->reference]) }}"
               class="btn-ghost inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold scale-on-press">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                {{ __('portal.reschedule') }}
            </a>
        @endif
    </div>
</section>
@endsection
