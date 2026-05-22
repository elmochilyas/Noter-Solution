<?php

namespace App\ValueObjects;

use App\Enums\Locale;
use InvalidArgumentException;

final readonly class MoneyMad
{
    public function __construct(public int $centimes)
    {
        if ($centimes < 0) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public static function fromDirhams(float $dirhams): self
    {
        return new self((int) round($dirhams * 100));
    }

    public function add(self $other): self
    {
        return new self($this->centimes + $other->centimes);
    }

    public function subtract(self $other): self
    {
        return new self($this->centimes - $other->centimes);
    }

    public function isZero(): bool
    {
        return $this->centimes === 0;
    }

    public function dirhams(): float
    {
        return $this->centimes / 100;
    }

    public function formatted(Locale $locale): string
    {
        $amount = number_format($this->dirhams(), 2, ',', ' ');

        return match ($locale) {
            Locale::AR => "{$amount} درهم",
            Locale::FR => "{$amount} MAD",
        };
    }
}
