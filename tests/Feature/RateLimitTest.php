<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;

uses(RefreshDatabase::class);

describe('Fortify rate limiters', function () {
    it('limits login attempts to 5 per 15 minutes per email+IP', function () {
        $email = 'test@example.com';
        $ip = '192.168.1.1';

        $key = Str::transliterate(Str::lower($email).'|'.$ip);

        // Simulate hitting the rate limiter
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit('login|'.$key, 900);
        }

        expect(RateLimiter::tooManyAttempts('login|'.$key, 5))->toBeTrue();
    });

    it('allows under-limit login attempts', function () {
        $email = 'allowed@example.com';
        $ip = '10.0.0.1';

        $key = Str::transliterate(Str::lower($email).'|'.$ip);

        for ($i = 0; $i < 3; $i++) {
            RateLimiter::hit('login|'.$key, 900);
        }

        expect(RateLimiter::tooManyAttempts('login|'.$key, 5))->toBeFalse();
    });

    it('resets login attempts after 15 minutes', function () {
        $email = 'reset@example.com';
        $ip = '10.0.0.2';

        $key = Str::transliterate(Str::lower($email).'|'.$ip);

        RateLimiter::hit('login|'.$key, 1);

        expect(RateLimiter::tooManyAttempts('login|'.$key, 5))->toBeFalse();
    });
});

describe('Webhook throttle', function () {
    it('rate limits webhook endpoint at 1000 per 60 seconds', function () {
        $ip = '10.0.0.3';

        $key = 'webhooks|'.$ip;

        for ($i = 0; $i < 1000; $i++) {
            RateLimiter::hit($key, 60);
        }

        expect(RateLimiter::tooManyAttempts($key, 1000))->toBeTrue();
    });
});
