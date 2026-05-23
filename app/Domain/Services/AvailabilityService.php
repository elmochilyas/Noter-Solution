<?php

namespace App\Domain\Services;

use App\Enums\BookingFormat;
use App\Exceptions\Domain\SlotNotAvailable;
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
    ): array {
        $cacheKey = "slots:{$plan->id}:{$format->value}:{$from->format('Ymd')}:{$to->format('Ymd')}";

        return \Cache::remember($cacheKey, 60, function () use ($from, $to, $plan, $format) {
            $slots = [];
            $durationMinutes = $plan->duration_minutes;
            $now = CarbonImmutable::now();

            $current = $from->startOfDay();
            while ($current->lessThanOrEqualTo($to)) {
                $dayOfWeek = (int) $current->dayOfWeekIso;

                $rules = AvailabilityRule::where('day_of_week', $dayOfWeek)
                    ->where('is_active', true)
                    ->where(function ($q) use ($format) {
                        $q->where('format', $format->value)->orWhere('format', 'both');
                    })
                    ->get();

                foreach ($rules as $rule) {
                    $ruleStartDay = $current->format('Y-m-d');
                    $ruleStart = CarbonImmutable::parse($ruleStartDay.' '.$rule->starts_at);
                    $ruleEnd = CarbonImmutable::parse($ruleStartDay.' '.$rule->ends_at);

                    $cursor = $ruleStart;
                    $maxIterations = 100;
                    $iterations = 0;

                    while ($cursor->lessThan($ruleEnd) && $iterations < $maxIterations) {
                        $slotEnd = $cursor->addMinutes($durationMinutes);

                        if ($slotEnd->greaterThan($ruleEnd)) {
                            break;
                        }

                        if ($cursor->hour < 9 || $slotEnd->hour > 18 || ($slotEnd->hour === 18 && $slotEnd->minute > 0)) {
                            $cursor = $slotEnd;
                            $iterations++;

                            continue;
                        }

                        if ($slotEnd->lessThanOrEqualTo($now->addHours(2))) {
                            $cursor = $slotEnd;
                            $iterations++;

                            continue;
                        }

                        $slots[] = new TimeSlot($cursor, $slotEnd);

                        $cursor = $slotEnd;
                        $iterations++;
                    }
                }

                $current = $current->addDay();
            }

            $exceptions = AvailabilityException::where('starts_at', '<', $to)
                ->where('ends_at', '>', $from)
                ->get();

            $bookedSlots = Booking::whereIn('status', ['pending_payment', 'confirmed'])
                ->where('starts_at', '<', $to)
                ->where('ends_at', '>', $from)
                ->get();

            $holds = BookingHold::where('expires_at', '>', $now)
                ->where('slot_starts_at', '<', $to)
                ->where('slot_ends_at', '>', $from)
                ->get();

            $slots = collect($slots)->filter(function (TimeSlot $slot) use ($exceptions, $bookedSlots, $holds) {
                $isExcluded = $exceptions->contains(fn (AvailabilityException $e) => $slot->overlaps(
                    new TimeSlot($e->starts_at->toImmutable(), $e->ends_at->toImmutable()),
                ));

                $isBooked = $bookedSlots->contains(fn (Booking $b) => $slot->overlaps(
                    new TimeSlot($b->starts_at->toImmutable(), $b->ends_at->toImmutable()),
                ));

                $isHeld = $holds->contains(fn (BookingHold $h) => $slot->overlaps(
                    new TimeSlot($h->slot_starts_at->toImmutable(), $h->slot_ends_at->toImmutable()),
                ));

                return ! $isExcluded && ! $isBooked && ! $isHeld;
            })->values()->toArray();

            return $slots;
        });
    }

    public function clearSlotsCache(ConsultationPlan $plan, BookingFormat $format): void
    {
        \Cache::forget("slots:{$plan->id}:{$format->value}:*");
    }

    public function assertSlotIsFree(TimeSlot $slot, BookingFormat $format): void
    {
        $overlapping = Booking::whereIn('status', ['pending_payment', 'confirmed'])
            ->where('starts_at', '<', $slot->endsAt)
            ->where('ends_at', '>', $slot->startsAt)
            ->exists();

        $held = BookingHold::where('expires_at', '>', now())
            ->where('slot_starts_at', '<', $slot->endsAt)
            ->where('slot_ends_at', '>', $slot->startsAt)
            ->exists();

        $exception = AvailabilityException::where('starts_at', '<', $slot->endsAt)
            ->where('ends_at', '>', $slot->startsAt)
            ->exists();

        if ($overlapping || $held || $exception) {
            throw new SlotNotAvailable;
        }
    }

    public function holdSlot(TimeSlot $slot, string $sessionId, ?Client $client = null): BookingHold
    {
        return BookingHold::create([
            'slot_starts_at' => $slot->startsAt,
            'slot_ends_at' => $slot->endsAt,
            'client_id' => $client?->id,
            'session_id' => $sessionId,
            'expires_at' => now()->addMinutes(10),
        ]);
    }
}
