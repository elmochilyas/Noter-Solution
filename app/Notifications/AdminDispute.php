<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminDispute extends Notification
{
    use Queueable;

    public function __construct(
        public Booking $booking,
        public string $message,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Litige signalé : '.$this->booking->reference)
            ->greeting('Litige signalé')
            ->line('Référence réservation : '.$this->booking->reference)
            ->line('Client : '.$this->booking->client?->full_name)
            ->line('')
            ->line('Message du client :')
            ->line($this->message)
            ->salutation('Cabinet de Maître Sana Bouhamidi');
    }
}
