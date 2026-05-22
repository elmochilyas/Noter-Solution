<?php

namespace App\Notifications;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminContactMessage extends Notification
{
    use Queueable;

    public function __construct(
        public ContactMessage $message,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nouveau message de contact')
            ->greeting('Nouveau message')
            ->line('De : '.$this->message->name.' <'.$this->message->email.'>')
            ->line('Téléphone : '.($this->message->phone ?? 'Non renseigné'))
            ->line('')
            ->line('Message :')
            ->line($this->message->message)
            ->salutation('Cabinet de Maître Sana Bouhamidi');
    }
}
