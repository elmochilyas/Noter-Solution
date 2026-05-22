<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentFailedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Payment $payment,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $booking = $this->payment->booking;
        $locale = $booking->client?->preferred_locale ?? 'fr';

        return (new MailMessage)
            ->subject(__('notifications.payment_failed.subject', [], $locale))
            ->greeting(__('notifications.hello', [], $locale).' '.$booking->client?->full_name)
            ->line(__('notifications.payment_failed.line1', [], $locale))
            ->line(__('notifications.booking_confirmed.reference', [], $locale).' : '.$booking->reference)
            ->line(__('notifications.payment_failed.line2', [], $locale))
            ->replyTo(config('mail.reply_to.address'), config('mail.reply_to.name'))
            ->salutation(__('notifications.regards', [], $locale).",\n".__('notifications.signature', [], $locale));
    }
}
