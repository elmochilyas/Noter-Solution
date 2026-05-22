<?php

namespace App\Notifications;

use App\Channels\TwilioSmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TwilioSmsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $text,
    ) {}

    public function via(object $notifiable): array
    {
        return [TwilioSmsChannel::class];
    }

    public function toTwilioSms(object $notifiable): string
    {
        return $this->text;
    }
}
