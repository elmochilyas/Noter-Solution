<?php

namespace App\Listeners;

use App\Events\ReceiptGenerated;
use App\Notifications\PaymentReceipt;

class SendReceiptNotification
{
    public function handle(ReceiptGenerated $event): void
    {
        $client = $event->receipt->booking?->client;

        if (! $client) {
            return;
        }

        $client->notify(new PaymentReceipt($event->receipt));
    }
}
