<?php

namespace App\Domain\Services;

use App\Domain\Payment\CreateIntentRequest;
use App\Domain\Payment\GatewayWebhookEvent;
use App\Domain\Payment\PaymentGateway;
use App\Enums\PaymentStatus;
use App\Events\PaymentFailed;
use App\Events\PaymentSucceeded;
use App\Events\RefundIssued;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use App\ValueObjects\MoneyMad;
use Illuminate\Support\Str;

final class PaymentService
{
    public function __construct(
        private readonly PaymentGateway $gateway,
    ) {}

    public function createIntent(Booking $booking): array
    {
        $intent = $this->gateway->createIntent(new CreateIntentRequest(
            idempotencyKey: "booking-{$booking->id}",
            amountCentimes: $booking->total_centimes,
            currency: strtolower($booking->currency),
            metadata: [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->reference,
            ],
        ));

        Payment::create([
            'uuid' => (string) Str::uuid(),
            'booking_id' => $booking->id,
            'gateway' => $this->gateway->name()->value,
            'gateway_intent_id' => $intent->id,
            'amount_centimes' => $intent->amountCentimes,
            'currency' => $booking->currency,
            'status' => PaymentStatus::PENDING->value,
        ]);

        return [
            'client_secret' => $intent->clientSecret,
            'intent_id' => $intent->id,
        ];
    }

    public function confirmFromWebhook(GatewayWebhookEvent $event): void
    {
        $payment = Payment::where('gateway_intent_id', $event->data['id'])->firstOrFail();

        if ($event->type === 'payment_intent.succeeded') {
            $payment->status = PaymentStatus::SUCCEEDED->value;
            $payment->paid_at = now();

            $charges = $event->data['charges']['data'] ?? [];
            $payment->gateway_charge_id = $charges[0]['id'] ?? null;

            $payment->save();

            PaymentSucceeded::dispatch($payment);
        } elseif ($event->type === 'payment_intent.payment_failed') {
            $payment->status = PaymentStatus::FAILED->value;
            $payment->save();

            PaymentFailed::dispatch($payment);
        }
    }

    public function refund(Payment $payment, MoneyMad $amount, string $reason, ?User $by = null): Refund
    {
        $refund = new Refund;
        $refund->payment_id = $payment->id;
        $refund->amount_centimes = $amount->centimes;
        $refund->reason = $reason;
        $refund->gateway_refund_id = '';
        $refund->requested_by = $by?->id;
        $refund->status = 'requested';
        $refund->save();

        return $refund;
    }

    public function processRefund(Refund $refund): void
    {
        $payment = $refund->payment;
        $chargeId = $payment->gateway_charge_id;

        if (! $chargeId) {
            throw new \RuntimeException('Cannot refund payment without a charge ID');
        }

        $gatewayRefund = $this->gateway->refund(
            chargeId: $chargeId,
            amount: new MoneyMad($refund->amount_centimes),
            reason: $refund->reason,
        );

        $refund->gateway_refund_id = $gatewayRefund->id;
        $refund->status = 'succeeded';
        $refund->processed_at = now();
        $refund->save();

        RefundIssued::dispatch($refund);
    }

    public function markCashSucceeded(Payment $payment, User $by): void
    {
        $payment->status = PaymentStatus::SUCCEEDED->value;
        $payment->paid_at = now();
        $payment->save();

        PaymentSucceeded::dispatch($payment);
    }

    public function createCashPending(Booking $booking): Payment
    {
        $payment = Payment::create([
            'uuid' => (string) Str::uuid(),
            'booking_id' => $booking->id,
            'gateway' => 'cash',
            'gateway_intent_id' => 'cash-'.Str::random(16),
            'amount_centimes' => $booking->total_centimes,
            'currency' => $booking->currency,
            'status' => PaymentStatus::PENDING->value,
        ]);

        return $payment;
    }
}
