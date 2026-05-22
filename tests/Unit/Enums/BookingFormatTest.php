<?php

use App\Enums\BookingFormat;

test('BookingFormat has all expected cases', function () {
    expect(BookingFormat::ONLINE->value)->toBe('online');
    expect(BookingFormat::IN_OFFICE->value)->toBe('in_office');
});
