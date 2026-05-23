@extends('layouts.portal')

@section('title', __('portal.bookings_title').' — '.config('app.name'))

@section('content')
<section class="mx-auto max-w-5xl px-4 py-10 md:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-8 reveal-up">
        <div>
            <h1 class="text-2xl md:text-3xl font-semibold text-ink">{{ __('portal.bookings_title') }}</h1>
            <p class="text-sm text-stone-500 mt-1">{{ __('portal.see_all_bookings') }}</p>
        </div>
        <a href="/{{ app()->getLocale() }}/book" class="btn-brass text-sm px-5 py-2.5 rounded-xl scale-on-press">
            + {{ __('portal.nav_book_appointment') }}
        </a>
    </div>

    <div class="space-y-3 reveal-up" style="animation-delay:60ms">
        @forelse ($bookings as $booking)
            <a href="/{{ app()->getLocale() }}/portal/bookings/{{ $booking->reference }}"
               class="card-premium p-5 md:p-6 block group">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-start gap-4">
                        <div class="shrink-0 size-10 rounded-xl bg-brass-50 border border-brass-100 flex items-center justify-center group-hover:bg-brass-100 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 text-brass-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-ink">{{ locale_string($booking->plan?->name_translations ?? [], app()->getLocale()) ?: ($booking->plan?->name ?? '-') }}</p>
                            <p class="text-sm text-stone-500 mt-0.5">
                                {{ $booking->starts_at->locale(app()->getLocale())->isoFormat('dddd D MMMM YYYY [à] HH:mm') }}
                                <span class="mx-1.5 text-stone-300">·</span>
                                <span class="font-mono text-xs">{{ $booking->reference }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 sm:shrink-0">
                        <span @class([
                            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold',
                            'bg-success-bg text-success' => $booking->status === 'confirmed',
                            'bg-warning-bg text-warning' => $booking->status === 'pending_payment',
                            'bg-danger-bg text-danger'   => $booking->status === 'cancelled',
                            'bg-info-bg text-info'       => $booking->status === 'completed',
                            'bg-stone-100 text-stone-500'=> !in_array($booking->status, ['confirmed','pending_payment','cancelled','completed']),
                        ])>
                            {{ __('booking.status.'.$booking->status) }}
                        </span>
                        <span class="text-xs text-stone-400">{{ $booking->format === 'online' ? __('booking.online') : __('booking.in_office') }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-stone-300 rtl:scale-x-[-1] group-hover:text-brass-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>
        @empty
            <div class="rounded-2xl border border-stone-200 bg-white p-12 text-center shadow-sm">
                <div class="mx-auto size-14 rounded-xl bg-stone-50 border border-stone-100 flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-stone-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                    </svg>
                </div>
                <p class="text-stone-500 text-sm mb-5">{{ __('portal.no_bookings') }}</p>
                <a href="/{{ app()->getLocale() }}/book"
                   class="btn-brass text-sm px-6 py-2.5 rounded-xl scale-on-press">
                    {{ __('portal.nav_book_appointment') }}
                </a>
            </div>
        @endforelse
    </div>

    @if ($bookings->hasPages())
        <div class="mt-8 reveal-up" style="animation-delay:120ms">
            {{ $bookings->links() }}
        </div>
    @endif
</section>
@endsection
