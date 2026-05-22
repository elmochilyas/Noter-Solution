@extends('layouts.portal')

@section('title', __('portal.booking_detail_title', ['reference' => $booking->reference]).' — '.config('app.name'))

@section('content')
<section class="mx-auto max-w-4xl px-4 py-12 md:px-6 lg:px-8">
    <nav class="text-sm text-stone-400 mb-6">
        <a href="{{ route('portal.bookings.index', ['locale' => app()->getLocale()]) }}" class="hover:text-ink transition-fast">{{ __('portal.nav_bookings') }}</a>
        <span class="mx-2">/</span>
        <span class="text-ink">{{ $booking->reference }}</span>
    </nav>

    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-semibold text-ink">{{ $booking->plan?->name_translations[app()->getLocale()] ?? $booking->plan?->name ?? '-' }}</h1>
            <p class="text-stone-500 mt-1">
                {{ $booking->starts_at->locale(app()->getLocale())->isoFormat('dddd D MMMM YYYY [à] HH:mm') }}
                · {{ $booking->format === 'online' ? __('booking.online') : __('booking.in_office') }}
            </p>
        </div>
        <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium
            @if ($booking->status === 'confirmed') bg-success/10 text-success
            @elseif ($booking->status === 'pending_payment') bg-warning/10 text-warning
            @elseif ($booking->status === 'cancelled') bg-danger/10 text-danger
            @elseif ($booking->status === 'completed') bg-info/10 text-info
            @else bg-stone/10 text-stone-500
            @endif">
            {{ __('booking.status.'.$booking->status) }}
        </span>
    </div>

    @if ($showJoinLink && $booking->meeting_url)
        <div class="rounded-xl border border-success/30 bg-success/5 p-5 mb-6">
            <p class="font-medium text-success mb-2">{{ __('portal.join_meeting') }}</p>
            <a href="{{ $booking->meeting_url }}" target="_blank"
               class="inline-flex items-center rounded-md bg-success px-4 py-2 text-sm font-medium text-white hover:bg-success/90 transition-fast">
                {{ __('portal.join_button') }}
            </a>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        @if ($booking->format === 'in_office')
            <div class="rounded-xl border border-stone-200 bg-white p-5">
                <h2 class="text-sm font-semibold text-stone-500 uppercase tracking-wide mb-2">{{ __('portal.office_address_title') }}</h2>
                <p class="text-ink">{{ __('notifications.booking_confirmed.office_address') }}</p>
            </div>
        @endif

        <div class="rounded-xl border border-stone-200 bg-white p-5">
            <h2 class="text-sm font-semibold text-stone-500 uppercase tracking-wide mb-2">{{ __('portal.reference') }}</h2>
            <p class="text-ink font-mono">{{ $booking->reference }}</p>
        </div>

        <div class="rounded-xl border border-stone-200 bg-white p-5">
            <h2 class="text-sm font-semibold text-stone-500 uppercase tracking-wide mb-2">{{ __('portal.description') }}</h2>
            <p class="text-ink">{{ $booking->description ?: '-' }}</p>
        </div>
    </div>

    @if ($booking->payment)
        <div class="rounded-xl border border-stone-200 bg-white p-5 mb-6">
            <h2 class="text-sm font-semibold text-stone-500 uppercase tracking-wide mb-3">{{ __('portal.payment_section') }}</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div>
                    <span class="text-stone-400">{{ __('portal.amount') }}</span>
                    <p class="font-medium text-ink">{{ number_format($booking->payment->amount_centimes / 100, 2, ',', ' ') }} MAD</p>
                </div>
                <div>
                    <span class="text-stone-400">{{ __('portal.method') }}</span>
                    <p class="font-medium text-ink">{{ $booking->payment->gateway }}</p>
                </div>
                <div>
                    <span class="text-stone-400">{{ __('portal.status') }}</span>
                    <p class="font-medium text-ink">{{ $booking->payment->status }}</p>
                </div>
                @if ($booking->receipt)
                    <div>
                        <span class="text-stone-400">{{ __('portal.receipt') }}</span>
                        <a href="{{ route('portal.bookings.receipt.download', ['locale' => app()->getLocale(), 'reference' => $booking->reference, 'receipt' => $booking->receipt->id]) }}"
                           class="font-medium text-brass-600 hover:text-brass-700 transition-fast">
                            {{ __('portal.download') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if ($booking->documents->isNotEmpty())
        <div class="rounded-xl border border-stone-200 bg-white p-5 mb-6">
            <h2 class="text-sm font-semibold text-stone-500 uppercase tracking-wide mb-3">{{ __('portal.documents') }}</h2>
            <ul class="divide-y divide-stone-100">
                @foreach ($booking->documents as $doc)
                    <li class="flex items-center justify-between py-2">
                        <span class="text-sm text-ink">{{ $doc->original_filename }}</span>
                        @if ($doc->scan_status === 'pending')
                            <span class="text-xs text-stone-400">{{ __('portal.scanning') }}</span>
                        @elseif ($doc->scan_status === 'infected')
                            <span class="text-xs text-danger">{{ __('portal.infected') }}</span>
                        @else
                            <a href="{{ route('portal.bookings.documents.download', ['locale' => app()->getLocale(), 'reference' => $booking->reference, 'document' => $doc->id]) }}"
                               class="text-sm text-brass-600 hover:text-brass-700 transition-fast">
                                {{ __('portal.download') }}
                            </a>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex flex-wrap gap-3">
        @if ($canCancel)
            <a href="{{ route('portal.bookings.cancel.confirm', ['locale' => app()->getLocale(), 'reference' => $booking->reference]) }}"
               class="rounded-md border border-danger/30 px-4 py-2 text-sm font-medium text-danger hover:bg-danger/5 transition-fast">
                {{ __('portal.cancel_booking') }}
            </a>
        @endif

        @if ($canReschedule)
            <a href="{{ route('portal.bookings.reschedule.edit', ['locale' => app()->getLocale(), 'reference' => $booking->reference]) }}"
               class="rounded-md border border-stone-300 px-4 py-2 text-sm font-medium text-ink hover:bg-stone-50 transition-fast">
                {{ __('portal.reschedule') }}
            </a>
        @endif
    </div>
</section>
@endsection
