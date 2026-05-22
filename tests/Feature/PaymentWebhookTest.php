<?php

use App\Domain\Payment\GatewayWebhookEvent;
use App\Domain\Services\PaymentService;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(PaymentService::class);
});

test('confirmFromWebhook marks payment succeeded and sets paid_at', function () {
    $payment = Payment::factory()->create([
        'gateway_intent_id' => 'pi_test_123',
        'status' => PaymentStatus::PENDING->value,
    ]);

    $event = new GatewayWebhookEvent(
        type: 'payment_intent.succeeded',
        id: 'evt_test_123',
        data: [
            'id' => 'pi_test_123',
            'charges' => ['data' => [['id' => 'ch_test_456']]],
        ],
    );

    $this->service->confirmFromWebhook($event);

    $fresh = Payment::find($payment->id);
    expect($fresh->status)->toBe(PaymentStatus::SUCCEEDED->value);
    expect($fresh->paid_at)->not->toBeNull();
    expect($fresh->gateway_charge_id)->toBe('ch_test_456');
});

test('confirmFromWebhook marks payment failed', function () {
    $payment = Payment::factory()->create([
        'gateway_intent_id' => 'pi_test_789',
        'status' => PaymentStatus::PENDING->value,
    ]);

    $event = new GatewayWebhookEvent(
        type: 'payment_intent.payment_failed',
        id: 'evt_test_456',
        data: ['id' => 'pi_test_789'],
    );

    $this->service->confirmFromWebhook($event);

    $fresh = Payment::find($payment->id);
    expect($fresh->status)->toBe(PaymentStatus::FAILED->value);
});
