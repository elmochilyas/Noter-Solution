<?php

namespace App\Enums;

enum BookingStatus: string
{
    case PENDING_PAYMENT = 'pending_payment';
    case CONFIRMED = 'confirmed';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case NO_SHOW = 'no_show';

    public function isTerminal(): bool
    {
        return match ($this) {
            self::COMPLETED, self::CANCELLED, self::NO_SHOW => true,
            default => false,
        };
    }
}
