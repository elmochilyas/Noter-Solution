<?php

namespace App\Listeners;

use App\Domain\Services\NotificationService;
use App\Events\RefundIssued;
use Illuminate\Contracts\Queue\ShouldQueue;

final class SendRefundIssuedNotification implements ShouldQueue
{
    public function __construct(
        private readonly NotificationService $notifications,
    ) {}

    public function handle(RefundIssued $event): void
    {
        $this->notifications->sendRefundIssued($event->refund);
    }
}
