<?php

namespace App\Listeners;

use App\Domain\Services\NotificationService;
use App\Events\PaymentFailed;
use Illuminate\Contracts\Queue\ShouldQueue;

final class NotifyPaymentFailed implements ShouldQueue
{
    public function __construct(
        private readonly NotificationService $notifications,
    ) {}

    public function handle(PaymentFailed $event): void
    {
        $this->notifications->sendPaymentFailed($event->payment);
    }
}
