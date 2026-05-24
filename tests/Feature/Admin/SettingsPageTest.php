<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    Setting::set('refund_policy', ['cancellation_hours' => 24, 'reschedule_hours' => 2]);
});

it('assistant cannot update settings', function () {
    $assistant = User::factory()->assistant()->create();
    $this->actingAs($assistant);

    expect(auth()->user()->can('update', Setting::class))->toBeFalse();
});

it('owner can update settings', function () {
    $owner = User::factory()->owner()->create();
    $this->actingAs($owner);

    expect(auth()->user()->can('update', Setting::class))->toBeTrue();
});

it('persists and retrieves array settings', function () {
    Setting::set('practice_info', ['ice' => 'TEST12345', 'phone' => '+212600000000']);
    Setting::set('booking_lead_time', 4);

    expect(Setting::get('practice_info'))->toEqual(['ice' => 'TEST12345', 'phone' => '+212600000000']);
    expect(Setting::get('booking_lead_time'))->toBe(4);
});

it('returns default value when key missing', function () {
    expect(Setting::get('nonexistent_key', 'fallback'))->toBe('fallback');
});

it('stores array as JSON in database', function () {
    Setting::set('feature_flags', ['chatbot' => true, 'online_payment' => false]);

    $raw = DB::table('settings')
        ->where('key', 'feature_flags')
        ->value('value');

    expect($raw)->toBe('{"chatbot":true,"online_payment":false}');
});

it('decodes stored JSON on get', function () {
    Setting::set('quiet_hours', ['start' => '22:00', 'end' => '07:00']);

    $result = Setting::get('quiet_hours');

    expect($result)->toBeArray();
    expect($result['start'])->toBe('22:00');
    expect($result['end'])->toBe('07:00');
});

it('updates existing setting instead of duplicating', function () {
    Setting::set('booking_lead_time', 2);
    Setting::set('booking_lead_time', 24);

    $count = DB::table('settings')
        ->where('key', 'booking_lead_time')
        ->count();

    expect($count)->toBe(1);
    expect(Setting::get('booking_lead_time'))->toBe(24);
});
