<?php

use App\Jobs\PurgeExpiredBookingHolds;
use App\Models\BookingHold;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('PurgeExpiredBookingHolds deletes expired holds', function () {
    BookingHold::factory()->create([
        'expires_at' => now()->subMinutes(5),
    ]);

    BookingHold::factory()->create([
        'expires_at' => now()->addMinutes(30),
    ]);

    (new PurgeExpiredBookingHolds)->handle();

    expect(BookingHold::count())->toBe(1);
});

test('PurgeExpiredBookingHolds keeps active holds', function () {
    BookingHold::factory()->create([
        'expires_at' => now()->addMinutes(10),
    ]);

    (new PurgeExpiredBookingHolds)->handle();

    expect(BookingHold::count())->toBe(1);
});
