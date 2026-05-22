<?php

namespace App\Listeners;

use App\Domain\Services\NotificationService;
use App\Events\BookingCancelled;
use Illuminate\Contracts\Queue\ShouldQueue;

final class SendBookingCancelledNotifications implements ShouldQueue
{
    public function __construct(
        private readonly NotificationService $notifications,
    ) {}

    public function handle(BookingCancelled $event): void
    {
        $this->notifications->sendBookingCancelled($event->booking);
    }
}
