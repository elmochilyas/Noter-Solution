<?php

namespace App\ValueObjects;

use InvalidArgumentException;
use Stringable;

final readonly class MoroccanPhoneNumber implements Stringable
{
    public function __construct(public string $e164) {}

    public static function fromInput(string $input): self
    {
        $cleaned = preg_replace('/[\s\-\.\(\)]+/', '', $input);

        if (preg_match('/^0(6|7|5)(\d{8})$/', $cleaned, $m)) {
            return new self('+212'.$m[1].$m[2]);
        }

        if (preg_match('/^\+212(6|7|5)(\d{8})$/', $cleaned, $m)) {
            return new self('+212'.$m[1].$m[2]);
        }

        if (preg_match('/^212(6|7|5)(\d{8})$/', $cleaned, $m)) {
            return new self('+212'.$m[1].$m[2]);
        }

        throw new InvalidArgumentException("Invalid Moroccan phone number: {$input}");
    }

    public function national(): string
    {
        $digits = substr($this->e164, 3);

        return implode(' ', str_split($digits, 2));
    }

    public function whatsapp(): string
    {
        return "whatsapp:{$this->e164}";
    }

    public function __toString(): string
    {
        return $this->e164;
    }
}
