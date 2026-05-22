<?php

use App\ValueObjects\BookingReference;

test('can create valid booking reference', function () {
    $ref = new BookingReference('SBA-ABC123');
    expect($ref->value)->toBe('SBA-ABC123');
});

test('throws on invalid format', function (string $invalid) {
    new BookingReference($invalid);
})->with([
    'SBA-ABC12',
    'SBA-ABC1234',
    'ABC-ABC123',
    'SBA-1',
    '',
    'SBA-abc123',
])->throws(InvalidArgumentException::class);

test('generate creates valid reference', function () {
    $ref = BookingReference::generate();
    expect($ref->value)->toMatch('/^SBA-[A-Z0-9]{6}$/');
});

test('generate creates unique references', function () {
    $refs = array_map(fn () => BookingReference::generate()->value, range(1, 100));
    expect(count(array_unique($refs)))->toBe(100);
});

test('__toString returns the reference string', function () {
    $ref = new BookingReference('SBA-ABC123');
    expect((string) $ref)->toBe('SBA-ABC123');
});

test('generate excludes ambiguous characters', function () {
    $ref = BookingReference::generate()->value;
    expect($ref)->not->toMatch('/[0O1I]/');
});
