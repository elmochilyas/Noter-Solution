<?php

use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->client = Client::factory()->create();
});

test('client can view deletion confirmation page', function () {
    $this->actingAs($this->client, 'client')
        ->get('/fr/portal/account/delete')
        ->assertStatus(200)
        ->assertSee(__('portal.delete_account_title'));
});

test('client can delete account with correct confirmation', function () {
    $this->actingAs($this->client, 'client')
        ->post('/fr/portal/account/delete', ['confirmation' => 'SUPPRIMER'])
        ->assertRedirect('/fr/portal/login');

    $this->client->refresh();
    expect($this->client->full_name)->toContain('Supprimé');
    expect($this->client->email)->toContain('@anonymized');
    expect($this->client->phone)->toBeNull();
});

test('client cannot delete account with wrong confirmation', function () {
    $this->actingAs($this->client, 'client')
        ->post('/fr/portal/account/delete', ['confirmation' => 'wrong'])
        ->assertSessionHasErrors('confirmation');

    $this->client->refresh();
    expect($this->client->full_name)->not->toContain('Supprimé');
});

test('guest cannot access deletion page', function () {
    $this->get('/fr/portal/account/delete')->assertRedirect();
});
