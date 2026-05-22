<?php

namespace App\Domain\Payment;

final readonly class GatewayWebhookEvent
{
    public function __construct(
        public string $type,
        public string $id,
        public array $data,
    ) {}
}
