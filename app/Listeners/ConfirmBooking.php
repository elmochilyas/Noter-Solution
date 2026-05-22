<?php

namespace App\Listeners;

use App\Domain\Services\BookingService;
use App\Events\PaymentSucceeded;
use Illuminate\Contracts\Queue\ShouldQueue;

final class ConfirmBooking implements ShouldQueue
{
    public function __construct(
        private readonly BookingService $bookings,
    ) {}

    public function handle(PaymentSucceeded $event): void
    {
        $booking = $event->payment->booking;

        $this->bookings->confirm($booking, $event->payment);
    }
}
