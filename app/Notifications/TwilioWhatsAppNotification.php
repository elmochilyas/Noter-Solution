<?php

namespace App\Notifications;

use App\Channels\TwilioWhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TwilioWhatsAppNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $text,
    ) {}

    public function via(object $notifiable): array
    {
        return [TwilioWhatsAppChannel::class];
    }

    public function toTwilioWhatsApp(object $notifiable): string
    {
        return $this->text;
    }
}
