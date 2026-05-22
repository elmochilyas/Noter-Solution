<?php

namespace App\Exceptions\Domain;

use App\Enums\BookingStatus;
use Exception;

class InvalidBookingTransition extends Exception
{
    public function __construct(BookingStatus $from, BookingStatus $to)
    {
        parent::__construct("Invalid booking transition from {$from->value} to {$to->value}");
    }
}
