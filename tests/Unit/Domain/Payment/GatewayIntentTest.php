<?php

use App\Domain\Payment\GatewayIntent;

test('can create GatewayIntent', function () {
    $intent = new GatewayIntent(
        id: 'pi_abc123',
        clientSecret: 'pi_abc123_secret_xyz',
        amountCentimes: 25000,
    );

    expect($intent->id)->toBe('pi_abc123');
    expect($intent->clientSecret)->toBe('pi_abc123_secret_xyz');
    expect($intent->amountCentimes)->toBe(25000);
});
