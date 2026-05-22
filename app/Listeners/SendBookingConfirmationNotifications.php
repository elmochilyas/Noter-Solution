<?php

namespace App\Listeners;

use App\Domain\Services\NotificationService;
use App\Events\BookingConfirmed;
use Illuminate\Contracts\Queue\ShouldQueue;

final class SendBookingConfirmationNotifications implements ShouldQueue
{
    public function __construct(
        private readonly NotificationService $notifications,
    ) {}

    public function handle(BookingConfirmed $event): void
    {
        $this->notifications->sendBookingConfirmation($event->booking);
    }
}
