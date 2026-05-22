<?php

namespace App\Notifications;

use App\Models\Refund;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RefundIssuedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Refund $refund,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $booking = $this->refund->payment->booking;
        $locale = $booking->client?->preferred_locale ?? 'fr';

        return (new MailMessage)
            ->subject(__('notifications.refund_issued.subject', [], $locale))
            ->greeting(__('notifications.hello', [], $locale).' '.$booking->client?->full_name)
            ->line(__('notifications.refund_issued.line1', [], $locale))
            ->line(__('notifications.booking_confirmed.reference', [], $locale).' : '.$booking->reference)
            ->line(__('notifications.refund_issued.amount', [], $locale).' : '.number_format($this->refund->amount_centimes / 100, 2, ',', ' ').' MAD')
            ->line(__('notifications.refund_issued.delay_note', [], $locale))
            ->replyTo(config('mail.reply_to.address'), config('mail.reply_to.name'))
            ->salutation(__('notifications.regards', [], $locale).",\n".__('notifications.signature', [], $locale));
    }
}
