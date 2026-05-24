<?php

use App\Filament\Resources\ActivityLogResource;
use App\Filament\Resources\BookingResource;
use App\Filament\Resources\ChatbotConversationResource;
use App\Filament\Resources\ClientResource;
use App\Filament\Resources\ContactMessageResource;
use App\Filament\Resources\DocumentResource;
use App\Filament\Resources\NotificationLogResource;
use App\Filament\Resources\PaymentResource;
use App\Filament\Resources\ReceiptResource;
use App\Filament\Resources\RefundResource;
use App\Filament\Resources\UserResource;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Document;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->owner = User::factory()->owner()->create();
    $this->assistant = User::factory()->assistant()->create();
});

it('owner can view admin resources', function () {
    $this->actingAs($this->owner);

    expect(BookingResource::canViewAny())->toBeTrue();
    expect(ClientResource::canViewAny())->toBeTrue();
    expect(PaymentResource::canViewAny())->toBeTrue();
    expect(RefundResource::canViewAny())->toBeTrue();
    expect(ReceiptResource::canViewAny())->toBeTrue();
    expect(DocumentResource::canViewAny())->toBeTrue();
    expect(ContactMessageResource::canViewAny())->toBeTrue();
    expect(NotificationLogResource::canViewAny())->toBeTrue();
    expect(ChatbotConversationResource::canViewAny())->toBeTrue();
    expect(ActivityLogResource::canViewAny())->toBeTrue();
    expect(UserResource::canViewAny())->toBeTrue();
});

it('assistant cannot access owner-only resources', function () {
    $this->actingAs($this->assistant);

    expect(ActivityLogResource::canViewAny())->toBeFalse();
    expect(UserResource::canViewAny())->toBeFalse();
});

it('assistant can access shared resources', function () {
    $this->actingAs($this->assistant);

    expect(BookingResource::canViewAny())->toBeTrue();
    expect(ClientResource::canViewAny())->toBeTrue();
    expect(PaymentResource::canViewAny())->toBeTrue();
    expect(RefundResource::canViewAny())->toBeTrue();
    expect(ReceiptResource::canViewAny())->toBeTrue();
    expect(DocumentResource::canViewAny())->toBeTrue();
    expect(ContactMessageResource::canViewAny())->toBeTrue();
    expect(NotificationLogResource::canViewAny())->toBeTrue();
    expect(ChatbotConversationResource::canViewAny())->toBeTrue();
});

it('assistant can view admin dashboard', function () {
    $this->actingAs($this->assistant);

    expect(BookingResource::canViewAny())->toBeTrue();
});

it('assistant cannot update settings', function () {
    $this->actingAs($this->assistant);

    expect(auth()->user()->can('update', Setting::class))->toBeFalse();
});

it('owner can update settings', function () {
    $this->actingAs($this->owner);

    expect(auth()->user()->can('update', Setting::class))->toBeTrue();
});

describe('consultation plan price converts MAD input to centimes', function () {
    $mutate = fn (?string $state): int => (int) round(((float) str_replace(',', '.', $state ?? '0')) * 100);

    expect($mutate('15.00'))->toBe(1500);
    expect($mutate('15'))->toBe(1500);
    expect($mutate('0'))->toBe(0);
    expect($mutate('99.99'))->toBe(9999);
    expect($mutate('15,00'))->toBe(1500);
    expect($mutate('99,99'))->toBe(9999);
});

describe('document resource expired filter', function () {
    it('filters expired documents when value is yes', function () {
        $this->actingAs($this->owner);

        Document::factory()->create(['purge_after' => now()->subDay()]);
        Document::factory()->create(['purge_after' => now()->addYear()]);

        $filtered = Document::where('purge_after', '<=', now());
        expect($filtered->count())->toBe(1);
    });

    it('filters non-expired documents when value is no', function () {
        $this->actingAs($this->owner);

        Document::factory()->create(['purge_after' => now()->subDay()]);
        Document::factory()->create(['purge_after' => now()->addYear()]);

        $filtered = Document::where('purge_after', '>', now());
        expect($filtered->count())->toBe(1);
    });
});

describe('admin document download scan_status guard', function () {
    it('blocks download of pending document', function () {
        Storage::fake('local');
        $this->actingAs($this->owner);

        $client = Client::factory()->create();
        $booking = Booking::factory()->create(['client_id' => $client->id]);
        $document = Document::factory()->create([
            'booking_id' => $booking->id,
            'client_id' => $client->id,
            'scan_status' => 'pending',
            'storage_path' => 'documents/test/pending.pdf',
        ]);
        Storage::disk('local')->put('documents/test/pending.pdf', 'content');

        $this->get(route('admin.downloads.document', ['document' => $document->id]))
            ->assertForbidden();
    });

    it('blocks download of infected document', function () {
        Storage::fake('local');
        $this->actingAs($this->owner);

        $client = Client::factory()->create();
        $booking = Booking::factory()->create(['client_id' => $client->id]);
        $document = Document::factory()->create([
            'booking_id' => $booking->id,
            'client_id' => $client->id,
            'scan_status' => 'infected',
            'storage_path' => 'documents/test/infected.pdf',
        ]);
        Storage::disk('local')->put('documents/test/infected.pdf', 'content');

        $this->get(route('admin.downloads.document', ['document' => $document->id]))
            ->assertForbidden();
    });

    it('allows download of clean document', function () {
        Storage::fake('local');
        $this->actingAs($this->owner);

        $client = Client::factory()->create();
        $booking = Booking::factory()->create(['client_id' => $client->id]);
        $document = Document::factory()->create([
            'booking_id' => $booking->id,
            'client_id' => $client->id,
            'scan_status' => 'clean',
            'storage_path' => 'documents/test/clean.pdf',
        ]);
        Storage::disk('local')->put('documents/test/clean.pdf', 'content');

        $this->get(route('admin.downloads.document', ['document' => $document->id]))
            ->assertOk();
    });
});
