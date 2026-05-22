<?php

use App\ValueObjects\MoroccanPhoneNumber;

test('fromInput parses 06xxxxxxxx format', function () {
    $phone = MoroccanPhoneNumber::fromInput('0612345678');
    expect($phone->e164)->toBe('+212612345678');
});

test('fromInput parses 07xxxxxxxx format', function () {
    $phone = MoroccanPhoneNumber::fromInput('0712345678');
    expect($phone->e164)->toBe('+212712345678');
});

test('fromInput parses 05xxxxxxxx format', function () {
    $phone = MoroccanPhoneNumber::fromInput('0512345678');
    expect($phone->e164)->toBe('+212512345678');
});

test('fromInput parses +2126xxxxxxxx format', function () {
    $phone = MoroccanPhoneNumber::fromInput('+212612345678');
    expect($phone->e164)->toBe('+212612345678');
});

test('fromInput parses +2127xxxxxxxx format', function () {
    $phone = MoroccanPhoneNumber::fromInput('+212712345678');
    expect($phone->e164)->toBe('+212712345678');
});

test('fromInput parses 2126xxxxxxxx format', function () {
    $phone = MoroccanPhoneNumber::fromInput('212612345678');
    expect($phone->e164)->toBe('+212612345678');
});

test('fromInput strips separators', function () {
    $phone = MoroccanPhoneNumber::fromInput('06 12 34 56 78');
    expect($phone->e164)->toBe('+212612345678');
});

test('fromInput strips dashes', function () {
    $phone = MoroccanPhoneNumber::fromInput('06-12-34-56-78');
    expect($phone->e164)->toBe('+212612345678');
});

test('fromInput throws on invalid number', function () {
    MoroccanPhoneNumber::fromInput('12345');
})->throws(InvalidArgumentException::class);

test('fromInput throws on non-Moroccan number', function () {
    MoroccanPhoneNumber::fromInput('+33612345678');
})->throws(InvalidArgumentException::class);

test('national formats number in pairs', function () {
    $phone = MoroccanPhoneNumber::fromInput('0612345678');
    expect($phone->national())->toBe('26 12 34 56 78');
});

test('whatsapp returns whatsapp:// URI', function () {
    $phone = MoroccanPhoneNumber::fromInput('0612345678');
    expect($phone->whatsapp())->toBe('whatsapp:+212612345678');
});

test('__toString returns e164', function () {
    $phone = MoroccanPhoneNumber::fromInput('0612345678');
    expect((string) $phone)->toBe('+212612345678');
});
