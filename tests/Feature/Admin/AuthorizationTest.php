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
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
