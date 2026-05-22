<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MagicLinkNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $signedUrl,
        public ?string $intendedUrl = null,
        public string $locale = 'fr',
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $msg = (new MailMessage)
            ->subject(__('notifications.magic_link.subject', [], $this->locale))
            ->greeting(__('notifications.hello', [], $this->locale).' '.$notifiable->full_name)
            ->line(__('notifications.magic_link.line1', [], $this->locale))
            ->action(__('notifications.magic_link.button', [], $this->locale), $this->signedUrl)
            ->line(__('notifications.magic_link.expiry', [], $this->locale));

        return $msg->replyTo(config('mail.reply_to.address'), config('mail.reply_to.name'))
            ->salutation(__('notifications.regards', [], $this->locale).",\n".__('notifications.signature', [], $this->locale));
    }
}
