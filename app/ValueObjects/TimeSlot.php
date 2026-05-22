<?php

namespace App\ValueObjects;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

final readonly class TimeSlot
{
    public function __construct(
        public CarbonImmutable $startsAt,
        public CarbonImmutable $endsAt,
    ) {
        if ($startsAt->greaterThanOrEqualTo($endsAt)) {
            throw new InvalidArgumentException('Slot end must be after start');
        }
    }

    public function durationMinutes(): int
    {
        return (int) $this->startsAt->diffInMinutes($this->endsAt);
    }

    public function overlaps(self $other): bool
    {
        return $this->startsAt->lessThan($other->endsAt)
            && $this->endsAt->greaterThan($other->startsAt);
    }

    public function contains(CarbonImmutable $moment): bool
    {
        return $moment->greaterThanOrEqualTo($this->startsAt)
            && $moment->lessThanOrEqualTo($this->endsAt);
    }

    public function isPast(): bool
    {
        return $this->endsAt->isPast();
    }
}
