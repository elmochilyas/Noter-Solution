<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MagicLinkFlashNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $signedUrl,
        public string $locale = 'fr',
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'magic_link_flash',
            'url' => $this->signedUrl,
            'message' => __('notifications.magic_link_flash_in_app.line1', [], $this->locale),
            'locale' => $this->locale,
        ];
    }
}
