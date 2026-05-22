<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingRescheduledNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Booking $oldBooking,
        public Booking $newBooking,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $locale = $this->newBooking->client?->preferred_locale ?? 'fr';

        return (new MailMessage)
            ->subject(__('notifications.booking_rescheduled.subject', [], $locale))
            ->greeting(__('notifications.hello', [], $locale).' '.$this->newBooking->client?->full_name)
            ->line(__('notifications.booking_rescheduled.line1', [], $locale))
            ->line(__('notifications.booking_confirmed.reference', [], $locale).' : '.$this->newBooking->reference)
            ->line(__('notifications.booking_confirmed.date', [], $locale).' : '.$this->newBooking->starts_at->locale($locale)->isoFormat('dddd D MMMM YYYY [à] HH:mm'))
            ->replyTo(config('mail.reply_to.address'), config('mail.reply_to.name'))
            ->salutation(__('notifications.regards', [], $locale).",\n".__('notifications.signature', [], $locale));
    }
}
