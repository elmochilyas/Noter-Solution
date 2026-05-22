<?php

use App\Domain\Services\AvailabilityService;
use App\Enums\BookingFormat;
use App\Models\ConsultationPlan;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(AvailabilityService::class);
    $this->plan = ConsultationPlan::factory()->create(['duration_minutes' => 30]);
});

test('availableSlots caches results', function () {
    $from = CarbonImmutable::tomorrow();
    $to = $from->addDays(7);

    $key = "slots:{$this->plan->id}:online:{$from->format('Ymd')}:{$to->format('Ymd')}";

    $this->service->availableSlots($from, $to, $this->plan, BookingFormat::ONLINE);

    expect(Cache::has($key))->toBeTrue();
});

test('clearSlotsCache removes cached entry', function () {
    $from = CarbonImmutable::tomorrow();
    $to = $from->addDays(7);

    $this->service->availableSlots($from, $to, $this->plan, BookingFormat::ONLINE);

    $this->service->clearSlotsCache($this->plan, BookingFormat::ONLINE);

    expect(Cache::has("slots:{$this->plan->id}:online:*"))->toBeFalse();
});
