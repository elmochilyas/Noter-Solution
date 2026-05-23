<?php

namespace App\Listeners;

use App\Domain\Services\PaymentService;
use App\Enums\BookingStatus;
use App\Events\BookingCancelled;
use App\ValueObjects\MoneyMad;
use Illuminate\Contracts\Queue\ShouldQueue;

final class IssueRefundIfApplicable implements ShouldQueue
{
    public function __construct(
        private readonly PaymentService $payments,
    ) {}

    public function handle(BookingCancelled $event): void
    {
        $booking = $event->booking;

        if ($booking->status !== BookingStatus::CANCELLED->value) {
            return;
        }

        if ($booking->cancellation_reason === 'rescheduled') {
            return;
        }

        $payment = $booking->payment;

        if (! $payment || $payment->gateway === 'cash') {
            return;
        }

        $hoursBeforeAppointment = max(0, now()->diffInHours($booking->starts_at, false));

        $refundPercentage = match (true) {
            $hoursBeforeAppointment >= 24 => 100,
            $hoursBeforeAppointment >= 2 => 50,
            default => 0,
        };

        if ($refundPercentage <= 0) {
            return;
        }

        $refundAmount = new MoneyMad(
            (int) round($payment->amount_centimes * $refundPercentage / 100),
        );

        $this->payments->refund(
            $payment,
            $refundAmount,
            "Annulation client : remboursement de {$refundPercentage}%",
        );
    }
}
