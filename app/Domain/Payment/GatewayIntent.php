<?php

namespace App\Domain\Payment;

final readonly class GatewayIntent
{
    public function __construct(
        public string $id,
        public string $clientSecret,
        public int $amountCentimes,
    ) {}
}
