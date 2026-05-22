<?php

namespace App\ValueObjects;

use InvalidArgumentException;

final readonly class BookingReference
{
    public function __construct(public string $value)
    {
        if (! preg_match('/^SBA-[A-Z0-9]{6}$/', $value)) {
            throw new InvalidArgumentException("Invalid reference: {$value}");
        }
    }

    public static function generate(): self
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        $code = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return new self('SBA-'.$code);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
