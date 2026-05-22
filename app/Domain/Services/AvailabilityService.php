<?php

namespace App\Domain\Services;

use App\Enums\BookingFormat;
use App\Models\AvailabilityException;
use App\Models\AvailabilityRule;
use App\Models\Booking;
use App\Models\BookingHold;
use App\Models\Client;
use App\Models\ConsultationPlan;
use App\ValueObjects\TimeSlot;
use Carbon\CarbonImmutable;

final class AvailabilityService
{
    public function availableSlots(
        CarbonImmutable $from,
        CarbonImmutable $to,
        ConsultationPlan $plan,
        BookingFormat $format,
    ): iterable {
        $slots = [];

        $current = $from->copy()->startOfDay();
        while ($current->lessThanOrEqualTo($to)) {
            $dayOfWeek = (int) $current->dayOfWeekIso;

            $rules = AvailabilityRule::where('day_of_week', $dayOfWeek)
                ->where('is_active', true)
                ->where(function ($q) use ($format) {
                    $q->where('format', $format->value)->orWhere('format', 'both');
                })
                ->get();

            foreach ($rules as $rule) {
                $slotStart = $current->setTimeFromTimeString($rule->starts_at);
                $slotEnd = $current->setTimeFromTimeString($rule->ends_at);
                $slots[] = new TimeSlot($slotStart, $slotEnd);
            }

            $current = $current->addDay();
        }

        $exceptions = AvailabilityException::whereBetween('starts_at', [$from, $to])
            ->orWhereBetween('ends_at', [$from, $to])
            ->get();

        $bookedSlots = Booking::whereIn('status', ['pending_payment', 'confirmed'])
            ->whereBetween('starts_at', [$from, $to])
            ->get();

        foreach ($slots as $i => $slot) {
            $isExcluded = $exceptions->contains(fn (AvailabilityException $e) => $slot->overlaps(
                new TimeSlot($e->starts_at->toImmutable(), $e->ends_at->toImmutable()),
            ));

            $isBooked = $bookedSlots->contains(fn (Booking $b) => $slot->overlaps(
                new TimeSlot($b->starts_at->toImmutable(), $b->ends_at->toImmutable()),
            ));

            if ($isExcluded || $isBooked) {
                unset($slots[$i]);
            }
        }

        return array_values($slots);
    }

    public function assertSlotIsFree(TimeSlot $slot, BookingFormat $format): void
    {
        $overlapping = Booking::whereIn('status', ['pending_payment', 'confirmed'])
            ->where('starts_at', '<', $slot->endsAt)
            ->where('ends_at', '>', $slot->startsAt)
            ->exists();

        if ($overlapping) {
            throw new \RuntimeException('Time slot overlaps with an existing booking');
        }
    }

    public function holdSlot(TimeSlot $slot, string $sessionId, ?Client $client = null): BookingHold
    {
        return BookingHold::create([
            'slot_starts_at' => $slot->startsAt,
            'slot_ends_at' => $slot->endsAt,
            'client_id' => $client?->id,
            'session_id' => $sessionId,
            'expires_at' => now()->addMinutes(15),
        ]);
    }
}
