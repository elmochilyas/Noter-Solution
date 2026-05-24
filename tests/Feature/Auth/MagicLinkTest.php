<?php

use App\Models\Client;
use App\Models\MagicLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    Event::fake();
    RateLimiter::clear('magic-link:ip:127.0.0.1');
});

test('guest can view magic link form', function () {
    $this->get('/fr/portal/login')
        ->assertStatus(200)
        ->assertSee(__('auth.magic_link_title'));
});

test('guest can submit magic link email', function () {
    $client = Client::factory()->create(['email' => 'test@example.com']);

    $this->post('/fr/portal/login', ['email' => 'test@example.com'])
        ->assertRedirect('/fr/portal/login/sent');

    $this->assertDatabaseHas('magic_links', [
        'client_id' => $client->id,
        'consumed_at' => null,
    ]);
});

test('submitting unknown email still shows sent page', function () {
    $this->post('/fr/portal/login', ['email' => 'unknown@example.com'])
        ->assertRedirect('/fr/portal/login/sent');
});

test('new client created via magic link has no placeholder phone', function () {
    $this->post('/fr/portal/login', ['email' => 'newclient@example.com'])
        ->assertRedirect('/fr/portal/login/sent');

    $client = Client::where('email', 'newclient@example.com')->first();
    expect($client)->not->toBeNull();
    expect($client->phone)->toBeNull();
});

test('magic link sent page renders', function () {
    $this->get('/fr/portal/login/sent')
        ->assertStatus(200)
        ->assertSee(__('auth.link_sent_title'));
});

test('guest can login with valid magic link', function () {
    $client = Client::factory()->create(['email' => 'test@example.com']);

    $token = Str::random(64);
    MagicLink::create([
        'client_id' => $client->id,
        'token_hash' => hash('sha256', $token),
        'expires_at' => now()->addMinutes(15),
    ]);

    $signedUrl = URL::temporarySignedRoute(
        'portal.login.verify',
        now()->addMinutes(15),
        ['token' => $token, 'email' => $client->email, 'locale' => 'fr'],
    );

    $this->get($signedUrl)
        ->assertRedirect('/fr/portal/dashboard');

    $this->assertAuthenticatedAs($client, 'client');
});

test('consumed magic link cannot be reused', function () {
    $client = Client::factory()->create(['email' => 'test@example.com']);

    $token = Str::random(64);
    MagicLink::create([
        'client_id' => $client->id,
        'token_hash' => hash('sha256', $token),
        'expires_at' => now()->addMinutes(15),
        'consumed_at' => now()->subMinute(),
    ]);

    $signedUrl = URL::temporarySignedRoute(
        'portal.login.verify',
        now()->addMinutes(15),
        ['token' => $token, 'email' => $client->email, 'locale' => 'fr'],
    );

    $this->get($signedUrl)
        ->assertRedirect('/fr/portal/login');
});

test('expired magic link returns error', function () {
    $client = Client::factory()->create(['email' => 'test@example.com']);

    $token = Str::random(64);
    MagicLink::create([
        'client_id' => $client->id,
        'token_hash' => hash('sha256', $token),
        'expires_at' => now()->subMinute(),
    ]);

    $signedUrl = URL::temporarySignedRoute(
        'portal.login.verify',
        now()->subMinutes(5),
        ['token' => $token, 'email' => $client->email, 'locale' => 'fr'],
    );

    $this->get($signedUrl)
        ->assertRedirect('/fr/portal/login');
});

test('authenticated client sees dashboard', function () {
    $client = Client::factory()->create();

    $this->actingAs($client, 'client')
        ->get('/fr/portal/dashboard')
        ->assertStatus(200)
        ->assertSee(__('portal.dashboard_title'));
});

test('dashboard shows translated open text in Arabic', function () {
    $client = Client::factory()->create(['preferred_locale' => 'ar']);

    $this->actingAs($client, 'client')
        ->get('/ar/portal/dashboard')
        ->assertDontSee('Ouvrir');
});

test('guest cannot access dashboard', function () {
    $this->get('/fr/portal/dashboard')
        ->assertRedirect();
});

test('client can logout', function () {
    $client = Client::factory()->create();

    $this->actingAs($client, 'client')
        ->post('/fr/portal/logout')
        ->assertRedirect('/fr/portal/login');

    $this->assertGuest('client');
});
