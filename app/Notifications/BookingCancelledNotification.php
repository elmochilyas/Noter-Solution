<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCancelledNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Booking $booking,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $locale = $this->booking->client?->preferred_locale ?? 'fr';

        $msg = (new MailMessage)
            ->subject(__('notifications.booking_cancelled.subject', [], $locale))
            ->greeting(__('notifications.hello', [], $locale).' '.$this->booking->client?->full_name)
            ->line(__('notifications.booking_cancelled.line1', [], $locale))
            ->line(__('notifications.booking_confirmed.reference', [], $locale).' : '.$this->booking->reference);

        if ($this->booking->cancellation_reason === 'rescheduled') {
            $msg->line(__('notifications.booking_cancelled.rescheduled_note', [], $locale));
        }

        return $msg->replyTo(config('mail.reply_to.address'), config('mail.reply_to.name'))
            ->salutation(__('notifications.regards', [], $locale).",\n".__('notifications.signature', [], $locale));
    }
}
