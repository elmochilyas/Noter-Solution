<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewAppointmentLinkNotification extends Notification implements ShouldQueue
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

        $url = route('portal.login', ['locale' => $locale, 'email' => $this->booking->client?->email]);

        return (new MailMessage)
            ->subject(__('notifications.new_appointment_link.subject', [], $locale))
            ->greeting(__('notifications.hello', [], $locale).' '.$this->booking->client?->full_name)
            ->line(__('notifications.new_appointment_link.line1', [], $locale))
            ->line(__('notifications.booking_confirmed.reference', [], $locale).' : '.$this->booking->reference)
            ->line(__('notifications.booking_confirmed.date', [], $locale).' : '.$this->booking->starts_at->locale($locale)->isoFormat('dddd D MMMM YYYY [à] HH:mm'))
            ->action(__('notifications.new_appointment_link.button', [], $locale), $url)
            ->replyTo(config('mail.reply_to.address'), config('mail.reply_to.name'))
            ->salutation(__('notifications.regards', [], $locale).",\n".__('notifications.signature', [], $locale));
    }
}
