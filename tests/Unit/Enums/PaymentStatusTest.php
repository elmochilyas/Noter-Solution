<?php

use App\Enums\PaymentStatus;

test('PaymentStatus has all expected cases', function () {
    expect(PaymentStatus::PENDING->value)->toBe('pending');
    expect(PaymentStatus::SUCCEEDED->value)->toBe('succeeded');
    expect(PaymentStatus::FAILED->value)->toBe('failed');
    expect(PaymentStatus::REFUNDED->value)->toBe('refunded');
    expect(PaymentStatus::PARTIALLY_REFUNDED->value)->toBe('partially_refunded');
});

test('isFinal returns true for final statuses', function (PaymentStatus $status) {
    expect($status->isFinal())->toBeTrue();
})->with([
    PaymentStatus::SUCCEEDED,
    PaymentStatus::FAILED,
    PaymentStatus::REFUNDED,
]);

test('isFinal returns false for non-final statuses', function (PaymentStatus $status) {
    expect($status->isFinal())->toBeFalse();
})->with([
    PaymentStatus::PENDING,
    PaymentStatus::PARTIALLY_REFUNDED,
]);
