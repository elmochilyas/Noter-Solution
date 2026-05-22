<?php

namespace App\Domain\Payment;

final readonly class GatewayRefund
{
    public function __construct(
        public string $id,
        public string $chargeId,
        public int $amountCentimes,
    ) {}
}
