<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(): View
    {
        $client = auth('client')->user();

        $bookings = $client->bookings()
            ->with(['plan', 'payment'])
            ->orderByRaw("CASE WHEN status IN ('pending_payment', 'confirmed') THEN 0 ELSE 1 END")
            ->orderBy('starts_at', 'desc')
            ->paginate(20);

        return view('portal.bookings.index', compact('bookings'));
    }

    public function show(string $reference): View
    {
        $client = auth('client')->user();

        $booking = $client->bookings()
            ->with(['plan', 'payment', 'receipt', 'documents', 'payment.refunds'])
            ->where('reference', $reference)
            ->firstOrFail();

        $canCancel = $client->can('cancel', $booking);

        $canReschedule = $booking->status === 'confirmed'
            && ! $client->hasExceededRescheduleLimit()
            && $canCancel;

        $showJoinLink = $booking->format === 'online'
            && $booking->status === 'confirmed'
            && $booking->starts_at->isBefore(now()->addMinutes(15))
            && $booking->starts_at->isAfter(now()->subHours(2));

        return view('portal.bookings.show', compact('booking', 'canCancel', 'canReschedule', 'showJoinLink'));
    }
}
