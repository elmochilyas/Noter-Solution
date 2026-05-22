<?php

namespace App\Infrastructure\Payment;

use App\Domain\Payment\CreateIntentRequest;
use App\Domain\Payment\GatewayIntent;
use App\Domain\Payment\GatewayRefund;
use App\Domain\Payment\GatewayWebhookEvent;
use App\Domain\Payment\PaymentGateway;
use App\Enums\PaymentGatewayName;
use App\ValueObjects\MoneyMad;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent as StripePaymentIntent;
use Stripe\Refund as StripeRefund;
use Stripe\Stripe;
use Stripe\Webhook;

final class StripeGateway implements PaymentGateway
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        Stripe::setApiVersion('2025-02-24');
    }

    public function createIntent(CreateIntentRequest $request): GatewayIntent
    {
        $params = [
            'amount' => $request->amountCentimes,
            'currency' => $request->currency,
            'metadata' => $request->metadata,
            'payment_method_types' => ['card'],
            'capture_method' => 'automatic',
        ];

        $options = [
            'idempotency_key' => $request->idempotencyKey,
        ];

        $intent = StripePaymentIntent::create($params, $options);

        return new GatewayIntent(
            id: $intent->id,
            clientSecret: $intent->client_secret,
            amountCentimes: $intent->amount,
        );
    }

    public function verifyWebhook(Request $request): GatewayWebhookEvent
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature', '');
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (SignatureVerificationException $e) {
            throw new \RuntimeException('Invalid Stripe webhook signature: '.$e->getMessage());
        }

        return new GatewayWebhookEvent(
            type: $event->type,
            id: $event->id,
            data: json_decode(json_encode($event->data->object), true),
        );
    }

    public function refund(string $chargeId, MoneyMad $amount, string $reason): GatewayRefund
    {
        $refund = StripeRefund::create([
            'charge' => $chargeId,
            'amount' => $amount->centimes,
            'reason' => $reason,
        ]);

        return new GatewayRefund(
            id: $refund->id,
            chargeId: $chargeId,
            amountCentimes: $refund->amount,
        );
    }

    public function name(): PaymentGatewayName
    {
        return PaymentGatewayName::STRIPE;
    }
}
