<?php

namespace App\Enums;

enum PaymentGatewayName: string
{
    case STRIPE = 'stripe';
    case CMI = 'cmi';
    case CASH = 'cash';

    public function label(): string
    {
        return __("payment.gateway.{$this->value}");
    }
}
