<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminNewBooking extends Notification
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
        return (new MailMessage)
            ->subject('Nouvelle réservation : '.$this->booking->reference)
            ->greeting('Nouvelle réservation')
            ->line('Référence : '.$this->booking->reference)
            ->line('Client : '.$this->booking->client?->full_name)
            ->line('Email : '.$this->booking->client?->email)
            ->line('Téléphone : '.$this->booking->client?->phone)
            ->line('Date : '.$this->booking->starts_at->isoFormat('dddd D MMMM YYYY [à] HH:mm'))
            ->line('Statut : '.$this->booking->status)
            ->salutation('Cabinet de Maître Sana Bouhamidi');
    }
}
