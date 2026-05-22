<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmation extends Notification
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

        return (new MailMessage)
            ->subject(__('notifications.booking_confirmed.subject', [], $locale))
            ->replyTo(config('mail.reply_to.address'), config('mail.reply_to.name'))
            ->markdown('emails.booking.confirmation', [
                'booking' => $this->booking,
                'locale' => $locale,
            ]);
    }
}
