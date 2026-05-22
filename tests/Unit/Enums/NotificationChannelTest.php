<?php

use App\Enums\NotificationChannel;

test('NotificationChannel has all expected cases', function () {
    expect(NotificationChannel::EMAIL->value)->toBe('email');
    expect(NotificationChannel::SMS->value)->toBe('sms');
    expect(NotificationChannel::WHATSAPP->value)->toBe('whatsapp');
});
