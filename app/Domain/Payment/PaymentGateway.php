<?php

namespace App\Domain\Payment;

use App\Enums\PaymentGatewayName;
use App\ValueObjects\MoneyMad;
use Illuminate\Http\Request;

interface PaymentGateway
{
    public function createIntent(CreateIntentRequest $request): GatewayIntent;

    public function verifyWebhook(Request $request): GatewayWebhookEvent;

    public function refund(string $chargeId, MoneyMad $amount, string $reason): GatewayRefund;

    public function name(): PaymentGatewayName;
}
