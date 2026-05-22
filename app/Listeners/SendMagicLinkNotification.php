<?php

namespace App\Listeners;

use App\Events\MagicLinkRequested;
use App\Notifications\MagicLinkNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

final class SendMagicLinkNotification implements ShouldQueue
{
    public function handle(MagicLinkRequested $event): void
    {
        $locale = $event->client->preferred_locale ?? 'fr';

        Notification::route('mail', $event->client->email)
            ->notify(new MagicLinkNotification(
                signedUrl: $event->signedUrl,
                intendedUrl: $event->intendedUrl,
                locale: $locale,
            ));
    }
}
