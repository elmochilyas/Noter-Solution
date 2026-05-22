<?php

namespace App\Notifications;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class ContactMessageNotification extends Notification
{
    use Queueable;

    public function __construct(public ContactMessage $contactMessage) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Nouveau message de contact — {$this->contactMessage->subject}")
            ->greeting("Nouveau message de {$this->contactMessage->name}")
            ->line("Sujet : {$this->contactMessage->subject}")
            ->line("Email : {$this->contactMessage->email}")
            ->line(new HtmlString('<strong>Message :</strong>'))
            ->line($this->contactMessage->message)
            ->action('Voir dans le tableau de bord', url('/admin/contact-messages'))
            ->salutation('Noter');
    }
}
