<?php

use App\Enums\BookingFormat;
use App\Enums\Locale;
use App\Enums\ServiceCategory;
use App\ValueObjects\BookingData;
use App\ValueObjects\MoroccanPhoneNumber;
use App\ValueObjects\TimeSlot;
use Carbon\CarbonImmutable;

test('can create BookingData', function () {
    $slot = new TimeSlot(
        CarbonImmutable::parse('2026-06-01 09:00'),
        CarbonImmutable::parse('2026-06-01 10:00'),
    );
    $phone = MoroccanPhoneNumber::fromInput('0612345678');

    $data = new BookingData(
        consultationPlanId: 1,
        serviceCategory: ServiceCategory::FAMILY,
        format: BookingFormat::IN_OFFICE,
        slot: $slot,
        clientFullName: 'Ahmed Alami',
        clientEmail: 'ahmed@example.com',
        clientPhone: $phone,
        description: 'Marriage document',
        locale: Locale::FR,
    );

    expect($data->consultationPlanId)->toBe(1);
    expect($data->serviceCategory)->toBe(ServiceCategory::FAMILY);
    expect($data->format)->toBe(BookingFormat::IN_OFFICE);
    expect($data->slot)->toBe($slot);
    expect($data->clientFullName)->toBe('Ahmed Alami');
    expect($data->clientEmail)->toBe('ahmed@example.com');
    expect($data->clientPhone)->toBe($phone);
    expect($data->description)->toBe('Marriage document');
    expect($data->locale)->toBe(Locale::FR);
    expect($data->documents)->toBe([]);
});

test('BookingData can have documents', function () {
    $slot = new TimeSlot(
        CarbonImmutable::parse('2026-06-01 09:00'),
        CarbonImmutable::parse('2026-06-01 10:00'),
    );

    $data = new BookingData(
        consultationPlanId: 1,
        serviceCategory: ServiceCategory::REAL_ESTATE,
        format: BookingFormat::ONLINE,
        slot: $slot,
        clientFullName: 'Fatima Alaoui',
        clientEmail: 'fatima@example.com',
        clientPhone: MoroccanPhoneNumber::fromInput('0712345678'),
        description: 'Property deed',
        locale: Locale::AR,
        documents: ['doc1.pdf', 'doc2.jpg'],
    );

    expect($data->documents)->toBe(['doc1.pdf', 'doc2.jpg']);
});
