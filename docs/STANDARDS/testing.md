# Testing Standards

## Testing pyramid

```
                    /\
                   /  \  Browser (Dusk)     ~15 tests
                  /----\
                 / Feat \ Feature tests    ~150 tests
                /--------\
               /   Unit   \ Unit tests     ~300 tests
              /------------\
```

- Most tests are fast unit + feature tests using Pest.
- Dusk for end-to-end flows that involve real browser behavior (booking flow with Stripe Elements, magic-link email click).
- No test should hit external APIs in CI — everything is faked / mocked / recorded.

## Coverage targets

| Layer | Target | Hard floor |
|---|---|---|
| Services (`app/Services/`) | 95% | 85% |
| Models | 80% | 70% |
| Controllers + Livewire | 80% | 70% |
| Filament resources | 60% | 50% |
| Overall | 80% | 70% |

Coverage is measured by Pest's XDebug coverage driver in CI. A PR that drops overall coverage below 70% fails CI.

## Pest configuration

```php
// tests/Pest.php
uses(Tests\TestCase::class, RefreshDatabase::class)->in('Feature');
uses(Tests\TestCase::class)->in('Unit');
uses(DuskTestCase::class)->in('Browser');

expect()->extend('toBeMoneyMad', function (int $centimes) {
    return $this->toBeInstanceOf(\App\Support\ValueObjects\MoneyMad::class)
        ->and($this->value->centimes)->toBe($centimes);
});
```

## What gets tested

### Always tested
- Every service method (happy path + at least one failure)
- Every policy (authorized + unauthorized for each role)
- Every form request validation rule
- Every value object construction and method
- Every enum helper method
- Every job's handle method
- Every observer side-effect
- Every webhook handler (signature valid + invalid)
- Every Livewire component's public methods

### Always tested via feature tests
- Every public route returns expected status code
- Every controller redirect target
- Every email is dispatched in the right scenario
- Every notification (email/SMS/WhatsApp) is dispatched

### Always tested via Dusk
- The booking flow from calendar → payment → confirmation
- Magic-link login round trip
- Admin login with 2FA
- Document upload in the client portal

### Not tested
- Framework code (Laravel itself)
- Filament internals (only our customizations)
- Third-party SDKs (only our adapters around them)

## Test naming

- Pest test names are full sentences in present tense:
  ```php
  it('creates a booking when the slot is available', ...);
  it('rejects a booking when the slot is taken', ...);
  ```
- Group related tests with `describe()`:
  ```php
  describe('BookingService::create', function () {
      it('creates a booking on a free slot', ...);
      it('fails when the slot is past', ...);
  });
  ```

## Factories

- Every model has a factory.
- Factories produce realistic, fully-valid data (no nulls in required fields).
- Use states for variations:
  ```php
  Booking::factory()->confirmed()->online()->forPlan('standard-online')->create();
  ```
- Factories must not call external services — all randomness is local.

## Fixtures and database

- Use `RefreshDatabase` in feature tests by default.
- Use `LazilyRefreshDatabase` if many tests don't touch DB.
- Test database is SQLite in CI (fast); PostgreSQL locally to match prod for any test touching pgvector or PostGIS.
- Use `Date::setTestNow()` / `Carbon::setTestNow()` for time-sensitive tests — never `sleep()`.

## Mocking external services

- **Stripe:** use `Stripe\Stripe::setApiBase()` to point at a local mock, or mock the gateway interface.
- **Cerebras API:** mock the `LlmClient` interface; never call the real API in tests.
- **Twilio:** mock the `SmsGateway` / `WhatsappGateway` interfaces.
- **Email:** `Mail::fake()` then assert with `Mail::assertSent()`.
- **Notifications:** `Notification::fake()` then assert dispatched.
- **Events:** `Event::fake()` then `Event::assertDispatched()`.
- **Jobs:** `Queue::fake()` then `Queue::assertPushed()`.
- **Storage:** `Storage::fake('supabase')` then assert uploads.
- **HTTP:** `Http::fake()` with response definitions.

## Authorization tests

For every protected route or action, write a paired test:

```php
it('allows the booking owner to view their booking', function () {
    $client = Client::factory()->create();
    $booking = Booking::factory()->forClient($client)->create();

    actingAs($client)->get(route('portal.bookings.show', $booking))
        ->assertOk();
});

it('forbids a different client from viewing the booking', function () {
    $owner = Client::factory()->create();
    $other = Client::factory()->create();
    $booking = Booking::factory()->forClient($owner)->create();

    actingAs($other)->get(route('portal.bookings.show', $booking))
        ->assertForbidden();
});
```

## Dusk tests

- Run against a real browser via headless Chrome.
- Each test resets the database and seeds the minimum needed.
- Stripe is in test mode; use Stripe's test card `4242 4242 4242 4242`.
- Time-sensitive flows use `now()->setTestNow()` server-side and assert against deterministic dates.

```php
test('a client can book and pay for a standard online consultation', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/consultation')
            ->click('@plan-standard-online')
            ->waitFor('@calendar')
            ->click('@slot-10-30')
            ->click('@continue')
            ->type('@name', 'Karim Test')
            ->type('@email', 'karim@example.com')
            ->type('@phone', '0612345678')
            ->click('@continue')
            ->withinFrame('@stripe-card', function ($frame) {
                $frame->type('cardnumber', '4242424242424242')
                      ->type('exp-date', '1234')
                      ->type('cvc', '123');
            })
            ->click('@confirm-and-pay')
            ->waitForLocation('/book/success')
            ->assertSee('Votre rendez-vous est confirmé');
    });
});
```

## Test data — Moroccan context

- Phone numbers use Moroccan format: `+212 6XX XXX XXX`.
- Names use Moroccan names: Karim, Fatima, Hassan, Aïcha, etc.
- Addresses use real Moroccan locality names: Agadir, Marrakech, Rabat, Casablanca, Bensergao.
- Dates account for `Africa/Casablanca` timezone.
- Currency: MAD only.

## CI requirements

The CI pipeline must run:

1. `composer install --no-dev` (sanity check prod dependencies build)
2. `composer install` (dev dependencies for testing)
3. `npm ci && npm run build`
4. `php artisan key:generate --env=testing`
5. `php artisan migrate --env=testing`
6. `vendor/bin/pint --test` (formatting check)
7. `vendor/bin/phpstan analyse` (Larastan level 8)
8. `vendor/bin/pest --coverage --min=70` (must pass with 70% min)
9. `npm run lint`
10. `composer audit`
11. `npm audit --production`

Dusk runs in a separate job on a Linux runner with Chrome headless.

## When tests fail

- A failing test in CI is a blocker.
- Never `skip()` a test to make CI green. Either fix it or delete it with a justification in the PR description.
- Flaky tests are escalated immediately and either fixed within the same PR or removed entirely.

## Performance test gates

- Every PR runs `php artisan test --filter=Performance` which contains tests asserting query counts and response times for critical paths.
- These tests catch N+1 regressions automatically.
