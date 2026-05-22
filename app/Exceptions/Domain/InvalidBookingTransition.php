<?php

namespace App\Exceptions\Domain;

use App\Enums\BookingStatus;

class InvalidBookingTransition extends \RuntimeException
{
    public function __construct(BookingStatus $from, BookingStatus $to)
    {
        parent::__construct("Cannot transition booking from {$from->value} to {$to->value}");
    }
}
