<?php

use App\Enums\Locale;
use App\ValueObjects\MoneyMad;

test('can create MoneyMad from centimes', function () {
    $money = new MoneyMad(15000);
    expect($money->centimes)->toBe(15000);
});

test('MoneyMad throws on negative centimes', function () {
    new MoneyMad(-1);
})->throws(InvalidArgumentException::class, 'Amount cannot be negative');

test('zero creates zero amount', function () {
    $money = MoneyMad::zero();
    expect($money->centimes)->toBe(0);
    expect($money->isZero())->toBeTrue();
});

test('fromDirhams converts correctly', function () {
    $money = MoneyMad::fromDirhams(150.50);
    expect($money->centimes)->toBe(15050);
});

test('fromDirhams rounds correctly', function () {
    $money = MoneyMad::fromDirhams(10.999);
    expect($money->centimes)->toBe(1100);
});

test('dirhams returns correct float', function () {
    $money = new MoneyMad(15050);
    expect($money->dirhams())->toBe(150.50);
});

test('add sums two amounts', function () {
    $a = new MoneyMad(1000);
    $b = new MoneyMad(2000);
    $result = $a->add($b);
    expect($result->centimes)->toBe(3000);
    expect($result)->not->toBe($a);
});

test('subtract computes difference', function () {
    $a = new MoneyMad(3000);
    $b = new MoneyMad(1000);
    $result = $a->subtract($b);
    expect($result->centimes)->toBe(2000);
});

test('isZero returns true only for zero', function () {
    expect((new MoneyMad(0))->isZero())->toBeTrue();
    expect((new MoneyMad(1))->isZero())->toBeFalse();
});

test('formatted returns French string', function () {
    $money = MoneyMad::fromDirhams(150.50);
    expect($money->formatted(Locale::FR))->toBe('150,50 MAD');
});

test('formatted returns Arabic string', function () {
    $money = MoneyMad::fromDirhams(150.50);
    expect($money->formatted(Locale::AR))->toBe('150,50 درهم');
});

test('formatted handles large amounts', function () {
    $money = MoneyMad::fromDirhams(10000);
    expect($money->formatted(Locale::FR))->toBe('10 000,00 MAD');
});
