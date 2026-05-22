<?php

namespace App\Listeners;

use App\Domain\Services\NotificationService;
use App\Events\BookingRescheduled;
use Illuminate\Contracts\Queue\ShouldQueue;

final class SendBookingRescheduledNotifications implements ShouldQueue
{
    public function __construct(
        private readonly NotificationService $notifications,
    ) {}

    public function handle(BookingRescheduled $event): void
    {
        $this->notifications->sendBookingRescheduled($event->oldBooking, $event->newBooking);
    }
}
