<?php

use App\Enums\PaymentGatewayName;

test('PaymentGatewayName has all expected cases', function () {
    expect(PaymentGatewayName::STRIPE->value)->toBe('stripe');
    expect(PaymentGatewayName::CMI->value)->toBe('cmi');
    expect(PaymentGatewayName::CASH->value)->toBe('cash');
});
