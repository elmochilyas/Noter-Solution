<?php

use App\Domain\Payment\GatewayRefund;

test('can create GatewayRefund', function () {
    $refund = new GatewayRefund(
        id: 're_abc123',
        chargeId: 'ch_xyz789',
        amountCentimes: 25000,
    );

    expect($refund->id)->toBe('re_abc123');
    expect($refund->chargeId)->toBe('ch_xyz789');
    expect($refund->amountCentimes)->toBe(25000);
});
