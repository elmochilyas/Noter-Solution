@extends('layouts.portal')

@section('title', __('portal.bookings_title').' — '.config('app.name'))

@section('content')
<section class="mx-auto max-w-4xl px-4 py-12 md:px-6 lg:px-8">
    <h1 class="text-3xl font-semibold text-ink mb-8">{{ __('portal.bookings_title') }}</h1>

    @forelse ($bookings as $booking)
        <a href="/{{ app()->getLocale() }}/portal/bookings/{{ $booking->reference }}"
           class="block rounded-xl border border-stone-200 bg-white p-5 mb-3 hover:border-brass-300 transition-fast">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <p class="font-medium text-ink">{{ $booking->plan?->name_translations[app()->getLocale()] ?? $booking->plan?->name ?? '-' }}</p>
                    <p class="text-sm text-stone-500 mt-0.5">
                        {{ $booking->starts_at->locale(app()->getLocale())->isoFormat('dddd D MMMM YYYY [à] HH:mm') }}
                        · {{ $booking->reference }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                        @if ($booking->status === 'confirmed') bg-success/10 text-success
                        @elseif ($booking->status === 'pending_payment') bg-warning/10 text-warning
                        @elseif ($booking->status === 'cancelled') bg-danger/10 text-danger
                        @elseif ($booking->status === 'completed') bg-info/10 text-info
                        @else bg-stone/10 text-stone-500
                        @endif">
                        {{ __('booking.status.'.$booking->status) }}
                    </span>
                    <span class="text-sm text-stone-400">{{ $booking->format === 'online' ? __('booking.online') : __('booking.in_office') }}</span>
                </div>
            </div>
        </a>
    @empty
        <div class="rounded-xl border border-stone-200 bg-white p-8 text-center">
            <p class="text-stone-500 mb-4">{{ __('portal.no_bookings') }}</p>
            <a href="/{{ app()->getLocale() }}/book"
               class="inline-flex items-center justify-center rounded-md bg-brass-500 px-6 py-3 text-sm font-medium text-parchment hover:bg-brass-600 transition-fast">
                {{ __('portal.nav_book_appointment') }}
            </a>
        </div>
    @endforelse

    @if ($bookings->hasPages())
        <div class="mt-8">
            {{ $bookings->links() }}
        </div>
    @endif
</section>
@endsection
