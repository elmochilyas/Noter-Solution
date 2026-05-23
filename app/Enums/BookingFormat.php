<?php

namespace App\Enums;

enum BookingFormat: string
{
    case ONLINE = 'online';
    case IN_OFFICE = 'in_office';

    public function label(): string
    {
        return __("booking.format.{$this->value}");
    }
}
