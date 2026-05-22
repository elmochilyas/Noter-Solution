<?php

use App\Enums\PlanFormat;

test('PlanFormat has all expected cases', function () {
    expect(PlanFormat::ONLINE->value)->toBe('online');
    expect(PlanFormat::IN_OFFICE->value)->toBe('in_office');
    expect(PlanFormat::BOTH->value)->toBe('both');
});
