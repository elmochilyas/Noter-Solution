<?php

namespace App\Listeners;

use App\Events\BookingRescheduled;
use Illuminate\Contracts\Queue\ShouldQueue;

final class RescheduleReminders implements ShouldQueue
{
    public function handle(BookingRescheduled $event): void
    {
        // Wave 4: cancel old reminders, schedule new ones
    }
}
