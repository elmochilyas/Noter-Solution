<?php

use App\Domain\Services\BookingService;
use App\Enums\BookingFormat;
use App\Enums\BookingStatus;
use App\Enums\Locale;
use App\Enums\ServiceCategory;
use App\Events\BookingConfirmed;
use App\Events\BookingRescheduled;
use App\Exceptions\Domain\InvalidBookingTransition;
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
    $this->slot = new TimeSlot(
        CarbonImmutable::parse('+7 days 10:00'),
        CarbonImmutable::parse('+7 days 10:30'),
    );
    $this->client = Client::factory()->create();
    $this->plan = ConsultationPlan::factory()->create();
});

test('createPending creates a booking with pending_payment status', function () {
    $data = new BookingData(
        consultationPlanId: $this->plan->id,
        serviceCategory: ServiceCategory::FAMILY,
        format: BookingFormat::ONLINE,
        slot: $this->slot,
        clientFullName: 'Test User',
        clientEmail: 'test@example.com',
        clientPhone: MoroccanPhoneNumber::fromInput('0612345678'),
        description: 'Test description for booking',
        locale: Locale::FR,
    );

    $booking = $this->service->createPending($data, $this->client);

    expect($booking)->toBeInstanceOf(Booking::class)
        ->status->toBe(BookingStatus::PENDING_PAYMENT->value)
        ->reference->not->toBeNull();
});

test('confirm changes booking status to confirmed and dispatches event', function () {
    Event::fake();

    $booking = Booking::factory()->create([
        'status' => BookingStatus::PENDING_PAYMENT->value,
    ]);

    $this->service->confirm($booking);

    expect($booking->refresh()->status)->toBe(BookingStatus::CONFIRMED->value);

    Event::assertDispatched(BookingConfirmed::class);
});

test('confirm throws on invalid transition', function () {
    $booking = Booking::factory()->create([
        'status' => BookingStatus::CONFIRMED->value,
    ]);

    $this->service->confirm($booking);
})->throws(InvalidBookingTransition::class);

test('complete marks booking as completed', function () {
    $booking = Booking::factory()->create([
        'status' => BookingStatus::CONFIRMED->value,
    ]);

    $this->service->complete($booking);

    $booking->refresh();
    expect($booking->status)->toBe(BookingStatus::COMPLETED->value);
    expect($booking->completed_at)->not->toBeNull();
});

test('markNoShow marks booking as no_show', function () {
    $booking = Booking::factory()->create([
        'status' => BookingStatus::CONFIRMED->value,
    ]);

    $this->service->markNoShow($booking);

    expect($booking->refresh()->status)->toBe(BookingStatus::NO_SHOW->value);
});

test('cancel marks booking as cancelled', function () {
    $booking = Booking::factory()->create([
        'status' => BookingStatus::CONFIRMED->value,
    ]);

    $this->service->cancel($booking, 'Test reason', $this->client);

    $booking->refresh();
    expect($booking->status)->toBe(BookingStatus::CANCELLED->value);
    expect($booking->cancellation_reason)->toBe('Test reason');
    expect($booking->cancelled_at)->not->toBeNull();
});

test('reschedule creates new booking and cancels old one', function () {
    $booking = Booking::factory()->create([
        'status' => BookingStatus::CONFIRMED->value,
        'client_id' => $this->client->id,
    ]);

    $newSlot = new TimeSlot(
        CarbonImmutable::parse('+14 days 14:00'),
        CarbonImmutable::parse('+14 days 14:30'),
    );

    $newBooking = $this->service->reschedule($booking, $newSlot);

    $booking->refresh();
    expect($booking->status)->toBe(BookingStatus::CANCELLED->value);
    expect($booking->cancellation_reason)->toBe('rescheduled');

    expect($newBooking)->toBeInstanceOf(Booking::class);
    expect($newBooking->status)->toBe(BookingStatus::PENDING_PAYMENT->value);
});

test('reschedule dispatches BookingRescheduled event', function () {
    Event::fake();

    $booking = Booking::factory()->create([
        'status' => BookingStatus::CONFIRMED->value,
        'client_id' => $this->client->id,
    ]);

    $newSlot = new TimeSlot(
        CarbonImmutable::parse('+14 days 14:00'),
        CarbonImmutable::parse('+14 days 14:30'),
    );

    $newBooking = $this->service->reschedule($booking, $newSlot);

    Event::assertDispatched(BookingRescheduled::class, function ($event) use ($booking, $newBooking) {
        return $event->oldBooking->is($booking)
            && $event->newBooking->is($newBooking);
    });
});

test('cancel throws on invalid transition', function () {
    $booking = Booking::factory()->create([
        'status' => BookingStatus::COMPLETED->value,
    ]);

    $this->service->cancel($booking, 'reason', $this->client);
})->throws(InvalidBookingTransition::class);
