<?php

namespace App\Http\Controllers\Portal;

use App\Domain\Services\BookingService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CancelController extends Controller
{
    public function __construct(
        private readonly BookingService $bookings,
    ) {}

    public function confirm(string $reference): View
    {
        $client = auth('client')->user();

        $booking = $client->bookings()
            ->with(['payment', 'plan'])
            ->where('reference', $reference)
            ->firstOrFail();

        if (! in_array($booking->status, ['pending_payment', 'confirmed'])) {
            abort(403);
        }

        if (! $booking->starts_at->isAfter(now()->addHours(2))) {
            abort(403, __('portal.cancel_window_expired'));
        }

        $hoursBeforeAppointment = max(0, now()->diffInHours($booking->starts_at, false));
        $refundPercentage = match (true) {
            $hoursBeforeAppointment >= 24 => 100,
            $hoursBeforeAppointment >= 2 => 50,
            default => 0,
        };

        $refundAmount = $booking->payment
            ? (int) round($booking->payment->amount_centimes * $refundPercentage / 100)
            : 0;

        return view('portal.bookings.cancel', compact('booking', 'refundPercentage', 'refundAmount'));
    }

    public function destroy(string $reference, Request $request): RedirectResponse
    {
        $client = auth('client')->user();

        $booking = $client->bookings()
            ->where('reference', $reference)
            ->firstOrFail();

        if (! in_array($booking->status, ['pending_payment', 'confirmed'])) {
            abort(403);
        }

        if (! $booking->starts_at->isAfter(now()->addHours(2))) {
            abort(403, __('portal.cancel_window_expired'));
        }

        $reason = $request->input('reason', '');

        $this->bookings->cancel($booking, $reason, $client);

        session()->flash('success', __('portal.cancelled_success'));

        return redirect()->route('portal.dashboard', ['locale' => $request->route('locale')]);
    }
}
