<?php

use App\Enums\DocumentScanStatus;

test('DocumentScanStatus has all expected cases', function () {
    expect(DocumentScanStatus::PENDING->value)->toBe('pending');
    expect(DocumentScanStatus::CLEAN->value)->toBe('clean');
    expect(DocumentScanStatus::INFECTED->value)->toBe('infected');
});
