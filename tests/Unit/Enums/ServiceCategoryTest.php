<?php

use App\Enums\ServiceCategory;

test('ServiceCategory has all expected cases', function () {
    expect(ServiceCategory::FAMILY->value)->toBe('family');
    expect(ServiceCategory::REAL_ESTATE->value)->toBe('real_estate');
    expect(ServiceCategory::FINANCIAL->value)->toBe('financial');
    expect(ServiceCategory::CONTRACTS->value)->toBe('contracts');
});
