<?php

use App\Domain\Services\NotificationService;
use App\Models\Booking;
use App\Models\Client;
use App\Notifications\BookingConfirmation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();
    $this->service = app(NotificationService::class);
});

test('sendBookingConfirmation sends notification to client', function () {
    $client = Client::factory()->create();
    $booking = Booking::factory()->create([
        'client_id' => $client->id,
        'status' => 'confirmed',
    ]);

    $this->service->sendBookingConfirmation($booking);

    Notification::assertSentTo($client, BookingConfirmation::class);
});

test('sendBookingConfirmation only sends to booking with a client', function () {
    $booking = Booking::factory()->create(['status' => 'confirmed']);

    $this->service->sendBookingConfirmation($booking);

    Notification::assertSentTimes(BookingConfirmation::class, 1);
});
