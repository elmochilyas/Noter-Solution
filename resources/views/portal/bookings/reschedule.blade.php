@extends('layouts.portal')

@section('title', __('portal.reschedule_title').' — '.config('app.name'))

@section('content')
<section class="mx-auto max-w-2xl px-4 py-12 md:px-6 lg:px-8">
    <nav class="text-sm text-stone-400 mb-6">
        <a href="{{ route('portal.bookings.index', ['locale' => app()->getLocale()]) }}" class="hover:text-ink transition-fast">{{ __('portal.nav_bookings') }}</a>
        <span class="mx-2">/</span>
        <a href="{{ route('portal.bookings.show', ['locale' => app()->getLocale(), 'reference' => $booking->reference]) }}" class="hover:text-ink transition-fast">{{ $booking->reference }}</a>
        <span class="mx-2">/</span>
        <span class="text-ink">{{ __('portal.reschedule') }}</span>
    </nav>

    <h1 class="text-3xl font-semibold text-ink mb-2">{{ __('portal.reschedule_title') }}</h1>
    <p class="text-stone-500 mb-8">{{ __('portal.reschedule_subtitle', ['reference' => $booking->reference]) }}</p>

    <div class="rounded-xl border border-stone-200 bg-white p-5 mb-8">
        <h2 class="text-sm font-semibold text-stone-500 uppercase tracking-wide mb-2">{{ __('portal.reschedule_current') }}</h2>
        <p class="text-ink">
            {{ $booking->starts_at->locale(app()->getLocale())->isoFormat('dddd D MMMM YYYY [à] HH:mm') }}
            · {{ $booking->format === 'online' ? __('booking.online') : __('booking.in_office') }}
        </p>
    </div>

    @if (empty($availableSlots))
        <div class="rounded-xl border border-warning/30 bg-warning/5 p-8 text-center">
            <p class="text-warning font-medium">{{ __('portal.reschedule_no_slots') }}</p>
            <p class="text-stone-500 text-sm mt-2">{{ __('portal.reschedule_no_slots_help') }}</p>
        </div>
    @else
        <form method="POST" action="{{ route('portal.bookings.reschedule.update', ['locale' => app()->getLocale(), 'reference' => $booking->reference]) }}">
            @csrf

            <div class="space-y-3 mb-8">
                @php
                    $grouped = [];
                    foreach ($availableSlots as $slot) {
                        $dateKey = $slot->startsAt->locale(app()->getLocale())->isoFormat('dddd D MMMM YYYY');
                        $grouped[$dateKey][] = $slot;
                    }
                @endphp

                @foreach ($grouped as $date => $slots)
                    <div class="mb-4">
                        <h3 class="text-sm font-semibold text-stone-500 mb-2">{{ $date }}</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($slots as $slot)
                                <label class="cursor-pointer">
                                    <input type="radio" name="starts_at" value="{{ $slot->startsAt->toIso8601String() }}"
                                           class="peer sr-only" required>
                                    <span class="inline-flex items-center rounded-md border border-stone-200 px-3 py-1.5 text-sm text-ink
                                                 peer-checked:border-brass-500 peer-checked:bg-brass-50 peer-checked:text-brass-700
                                                 hover:border-stone-300 transition-fast">
                                        {{ $slot->startsAt->format('H:i') }} — {{ $slot->endsAt->format('H:i') }}
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
                <button type="submit"
                        class="rounded-md bg-brass-500 px-6 py-2.5 text-sm font-medium text-parchment hover:bg-brass-600 transition-fast">
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
