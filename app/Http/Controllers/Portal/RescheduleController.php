<?php

namespace App\Http\Controllers\Portal;

use App\Domain\Services\AvailabilityService;
use App\Domain\Services\BookingService;
use App\Enums\BookingFormat;
use App\Http\Controllers\Controller;
use App\ValueObjects\TimeSlot;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RescheduleController extends Controller
{
    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly BookingService $bookings,
    ) {}

    public function edit(string $reference): View
    {
        $client = auth('client')->user();

        $booking = $client->bookings()
            ->with('plan')
            ->where('reference', $reference)
            ->firstOrFail();

        $canCancel = $booking->starts_at->isAfter(now()->addHours(2))
            && in_array($booking->status, ['pending_payment', 'confirmed']);

        if (! $canCancel || $booking->status !== 'confirmed' || $client->hasExceededRescheduleLimit()) {
            abort(403);
        }

        $format = BookingFormat::from($booking->format);
        $plan = $booking->plan;

        $now = CarbonImmutable::now();
        $from = $now->addDay()->startOfDay();
        $to = $from->addDays(14);

        $availableSlots = $this->availability->availableSlots($from, $to, $plan, $format);

        return view('portal.bookings.reschedule', compact('booking', 'availableSlots', 'plan', 'format'));
    }

    public function update(string $reference, Request $request): RedirectResponse
    {
        $client = auth('client')->user();

        $booking = $client->bookings()
            ->with('plan')
            ->where('reference', $reference)
            ->firstOrFail();

        if (! $booking->starts_at->isAfter(now()->addHours(2))
            || $booking->status !== 'confirmed'
            || $client->hasExceededRescheduleLimit()) {
            abort(403);
        }

        $data = $request->validate([
            'starts_at' => ['required', 'date', 'after:'.now()->addHours(2)->toIso8601String()],
        ]);

        $startsAt = CarbonImmutable::parse($data['starts_at']);
        $plan = $booking->plan;

        if (! $plan) {
            return back()->withErrors(['starts_at' => __('portal.reschedule_invalid')]);
        }

        $slot = new TimeSlot($startsAt, $startsAt->addMinutes($plan->duration_minutes));

        $this->availability->assertSlotIsFree($slot, BookingFormat::from($booking->format));

        try {
            $newBooking = $this->bookings->reschedule($booking, $slot);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['starts_at' => $e->getMessage()]);
        }

        session()->flash('success', __('portal.reschedule_success', ['reference' => $newBooking->reference]));

        return redirect()->route('portal.bookings.show', [
            'locale' => app()->getLocale(),
            'reference' => $newBooking->reference,
        ]);
    }
}
