<?php

use App\Domain\Services\BookingService;
use App\Enums\BookingStatus;
use App\Exceptions\Domain\InvalidBookingTransition;
use App\Models\Booking;
use App\Models\Client;
use App\Models\ConsultationPlan;
use App\Models\User;
use App\ValueObjects\TimeSlot;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->owner = User::factory()->owner()->create();
    $this->assistant = User::factory()->assistant()->create();
    $this->client = Client::factory()->create();
    $plan = ConsultationPlan::factory()->create(['duration_minutes' => 60]);
    $this->pendingBooking = Booking::factory()->create([
        'client_id' => $this->client->id,
        'consultation_plan_id' => $plan->id,
        'status' => BookingStatus::PENDING_PAYMENT->value,
        'starts_at' => now()->addDays(3),
        'ends_at' => now()->addDays(3)->addHour(),
        'total_centimes' => 15000,
    ]);
    $this->confirmedBooking = Booking::factory()->create([
        'client_id' => $this->client->id,
        'consultation_plan_id' => $plan->id,
        'status' => BookingStatus::CONFIRMED->value,
        'starts_at' => now()->addDays(3),
        'ends_at' => now()->addDays(3)->addHour(),
        'total_centimes' => 15000,
    ]);
    $this->completedBooking = Booking::factory()->completed()->create([
        'client_id' => $this->client->id,
        'consultation_plan_id' => $plan->id,
    ]);
});

describe('BookingService::cancel', function () {
    it('transitions pending_payment booking to cancelled', function () {
        $this->actingAs($this->owner);

        app(BookingService::class)->cancel($this->pendingBooking, 'Client requested', $this->owner);

        expect($this->pendingBooking->fresh()->status)->toBe(BookingStatus::CANCELLED->value);
        expect($this->pendingBooking->fresh()->cancellation_reason)->toBe('Client requested');
        expect($this->pendingBooking->fresh()->cancelled_at)->not->toBeNull();
    });

    it('transitions confirmed booking to cancelled', function () {
        $this->actingAs($this->owner);

        app(BookingService::class)->cancel($this->confirmedBooking, 'Admin override', $this->owner);

        expect($this->confirmedBooking->fresh()->status)->toBe(BookingStatus::CANCELLED->value);
    });

    it('rejects cancel on completed booking', function () {
        $this->actingAs($this->owner);

        expect(fn () => app(BookingService::class)->cancel($this->completedBooking, 'Test', $this->owner))
            ->toThrow(InvalidBookingTransition::class);
    });
});

describe('BookingService::complete', function () {
    it('transitions confirmed booking to completed', function () {
        $this->actingAs($this->owner);

        app(BookingService::class)->complete($this->confirmedBooking);

        expect($this->confirmedBooking->fresh()->status)->toBe(BookingStatus::COMPLETED->value);
        expect($this->confirmedBooking->fresh()->completed_at)->not->toBeNull();
    });

    it('rejects complete on pending_payment booking', function () {
        $this->actingAs($this->owner);

        expect(fn () => app(BookingService::class)->complete($this->pendingBooking))
            ->toThrow(InvalidBookingTransition::class);
    });
});

describe('BookingService::markNoShow', function () {
    it('transitions confirmed booking to no_show', function () {
        $this->actingAs($this->owner);

        app(BookingService::class)->markNoShow($this->confirmedBooking);

        expect($this->confirmedBooking->fresh()->status)->toBe(BookingStatus::NO_SHOW->value);
    });
});

describe('BookingService::reschedule', function () {
    it('creates new booking and cancels old one', function () {
        $this->actingAs($this->owner);

        $newSlot = new TimeSlot(
            CarbonImmutable::parse('+10 days 10:00'),
            CarbonImmutable::parse('+10 days 11:00'),
        );

        $newBooking = app(BookingService::class)->reschedule($this->confirmedBooking, $newSlot);

        expect($newBooking)->toBeInstanceOf(Booking::class);
        expect($this->confirmedBooking->fresh()->status)->toBe(BookingStatus::CANCELLED->value);
        expect($newBooking->fresh()->status)->toBe(BookingStatus::PENDING_PAYMENT->value);
    });
});

describe('Booking action authorization', function () {
    it('allows owner to view any booking', function () {
        expect($this->owner->can('view', $this->pendingBooking))->toBeTrue();
    });

    it('allows assistant to cancel booking', function () {
        expect($this->assistant->can('cancel', $this->confirmedBooking))->toBeTrue();
    });

    it('allows owner to complete booking', function () {
        expect($this->owner->can('complete', $this->confirmedBooking))->toBeTrue();
    });

    it('allows assistant to mark no-show', function () {
        expect($this->assistant->can('markNoShow', $this->confirmedBooking))->toBeTrue();
    });

    it('rejects cancel on completed booking', function () {
        expect($this->owner->can('cancel', $this->completedBooking))->toBeFalse();
    });
});
