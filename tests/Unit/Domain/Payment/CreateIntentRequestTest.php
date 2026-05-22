<?php

use App\Domain\Payment\CreateIntentRequest;

test('can create CreateIntentRequest', function () {
    $request = new CreateIntentRequest(
        idempotencyKey: 'booking-42',
        amountCentimes: 25000,
        currency: 'mad',
        metadata: ['booking_id' => 42],
    );

    expect($request->idempotencyKey)->toBe('booking-42');
    expect($request->amountCentimes)->toBe(25000);
    expect($request->currency)->toBe('mad');
    expect($request->metadata)->toBe(['booking_id' => 42]);
});

test('CreateIntentRequest can be created without metadata', function () {
    $request = new CreateIntentRequest(
        idempotencyKey: 'booking-1',
        amountCentimes: 0,
        currency: 'mad',
    );

    expect($request->metadata)->toBe([]);
});
