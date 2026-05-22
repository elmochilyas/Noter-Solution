<?php

use App\Infrastructure\Payment\CmiGateway;
use App\Infrastructure\Payment\StripeGateway;

return [
    'default' => env('PAYMENT_GATEWAY', 'stripe'),

    'gateways' => [
        'stripe' => StripeGateway::class,
        'cmi' => CmiGateway::class,
    ],
];
