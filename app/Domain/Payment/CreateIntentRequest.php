<?php

namespace App\Domain\Payment;

final readonly class CreateIntentRequest
{
    public function __construct(
        public string $idempotencyKey,
        public int $amountCentimes,
        public string $currency,
        public array $metadata = [],
    ) {}
}
