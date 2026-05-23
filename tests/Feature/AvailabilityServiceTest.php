<?php

use App\Domain\Services\AvailabilityService;
use App\Enums\BookingFormat;
use App\Exceptions\Domain\SlotNotAvailable;
use App\Models\AvailabilityException;
use App\Models\AvailabilityRule;
use App\Models\Booking;
use App\Models\BookingHold;
use App\Models\ConsultationPlan;
use App\ValueObjects\TimeSlot;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(AvailabilityService::class);
    $this->plan = ConsultationPlan::factory()->create([
        'duration_minutes' => 30,
        'format' => 'online',
    ]);
});

test('availableSlots returns slots for a day with rules', function () {
    $targetDate = CarbonImmutable::now()->addDays(30);

    AvailabilityRule::factory()->create([
        'day_of_week' => $targetDate->dayOfWeekIso,
        'starts_at' => '09:00',
        'ends_at' => '18:00',
        'format' => 'both',
        'is_active' => true,
    ]);

    $from = $targetDate->startOfDay();
    $to = $targetDate->endOfDay();

    $slots = $this->service->availableSlots($from, $to, $this->plan, BookingFormat::ONLINE);

    expect($slots)->not->toBeEmpty();
    expect($slots[0])->toBeInstanceOf(TimeSlot::class);
});

test('availableSlots excludes booked slots', function () {
    $targetDate = CarbonImmutable::now()->addDays(30);

    AvailabilityRule::factory()->create([
        'day_of_week' => $targetDate->dayOfWeekIso,
        'starts_at' => '09:00',
        'ends_at' => '18:00',
        'format' => 'both',
        'is_active' => true,
    ]);

    $from = $targetDate->startOfDay();

    Booking::factory()->create([
        'starts_at' => $from->addHours(10),
        'ends_at' => $from->addHours(10)->addMinutes(30),
        'status' => 'confirmed',
    ]);

    $slots = $this->service->availableSlots(
        $targetDate->startOfDay(),
        $targetDate->endOfDay(),
        $this->plan,
        BookingFormat::ONLINE,
    );

    $hasOverlap = collect($slots)->contains(fn (TimeSlot $s) => $s->startsAt->format('H:i') === '10:00'
    );

    expect($hasOverlap)->toBeFalse();
});

test('assertSlotIsFree passes for free slot', function () {
    $slot = new TimeSlot(
        CarbonImmutable::parse('+14 days 10:00'),
        CarbonImmutable::parse('+14 days 10:30'),
    );

    $this->service->assertSlotIsFree($slot, BookingFormat::ONLINE);

    expect(true)->toBeTrue();
});

test('assertSlotIsFree throws for booked slot', function () {
    $slot = new TimeSlot(
        CarbonImmutable::parse('+7 days 10:00'),
        CarbonImmutable::parse('+7 days 10:30'),
    );

    Booking::factory()->create([
        'starts_at' => $slot->startsAt,
        'ends_at' => $slot->endsAt,
        'status' => 'confirmed',
    ]);

    $this->service->assertSlotIsFree($slot, BookingFormat::ONLINE);
})->throws(SlotNotAvailable::class);

test('assertSlotIsFree throws for slot in availability exception period', function () {
    $slot = new TimeSlot(
        CarbonImmutable::parse('+7 days 10:00'),
        CarbonImmutable::parse('+7 days 10:30'),
    );

    AvailabilityException::factory()->create([
        'starts_at' => $slot->startsAt->subHour(),
        'ends_at' => $slot->endsAt->addHour(),
    ]);

    $this->service->assertSlotIsFree($slot, BookingFormat::ONLINE);
})->throws(SlotNotAvailable::class);

test('assertSlotIsFree throws for held slot', function () {
    $slot = new TimeSlot(
        CarbonImmutable::parse('+7 days 10:00'),
        CarbonImmutable::parse('+7 days 10:30'),
    );

    BookingHold::factory()->create([
        'slot_starts_at' => $slot->startsAt,
        'slot_ends_at' => $slot->endsAt,
        'expires_at' => now()->addMinutes(10),
    ]);

    $this->service->assertSlotIsFree($slot, BookingFormat::ONLINE);
})->throws(SlotNotAvailable::class);

test('availableSlots accepts correct argument order (from, to, plan, format)', function () {
    $targetDate = CarbonImmutable::now()->addDays(7);

    AvailabilityRule::factory()->create([
        'day_of_week' => $targetDate->dayOfWeekIso,
        'starts_at' => '09:00',
        'ends_at' => '18:00',
        'format' => 'both',
        'is_active' => true,
    ]);

    $from = $targetDate->startOfDay();
    $to = $targetDate->endOfDay();

    $slots = $this->service->availableSlots($from, $to, $this->plan, BookingFormat::ONLINE);

    expect($slots)->toBeArray();
    expect($slots[0])->toBeInstanceOf(TimeSlot::class);
});

test('holdSlot creates a hold with 10 minute expiry', function () {
    $slot = new TimeSlot(
        CarbonImmutable::parse('+7 days 10:00'),
        CarbonImmutable::parse('+7 days 10:30'),
    );

    $hold = $this->service->holdSlot($slot, 'test-session');

    expect($hold)->toBeInstanceOf(BookingHold::class)
        ->expires_at->toBeGreaterThan(now()->addMinutes(9));
});
