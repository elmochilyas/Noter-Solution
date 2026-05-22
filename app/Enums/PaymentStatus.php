<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case SUCCEEDED = 'succeeded';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';
    case PARTIALLY_REFUNDED = 'partially_refunded';

    public function isFinal(): bool
    {
        return match ($this) {
            self::SUCCEEDED, self::FAILED, self::REFUNDED => true,
            default => false,
        };
    }
}
