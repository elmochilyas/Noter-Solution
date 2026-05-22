<?php

use App\Domain\Services\BookingService;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Client;
use App\Models\ConsultationPlan;
use App\ValueObjects\TimeSlot;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(BookingService::class);
    $this->client = Client::factory()->create();
    $this->plan = ConsultationPlan::factory()->create();
    $this->oldSlot = new TimeSlot(
        CarbonImmutable::parse('+5 days 10:00'),
        CarbonImmutable::parse('+5 days 10:30'),
    );
});

test('reschedule count returns correct number', function () {
    Booking::factory()->count(3)->create([
        'client_id' => $this->client->id,
        'cancellation_reason' => 'rescheduled',
        'cancelled_at' => now()->subDay(),
    ]);

    expect($this->client->rescheduleCountInDays(30))->toBe(3);
});

test('reschedule limit blocks when exceeded', function () {
    Booking::factory()->count(2)->create([
        'client_id' => $this->client->id,
        'status' => BookingStatus::CANCELLED->value,
        'cancellation_reason' => 'rescheduled',
        'cancelled_at' => now()->subDay(),
    ]);

    expect($this->client->hasExceededRescheduleLimit(2, 30))->toBeTrue();
});

test('reschedule limit allows within limit', function () {
    Booking::factory()->create([
        'client_id' => $this->client->id,
        'status' => BookingStatus::CANCELLED->value,
        'cancellation_reason' => 'rescheduled',
        'cancelled_at' => now()->subDay(),
    ]);

    expect($this->client->hasExceededRescheduleLimit(2, 30))->toBeFalse();
});

test('reschedule limit ignores non-rescheduled cancellations', function () {
    Booking::factory()->create([
        'client_id' => $this->client->id,
        'status' => BookingStatus::CANCELLED->value,
        'cancellation_reason' => 'client_request',
        'cancelled_at' => now()->subDay(),
    ]);

    expect($this->client->rescheduleCountInDays(30))->toBe(0);
});

test('reschedule limit ignores old reschedules beyond window', function () {
    Booking::factory()->create([
        'client_id' => $this->client->id,
        'status' => BookingStatus::CANCELLED->value,
        'cancellation_reason' => 'rescheduled',
        'cancelled_at' => now()->subDays(31),
    ]);

    expect($this->client->rescheduleCountInDays(30))->toBe(0);
});

test('reschedule throws when limit exceeded', function () {
    Booking::factory()->count(2)->create([
        'client_id' => $this->client->id,
        'status' => BookingStatus::CANCELLED->value,
        'cancellation_reason' => 'rescheduled',
        'cancelled_at' => now()->subDay(),
    ]);

    $booking = Booking::factory()->create([
        'client_id' => $this->client->id,
        'status' => BookingStatus::CONFIRMED->value,
    ]);

    $newSlot = new TimeSlot(
        CarbonImmutable::parse('+7 days 14:00'),
        CarbonImmutable::parse('+7 days 14:30'),
    );

    expect(fn () => $this->service->reschedule($booking, $newSlot))
        ->toThrow(RuntimeException::class, 'Reschedule limit reached');
});
