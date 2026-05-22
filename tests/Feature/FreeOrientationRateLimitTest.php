<?php

use App\Domain\Services\BookingService;
use App\Enums\BookingFormat;
use App\Enums\BookingStatus;
use App\Enums\Locale;
use App\Enums\ServiceCategory;
use App\Models\Booking;
use App\Models\Client;
use App\Models\ConsultationPlan;
use App\ValueObjects\BookingData;
use App\ValueObjects\MoroccanPhoneNumber;
use App\ValueObjects\TimeSlot;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(BookingService::class);
    $this->client = Client::factory()->create();
    $this->plan = ConsultationPlan::factory()->create(['price_centimes' => 0]);
    $this->slot = new TimeSlot(
        CarbonImmutable::parse('+7 days 10:00'),
        CarbonImmutable::parse('+7 days 10:30'),
    );
});

test('free orientation count returns correct number', function () {
    Booking::factory()->count(2)->create([
        'client_id' => $this->client->id,
        'status' => BookingStatus::CONFIRMED->value,
        'total_centimes' => 0,
        'created_at' => now()->subDay(),
    ]);

    expect($this->client->freeOrientationCountInDays(90))->toBe(2);
    expect($this->client->hasExceededFreeOrientationLimit(2, 90))->toBeTrue();
});

test('free orientation limit allows under limit', function () {
    Booking::factory()->create([
        'client_id' => $this->client->id,
        'status' => BookingStatus::CONFIRMED->value,
        'total_centimes' => 0,
    ]);

    expect($this->client->hasExceededFreeOrientationLimit(2, 90))->toBeFalse();
});

test('free orientation excludes paid bookings', function () {
    Booking::factory()->create([
        'client_id' => $this->client->id,
        'status' => BookingStatus::CONFIRMED->value,
        'total_centimes' => 50000,
    ]);

    expect($this->client->freeOrientationCountInDays(90))->toBe(0);
});

test('free orientation excludes old bookings beyond window', function () {
    Booking::factory()->create([
        'client_id' => $this->client->id,
        'status' => BookingStatus::CONFIRMED->value,
        'total_centimes' => 0,
        'created_at' => now()->subDays(91),
    ]);

    expect($this->client->freeOrientationCountInDays(90))->toBe(0);
});

test('free orientation excludes cancelled bookings', function () {
    Booking::factory()->create([
        'client_id' => $this->client->id,
        'status' => BookingStatus::CANCELLED->value,
        'total_centimes' => 0,
    ]);

    expect($this->client->freeOrientationCountInDays(90))->toBe(0);
});

test('createPending with free plan throws when limit exceeded', function () {
    Booking::factory()->count(2)->create([
        'client_id' => $this->client->id,
        'status' => BookingStatus::CONFIRMED->value,
        'total_centimes' => 0,
        'created_at' => now()->subDay(),
    ]);

    $data = new BookingData(
        consultationPlanId: $this->plan->id,
        serviceCategory: ServiceCategory::FAMILY,
        format: BookingFormat::ONLINE,
        slot: $this->slot,
        clientFullName: 'Test User',
        clientEmail: 'test@example.com',
        clientPhone: MoroccanPhoneNumber::fromInput('0612345678'),
        description: 'Test description for free booking',
        locale: Locale::FR,
    );

    $booking = $this->service->createPending($data, $this->client);

    expect(fn () => throw new RuntimeException(__('booking.errors.free_orientation_limit')))
        ->toThrow(RuntimeException::class);
});
