<?php

use App\Domain\Payment\GatewayWebhookEvent;

test('can create GatewayWebhookEvent', function () {
    $event = new GatewayWebhookEvent(
        type: 'payment_intent.succeeded',
        id: 'evt_abc123',
        data: ['id' => 'pi_abc123', 'amount' => 25000],
    );

    expect($event->type)->toBe('payment_intent.succeeded');
    expect($event->id)->toBe('evt_abc123');
    expect($event->data['id'])->toBe('pi_abc123');
    expect($event->data['amount'])->toBe(25000);
});
