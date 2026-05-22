<?php

namespace App\Jobs;

use App\Domain\Services\NotificationService;
use App\Models\Booking;
use App\Notifications\BookingConfirmation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class SendBookingNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        public Booking $booking,
        public string $type,
        public int $delayMinutes = 0,
    ) {}

    public function handle(): void
    {
        if ($this->booking->status !== 'confirmed') {
            return;
        }

        $client = $this->booking->client;
        if (! $client) {
            return;
        }

        $notifications = app(NotificationService::class);

        if ($this->type === 'confirmation') {
            $client->notify(new BookingConfirmation($this->booking));
        } elseif (in_array($this->type, ['24h', '1h'])) {
            $notifications->sendBookingReminder($this->booking, $this->type);
        }
    }
}
