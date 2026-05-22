<?php

namespace App\Enums;

enum PaymentGatewayName: string
{
    case STRIPE = 'stripe';
    case CMI = 'cmi';
    case CASH = 'cash';
}
