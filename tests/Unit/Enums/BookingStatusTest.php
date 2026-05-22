<?php

use App\Enums\BookingStatus;

test('BookingStatus has all expected cases', function () {
    expect(BookingStatus::PENDING_PAYMENT->value)->toBe('pending_payment');
    expect(BookingStatus::CONFIRMED->value)->toBe('confirmed');
    expect(BookingStatus::COMPLETED->value)->toBe('completed');
    expect(BookingStatus::CANCELLED->value)->toBe('cancelled');
    expect(BookingStatus::NO_SHOW->value)->toBe('no_show');
});

test('isTerminal returns true for terminal statuses', function (BookingStatus $status) {
    expect($status->isTerminal())->toBeTrue();
})->with([
    BookingStatus::COMPLETED,
    BookingStatus::CANCELLED,
    BookingStatus::NO_SHOW,
]);

test('isTerminal returns false for non-terminal statuses', function (BookingStatus $status) {
    expect($status->isTerminal())->toBeFalse();
})->with([
    BookingStatus::PENDING_PAYMENT,
    BookingStatus::CONFIRMED,
]);
