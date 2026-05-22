<?php

namespace App\Notifications;

use App\Models\Refund;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminRefundRequest extends Notification
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

        return (new MailMessage)
            ->subject('Demande de remboursement : '.$booking->reference)
            ->greeting('Demande de remboursement')
            ->line('Référence réservation : '.$booking->reference)
            ->line('Client : '.$booking->client?->full_name)
            ->line('Montant : '.number_format($this->refund->amount_centimes / 100, 2, ',', ' ').' MAD')
            ->line('Motif : '.$this->refund->reason)
            ->action('Voir dans Filament', url('/admin/refunds/'.$this->refund->id.'/edit'))
            ->salutation('Cabinet de Maître Sana Bouhamidi');
    }
}
