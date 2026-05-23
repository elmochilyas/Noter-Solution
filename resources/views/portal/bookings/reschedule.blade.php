@extends('layouts.portal')

@section('title', __('portal.reschedule_title').' — '.config('app.name'))

@section('content')
<section class="mx-auto max-w-2xl px-4 py-10 md:px-6 lg:px-8">
    <nav class="text-sm text-stone-500 mb-8 flex items-center gap-2">
        <a href="{{ route('portal.bookings.index', ['locale' => app()->getLocale()]) }}" class="hover:text-brass-500 transition-fast">{{ __('portal.nav_bookings') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-stone-300 rtl:scale-x-[-1]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('portal.bookings.show', ['locale' => app()->getLocale(), 'reference' => $booking->reference]) }}" class="hover:text-brass-500 transition-fast">{{ $booking->reference }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-stone-300 rtl:scale-x-[-1]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-ink font-medium">{{ __('portal.reschedule') }}</span>
    </nav>

    <h1 class="text-2xl md:text-3xl font-semibold text-ink mb-2 reveal-up">{{ __('portal.reschedule_title') }}</h1>
    <p class="text-stone-500 text-sm mb-8 reveal-up" style="animation-delay:40ms">{{ __('portal.reschedule_subtitle', ['reference' => $booking->reference]) }}</p>

    <div class="card-premium p-5 mb-8 reveal-up" style="animation-delay:80ms">
        <h2 class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-2">{{ __('portal.reschedule_current') }}</h2>
        <p class="text-ink text-sm">
            {{ $booking->starts_at->locale(app()->getLocale())->isoFormat('dddd D MMMM YYYY [à] HH:mm') }}
            <span class="mx-1.5 text-stone-300">·</span>
            {{ $booking->format === 'online' ? __('booking.online') : __('booking.in_office') }}
        </p>
    </div>

    @if (empty($availableSlots))
        <div class="rounded-2xl border border-warning/20 bg-warning-bg p-8 text-center reveal-up" style="animation-delay:100ms">
            <div class="size-12 rounded-2xl bg-warning/10 border border-warning/20 flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="text-warning font-semibold mb-2">{{ __('portal.reschedule_no_slots') }}</p>
            <p class="text-stone-500 text-sm">{{ __('portal.reschedule_no_slots_help') }}</p>
        </div>
    @else
        <form method="POST" action="{{ route('portal.bookings.reschedule.update', ['locale' => app()->getLocale(), 'reference' => $booking->reference]) }}" class="reveal-up" style="animation-delay:100ms">
            @csrf

            <div class="space-y-6 mb-8">
                @php
                    $grouped = [];
                    foreach ($availableSlots as $slot) {
                        $dateKey = $slot->startsAt->locale(app()->getLocale())->isoFormat('dddd D MMMM YYYY');
                        $grouped[$dateKey][] = $slot;
                    }
                @endphp

                @foreach ($grouped as $date => $slots)
                    <div>
                        <h3 class="text-sm font-semibold text-stone-500 mb-3 capitalize">{{ $date }}</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($slots as $slot)
                                <label class="cursor-pointer">
                                    <input type="radio" name="starts_at" value="{{ $slot->startsAt->toIso8601String() }}"
                                           class="peer sr-only" required>
                                    <span class="inline-flex items-center rounded-xl border border-stone-200 bg-white px-4 py-2 text-sm font-medium text-ink
                                                 peer-checked:border-brass-500 peer-checked:bg-brass-50 peer-checked:text-brass-700
                                                 hover:border-brass-300 hover:bg-brass-50/50 transition-fast cursor-pointer select-none">
                                        {{ $slot->startsAt->format('H:i') }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            @error('starts_at')
                <p class="text-sm text-danger mb-4">{{ $message }}</p>
            @enderror

            <div class="flex items-center gap-3">
                <button type="submit" class="btn-brass px-6 py-3 rounded-xl text-sm font-semibold scale-on-press">
                    {{ __('portal.reschedule_confirm') }}
                </button>
                <a href="{{ route('portal.bookings.show', ['locale' => app()->getLocale(), 'reference' => $booking->reference]) }}"
                   class="text-sm text-stone-500 hover:text-ink transition-fast">
                    {{ __('portal.cancel') }}
                </a>
            </div>
        </form>
    @endif
</section>
@endsection
