<?php

use App\ValueObjects\TimeSlot;
use Carbon\CarbonImmutable;

test('can create valid TimeSlot', function () {
    $start = CarbonImmutable::parse('2026-06-01 09:00');
    $end = CarbonImmutable::parse('2026-06-01 10:00');
    $slot = new TimeSlot($start, $end);
    expect($slot->startsAt)->toBe($start);
    expect($slot->endsAt)->toBe($end);
});

test('throws when end is before start', function () {
    new TimeSlot(
        CarbonImmutable::parse('2026-06-01 10:00'),
        CarbonImmutable::parse('2026-06-01 09:00'),
    );
})->throws(InvalidArgumentException::class, 'Slot end must be after start');

test('throws when start equals end', function () {
    new TimeSlot(
        CarbonImmutable::parse('2026-06-01 09:00'),
        CarbonImmutable::parse('2026-06-01 09:00'),
    );
})->throws(InvalidArgumentException::class, 'Slot end must be after start');

test('durationMinutes returns correct duration', function () {
    $slot = new TimeSlot(
        CarbonImmutable::parse('2026-06-01 09:00'),
        CarbonImmutable::parse('2026-06-01 10:30'),
    );
    expect($slot->durationMinutes())->toBe(90);
});

test('overlaps detects overlapping slots', function () {
    $a = new TimeSlot(
        CarbonImmutable::parse('2026-06-01 09:00'),
        CarbonImmutable::parse('2026-06-01 10:00'),
    );
    $b = new TimeSlot(
        CarbonImmutable::parse('2026-06-01 09:30'),
        CarbonImmutable::parse('2026-06-01 10:30'),
    );
    expect($a->overlaps($b))->toBeTrue();
    expect($b->overlaps($a))->toBeTrue();
});

test('overlaps detects non-overlapping slots', function () {
    $a = new TimeSlot(
        CarbonImmutable::parse('2026-06-01 09:00'),
        CarbonImmutable::parse('2026-06-01 10:00'),
    );
    $b = new TimeSlot(
        CarbonImmutable::parse('2026-06-01 10:00'),
        CarbonImmutable::parse('2026-06-01 11:00'),
    );
    expect($a->overlaps($b))->toBeFalse();
});

test('overlaps with exact boundaries does not overlap', function () {
    $a = new TimeSlot(
        CarbonImmutable::parse('2026-06-01 09:00'),
        CarbonImmutable::parse('2026-06-01 10:00'),
    );
    $b = new TimeSlot(
        CarbonImmutable::parse('2026-06-01 10:00'),
        CarbonImmutable::parse('2026-06-01 11:00'),
    );
    expect($a->overlaps($b))->toBeFalse();
});

test('contains checks moment within slot', function () {
    $slot = new TimeSlot(
        CarbonImmutable::parse('2026-06-01 09:00'),
        CarbonImmutable::parse('2026-06-01 10:00'),
    );
    expect($slot->contains(CarbonImmutable::parse('2026-06-01 09:30')))->toBeTrue();
    expect($slot->contains(CarbonImmutable::parse('2026-06-01 09:00')))->toBeTrue();
    expect($slot->contains(CarbonImmutable::parse('2026-06-01 10:00')))->toBeTrue();
    expect($slot->contains(CarbonImmutable::parse('2026-06-01 08:00')))->toBeFalse();
    expect($slot->contains(CarbonImmutable::parse('2026-06-01 11:00')))->toBeFalse();
});

test('isPast returns false for future slot', function () {
    $slot = new TimeSlot(
        CarbonImmutable::now()->addDay(),
        CarbonImmutable::now()->addDay()->addHour(),
    );
    expect($slot->isPast())->toBeFalse();
});

test('isPast returns true for past slot', function () {
    $slot = new TimeSlot(
        CarbonImmutable::now()->subDay(),
        CarbonImmutable::now()->subDay()->addHour(),
    );
    expect($slot->isPast())->toBeTrue();
});
