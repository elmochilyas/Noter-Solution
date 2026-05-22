<?php

use App\Models\Client;
use App\Models\MagicLink;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;

uses()->group('dusk');

beforeEach(function () {
    App::setLocale('fr');

    Client::factory()->create([
        'email' => 'test@example.com',
        'phone' => '+212612345678',
        'full_name' => 'Test Client',
        'preferred_locale' => 'fr',
    ]);
});

test('magic link round trip logs in client and redirects to dashboard', function () {
    $this->browse(function (Browser $browser) {
        $token = Str::random(64);

        $browser->visit('/fr/portal/login')
            ->waitForText(__('auth.magic_link_title'))
            ->assertInputValue('email', '')
            ->type('email', 'test@example.com')
            ->storeSource('before-submit')
            ->press(__('auth.send_magic_link'))
            ->pause(3000)
            ->storeSource('after-send-source')
            ->waitForText(__('auth.magic_link_sent_title'), 10)
            ->assertSee(__('auth.magic_link_sent_title'));

        MagicLink::create([
            'client_id' => Client::where('email', 'test@example.com')->first()->id,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addMinutes(15),
        ]);

        $url = URL::temporarySignedRoute(
            'portal.login.verify',
            now()->addMinutes(15),
            ['token' => $token, 'email' => 'test@example.com', 'locale' => 'fr'],
        );

        $browser->visit($url)
            ->waitForLocation('/fr/portal/dashboard')
            ->assertPathIs('/fr/portal/dashboard');
    });
});

test('guest sees login page', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/fr/portal/login')
            ->pause(2000)
            ->storeSource('login-source-2')
            ->assertSeeIn('h1', __('auth.magic_link_title'));
    });
});

test('authenticated client can logout', function () {
    $token = Str::random(64);
    $client = Client::where('email', 'test@example.com')->first();

    MagicLink::create([
        'client_id' => $client->id,
        'token_hash' => hash('sha256', $token),
        'expires_at' => now()->addMinutes(15),
    ]);

    $url = URL::temporarySignedRoute(
        'portal.login.verify',
        now()->addMinutes(15),
        ['token' => $token, 'email' => 'test@example.com', 'locale' => 'fr'],
    );

    $this->browse(function (Browser $browser) use ($url) {
        $browser->visit($url)
            ->waitForLocation('/fr/portal/dashboard')
            ->assertPathIs('/fr/portal/dashboard')
            ->press(__('portal.logout'))
            ->waitForLocation('/fr/portal/login')
            ->assertPathIs('/fr/portal/login');
    });
});
