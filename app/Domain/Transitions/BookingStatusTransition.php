<?php

namespace App\Domain\Transitions;

use App\Enums\BookingStatus;
use App\Exceptions\Domain\InvalidBookingTransition;

class BookingStatusTransition
{
    public static function assertCanTransition(BookingStatus $from, BookingStatus $to): void
    {
        $allowed = match ([$from, $to]) {
            [BookingStatus::PENDING_PAYMENT, BookingStatus::CONFIRMED],
            [BookingStatus::PENDING_PAYMENT, BookingStatus::CANCELLED],
            [BookingStatus::CONFIRMED, BookingStatus::COMPLETED],
            [BookingStatus::CONFIRMED, BookingStatus::NO_SHOW],
            [BookingStatus::CONFIRMED, BookingStatus::CANCELLED] => true,
            default => false,
        };

        if (! $allowed) {
            throw new InvalidBookingTransition($from, $to);
        }
    }
}
