<?php

namespace App\ValueObjects;

use App\Enums\BookingFormat;
use App\Enums\Locale;
use App\Enums\ServiceCategory;

final readonly class BookingData
{
    public function __construct(
        public int $consultationPlanId,
        public ServiceCategory $serviceCategory,
        public BookingFormat $format,
        public TimeSlot $slot,
        public string $clientFullName,
        public string $clientEmail,
        public MoroccanPhoneNumber $clientPhone,
        public string $description,
        public Locale $locale,
        public int $totalCentimes = 0,
        public array $documents = [],
    ) {}
}
