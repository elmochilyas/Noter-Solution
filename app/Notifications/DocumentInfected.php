<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentInfected extends Notification
{
    use Queueable;

    public function __construct(
        public Document $document,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('notifications.document_infected_subject'))
            ->greeting(__('notifications.document_infected_greeting'))
            ->line(__('notifications.document_infected_line1', [
                'filename' => $this->document->original_filename,
            ]))
            ->line(__('notifications.document_infected_line2'))
            ->line(__('notifications.document_infected_line3', [
                'reference' => $this->document->booking?->reference ?? 'N/A',
            ]))
            ->salutation(__('notifications.regards'));
    }
}
