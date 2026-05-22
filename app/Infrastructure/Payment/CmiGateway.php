<?php

namespace App\Infrastructure\Payment;

use App\Domain\Payment\CreateIntentRequest;
use App\Domain\Payment\GatewayIntent;
use App\Domain\Payment\GatewayRefund;
use App\Domain\Payment\GatewayWebhookEvent;
use App\Domain\Payment\PaymentGateway;
use App\Enums\PaymentGatewayName;
use App\ValueObjects\MoneyMad;
use Illuminate\Http\Request;

final class CmiGateway implements PaymentGateway
{
    public function createIntent(CreateIntentRequest $request): GatewayIntent
    {
        throw new \RuntimeException('CMI gateway not yet implemented');
    }

    public function verifyWebhook(Request $request): GatewayWebhookEvent
    {
        throw new \RuntimeException('CMI gateway not yet implemented');
    }

    public function refund(string $chargeId, MoneyMad $amount, string $reason): GatewayRefund
    {
        throw new \RuntimeException('CMI gateway not yet implemented');
    }

    public function name(): PaymentGatewayName
    {
        return PaymentGatewayName::CMI;
    }
}
