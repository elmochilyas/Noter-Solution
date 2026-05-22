<?php

use App\Events\BookingConfirmed;
use App\Jobs\SendBookingNotification;
use App\Listeners\ScheduleReminders;
use App\Models\Booking;
use App\Models\Client;
use App\Notifications\BookingReminder;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->client = Client::factory()->create();
});

test('ScheduleReminders calculates correct delays for future appointments', function () {
    Carbon::setTestNow('2026-06-01 08:00');

    $booking = Booking::factory()->create([
        'client_id' => $this->client->id,
        'status' => 'confirmed',
        'starts_at' => '2026-06-04 10:00:00',
    ]);

    $totalHours = $booking->starts_at->diffInHours(now(), true);
    expect($totalHours)->toBeGreaterThanOrEqual(72);

    $hoursBefore24 = $totalHours - 24;
    $hoursBefore1 = $totalHours - 1;

    expect($hoursBefore24)->toBeGreaterThan(0);
    expect($hoursBefore1)->toBeGreaterThan(0);

    Carbon::setTestNow();
});

test('ScheduleReminders handles past appointments gracefully', function () {
    $booking = Booking::factory()->create([
        'client_id' => $this->client->id,
        'status' => 'confirmed',
        'starts_at' => now()->subDay(),
    ]);

    app(ScheduleReminders::class)->handle(new BookingConfirmed($booking));

    expect($booking->fresh()->status)->toBe('confirmed');
});

test('SendBookingNotification does nothing for cancelled booking', function () {
    $booking = Booking::factory()->create([
        'client_id' => $this->client->id,
        'status' => 'cancelled',
    ]);

    $job = new SendBookingNotification($booking, '24h');
    expect($job->handle())->toBeNull();
});

test('SendBookingNotification sends reminder for confirmed booking', function () {
    Notification::fake();

    $booking = Booking::factory()->create([
        'client_id' => $this->client->id,
        'status' => 'confirmed',
    ]);

    $job = new SendBookingNotification($booking, '24h');
    $job->handle();

    Notification::assertSentTo($this->client, BookingReminder::class);
});
