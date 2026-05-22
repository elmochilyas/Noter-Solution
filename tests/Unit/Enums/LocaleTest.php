<?php

use App\Enums\Locale;

test('Locale has AR and FR cases', function () {
    expect(Locale::AR->value)->toBe('ar');
    expect(Locale::FR->value)->toBe('fr');
});

test('Locale cases are strings', function () {
    expect(Locale::AR)->toBeInstanceOf(BackedEnum::class);
    expect(Locale::FR)->toBeInstanceOf(BackedEnum::class);
});
