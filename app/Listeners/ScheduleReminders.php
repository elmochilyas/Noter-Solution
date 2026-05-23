<?php

namespace App\Listeners;

use App\Events\BookingConfirmed;
use App\Jobs\SendBookingNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

final class ScheduleReminders implements ShouldQueue
{
    public function handle(BookingConfirmed $event): void
    {
        $booking = $event->booking;
        $startAt = $booking->starts_at;

        if (! $startAt || $startAt->isPast()) {
            return;
        }

        $totalHours = $startAt->diffInHours(now(), true);
        if ($totalHours <= 0) {
            return;
        }

        $hoursBefore24 = $totalHours - 24;
        $hoursBefore1 = $totalHours - 1;

        if ($hoursBefore24 > 0) {
            SendBookingNotification::dispatch($booking, '24h')
                ->delay(now()->addHours($hoursBefore24));
        }

        if ($hoursBefore1 > 0) {
            SendBookingNotification::dispatch($booking, '1h')
                ->delay(now()->addHours($hoursBefore1));
        }
    }
}
