<?php

namespace App\Http\Controllers\Webhook;

use App\Domain\Payment\GatewayWebhookEvent;
use App\Domain\Services\PaymentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Sentry\State\Scope;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __construct(
        private readonly PaymentService $payments,
    ) {}

    public function __invoke(Request $request)
    {
        $this->configureSentryScope($request);

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        if (! $sigHeader) {
            return response('Missing signature', 400);
        }

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                config('services.stripe.webhook_secret'),
            );
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature verification failed', [
                'error' => $e->getMessage(),
            ]);

            return response('Invalid signature', 400);
        }

        $handledTypes = [
            'payment_intent.succeeded',
            'payment_intent.payment_failed',
            'payment_intent.canceled',
        ];

        Log::info('Stripe webhook received', [
            'event_id' => $event->id,
            'event_type' => $event->type,
        ]);

        if (! in_array($event->type, $handledTypes)) {
            return response('Unhandled event type', 200);
        }

        if ($this->alreadyProcessed($event->id)) {
            return response('Already processed', 200);
        }

        try {
            $this->payments->confirmFromWebhook(new GatewayWebhookEvent(
                type: $event->type,
                data: json_decode($payload, true)['data']['object'],
            ));

            $this->markProcessed($event->id);
        } catch (\Throwable $e) {
            Log::error('Stripe webhook processing failed', [
                'event_id' => $event->id,
                'event_type' => $event->type,
                'error' => $e->getMessage(),
            ]);

            return response('Processing failed', 500);
        }

        return response('OK', 200);
    }

    private function configureSentryScope(Request $request): void
    {
        if (! function_exists('sentry_configure_scope')) {
            return;
        }

        sentry_configure_scope(function (Scope $scope) use ($request): void {
            $scope->setTag('payment.gateway', 'stripe');
            $scope->setTag('webhook.source', 'stripe');

            $payload = json_decode($request->getContent(), true);
            $eventType = $payload['type'] ?? 'unknown';
            $scope->setTag('stripe.event_type', $eventType);
        });
    }

    private function alreadyProcessed(string $eventId): bool
    {
        return \Cache::has("stripe_webhook:{$eventId}");
    }

    private function markProcessed(string $eventId): void
    {
        \Cache::put("stripe_webhook:{$eventId}", true, 3600 * 24);
    }
}
