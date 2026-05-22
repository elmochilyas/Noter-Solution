<?php

namespace App\Notifications;

use App\Models\Receipt;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class PaymentReceipt extends Notification
{
    use Queueable;

    public function __construct(
        public Receipt $receipt,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $booking = $this->receipt->booking;
        $locale = $booking->client?->preferred_locale ?? 'fr';

        $msg = (new MailMessage)
            ->subject(__('notifications.payment_receipt.subject', [], $locale))
            ->greeting(__('notifications.hello', [], $locale).' '.$booking->client?->full_name)
            ->line(__('notifications.payment_receipt.line1', [], $locale))
            ->line(__('notifications.payment_receipt.number', [], $locale).' : '.$this->receipt->number)
            ->line(__('notifications.payment_receipt.amount', [], $locale).' : '.number_format($this->receipt->amount_centimes / 100, 2, ',', ' ').' MAD');

        if ($this->receipt->storage_path && Storage::disk('receipts')->exists($this->receipt->storage_path)) {
            $msg->attach(Storage::disk('receipts')->path($this->receipt->storage_path), [
                'as' => 'recu-'.$this->receipt->number.'.pdf',
                'mime' => 'application/pdf',
            ]);
        }

        return $msg->replyTo(config('mail.reply_to.address'), config('mail.reply_to.name'))
            ->salutation(__('notifications.regards', [], $locale).",\n".__('notifications.signature', [], $locale));
    }
}
