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

    $version = Cache::get("slots:v:{$this->plan->id}:online", 0);
    $key = "slots:{$this->plan->id}:online:{$version}:{$from->format('Ymd')}:{$to->format('Ymd')}";

    $this->service->availableSlots($from, $to, $this->plan, BookingFormat::ONLINE);

    expect(Cache::has($key))->toBeTrue();
});

test('clearSlotsCache increments version to invalidate cache', function () {
    $from = CarbonImmutable::tomorrow();
    $to = $from->addDays(7);

    $versionBefore = Cache::get("slots:v:{$this->plan->id}:online", 0);

    $this->service->availableSlots($from, $to, $this->plan, BookingFormat::ONLINE);

    $this->service->clearSlotsCache($this->plan, BookingFormat::ONLINE);

    $versionAfter = Cache::get("slots:v:{$this->plan->id}:online", 0);
    expect($versionAfter)->toBe($versionBefore + 1);
});
