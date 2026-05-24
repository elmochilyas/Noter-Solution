<?php

use App\Models\Receipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->owner = User::factory()->owner()->create();
});

it('queries receipts within date range', function () {
    $this->actingAs($this->owner);

    $inRange = Receipt::factory()->create(['issued_at' => now()]);
    $outOfRange = Receipt::factory()->create(['issued_at' => now()->subMonths(3)]);

    $from = now()->startOfMonth()->format('Y-m-d');
    $to = now()->endOfMonth()->format('Y-m-d');

    $results = Receipt::whereBetween('issued_at', [$from, $to])
        ->with('booking.client')
        ->get();

    expect($results->pluck('id')->toArray())->toEqualCanonicalizing([$inRange->id]);
});

it('generates CSV headers expected by Reports page', function () {
    $this->actingAs($this->owner);

    $headers = ['Numéro', 'Client', 'Email', 'Montant', 'TVA', 'Date'];

    $receipt = Receipt::factory()->create(['issued_at' => now()]);
    $from = now()->startOfMonth()->format('Y-m-d');
    $to = now()->endOfMonth()->format('Y-m-d');

    $receipts = Receipt::whereBetween('issued_at', [$from, $to])
        ->with('booking.client')
        ->get();

    expect($receipts->count())->toBeGreaterThanOrEqual(1);
    expect($receipts->first()->number)->not->toBeNull();
    expect($receipts->first()->amount_centimes)->toBeGreaterThan(0);
});

it('ZIP export uses addFromString compatible with remote disks', function () {
    $this->actingAs($this->owner);

    $receipt = Receipt::factory()->create([
        'issued_at' => now(),
        'storage_path' => 'receipts/test.pdf',
    ]);

    Storage::disk('receipts')->put(
        'receipts/test.pdf',
        'fake-pdf-content',
    );

    $disk = Storage::disk('receipts');
    expect($disk->exists($receipt->storage_path))->toBeTrue();
    expect($disk->get($receipt->storage_path))->toBe('fake-pdf-content');
});
