<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingReminder extends Notification
{
    use Queueable;

    public function __construct(
        public Booking $booking,
        public string $type, // '24h' or '1h'
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $locale = $this->booking->client?->preferred_locale ?? 'fr';
        $key = 'booking.reminder.'.$this->type;

        $lines = __("notifications.{$key}.lines", [], $locale);

        $msg = (new MailMessage)
            ->subject(__("notifications.{$key}.subject", [], $locale))
            ->greeting(__('notifications.hello', [], $locale).' '.$this->booking->client?->full_name)
            ->line($lines[0] ?? '')
            ->line(__('notifications.booking_confirmed.date', [], $locale).' : '.$this->booking->starts_at->locale($locale)->isoFormat('dddd D MMMM YYYY [à] HH:mm'))
            ->line(__('notifications.booking_confirmed.reference', [], $locale).' : '.$this->booking->reference);

        if ($this->booking->format === 'online') {
            $msg->line(__('notifications.booking_confirmed.video_note', [], $locale));
        } else {
            $msg->line(__('notifications.booking_confirmed.office_address', [], $locale));
        }

        return $msg->replyTo(config('mail.reply_to.address'), config('mail.reply_to.name'))
            ->salutation(__('notifications.regards', [], $locale).",\n".__('notifications.signature', [], $locale));
    }
}
