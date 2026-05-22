<?php

use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->client = Client::factory()->create(['preferred_locale' => 'fr', 'preferred_channel' => 'email']);
});

test('client can view preferences page', function () {
    $this->actingAs($this->client, 'client')
        ->get('/fr/portal/preferences')
        ->assertStatus(200)
        ->assertSee(__('portal.preferences_title'));
});

test('client can update preferences', function () {
    $this->actingAs($this->client, 'client')
        ->post('/fr/portal/preferences', [
            'preferred_locale' => 'ar',
            'preferred_channel' => 'sms',
            'phone' => '+212612345678',
            'full_name' => $this->client->full_name,
        ])
        ->assertRedirect();

    $this->client->refresh();
    expect($this->client->preferred_locale)->toBe('ar');
    expect($this->client->preferred_channel)->toBe('sms');
});

test('guest cannot access preferences', function () {
    $this->get('/fr/portal/preferences')->assertRedirect();
});
