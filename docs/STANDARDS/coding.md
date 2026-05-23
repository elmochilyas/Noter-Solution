# Coding Standards

## Languages and versions

- **PHP:** 8.3+ — use typed properties, readonly properties, enums, first-class callable syntax, `match`, named arguments.
- **JavaScript:** ES2022+. Prefer Alpine.js inside Livewire components over raw JS.
- **CSS:** Tailwind utility classes only. No custom CSS files except for global tokens and one `app.css` entry point.

## File layout

```
app/
├── Console/Commands/
├── Enums/                              # one enum per file, PascalCase
├── Events/
├── Exceptions/
├── Filament/
│   ├── Resources/<Model>Resource.php
│   ├── Pages/
│   ├── Widgets/
│   └── Forms/Components/               # custom form components
├── Http/
│   ├── Controllers/
│   │   ├── Auth/                       # magic-link controllers
│   │   ├── Public/                     # marketing pages
│   │   ├── Portal/                     # client portal
│   │   └── Webhooks/                   # Stripe, Twilio
│   ├── Middleware/
│   └── Requests/                       # FormRequest classes
├── Jobs/
├── Listeners/
├── Livewire/
│   ├── Booking/
│   ├── Chatbot/
│   ├── Portal/
│   └── Public/
├── Mail/
├── Models/
├── Notifications/
├── Policies/
├── Providers/
├── Rules/                              # custom validation rules
├── Services/
│   ├── Booking/
│   ├── Chatbot/
│   ├── Document/
│   ├── Notification/
│   └── Payment/
└── Support/                            # value objects, helpers
    ├── ValueObjects/
    └── Concerns/                       # traits
```

## Naming

| Type | Convention | Example |
|---|---|---|
| Class | `PascalCase`, singular | `BookingService`, `Consultation` |
| Interface | `PascalCase`, no `I` prefix | `PaymentGateway` not `IPaymentGateway` |
| Trait | `PascalCase`, often ends in `-able` | `HasTranslations` |
| Enum | `PascalCase`, singular | `BookingStatus` |
| Method | `camelCase`, verb-first | `confirmBooking()` |
| Variable | `camelCase` | `$pendingBookings` |
| Constant | `SCREAMING_SNAKE_CASE` | `MAX_UPLOAD_SIZE_MB` |
| DB table | `snake_case`, plural | `bookings`, `consultation_plans` |
| DB column | `snake_case`, singular | `started_at`, `client_id` |
| Pivot table | alphabetical singular pair | `booking_document` |
| Route name | `dot.notation`, plural resource | `bookings.create`, `portal.bookings.show` |
| Translation key | `dot.notation` matching feature | `booking.confirmation.title` |
| Blade view | `kebab-case` matching route | `public/services/family.blade.php` |

## Project layering

The codebase is layered. Calls go **down only**.

```
HTTP Layer  →  Controllers, Livewire components, Filament resources
    ↓
Application →  Services, Jobs, Listeners, Form Requests
    ↓
Domain      →  Models, Enums, Value Objects, Policies
    ↓
Infrastructure → External clients (Stripe, Cerebras, Twilio, Supabase)
```

**Rules:**
- Controllers / Livewire components **never** contain business logic — they orchestrate by calling services.
- Services hold business logic. They are stateless and injected.
- Models hold persistence and minimal entity logic (accessors, casts, relations). **No business rules in models.**
- External SDK calls live behind a service interface. Never call `Stripe\Charge::create()` directly from a controller.

## Controller rules

- Maximum 5 methods per controller. If you need more, split.
- Each method ≤ 15 lines. If longer, extract to a service.
- Use FormRequest for any controller that accepts input.
- Return either `view()`, `redirect()`, or a `JsonResponse`. Never `echo` or `dd()`.

Good:

```php
public function store(StoreBookingRequest $request, BookingService $bookings): RedirectResponse
{
    $booking = $bookings->create($request->toBookingData());

    return to_route('booking.payment', $booking);
}
```

Bad:

```php
public function store(Request $request)
{
    $data = $request->all();
    // ... 50 lines of validation, DB writes, email sending, payment intent creation
}
```

## Service rules

- Stateless. No `protected` state on the service except injected dependencies.
- One responsibility per service. `BookingService` does bookings; it doesn't send notifications directly — it dispatches a job.
- Methods are verb-first and return value objects or models, not arrays.
- Use DTOs / value objects for inputs, not associative arrays.

```php
final class BookingService
{
    public function __construct(
        private readonly ConsultationPlanRepository $plans,
        private readonly AvailabilityService $availability,
        private readonly Dispatcher $events,
    ) {}

    public function create(BookingData $data): Booking
    {
        $this->availability->assertSlotIsFree($data->slot);

        $booking = DB::transaction(fn () => Booking::createFromData($data));

        $this->events->dispatch(new BookingCreated($booking));

        return $booking;
    }
}
```

## Model rules

- Always declare `$fillable` explicitly. **Never use `$guarded = []`.**
- Declare all `$casts` including dates, enums, JSON.
- Use enums for any column with a fixed set of values.
- Relationships are typed: `public function client(): BelongsTo`.
- Use `factory` for tests; never insert via direct SQL in tests.
- Soft delete only models that the user can "remove" but legal retention requires keeping (bookings, payments, documents).

## Value Objects

Use a value object whenever a primitive carries domain meaning:

- `MoroccanPhoneNumber` — validates and normalizes phone format
- `MoneyMad` — handles currency arithmetic in MAD (smallest unit = centime)
- `BookingReference` — generates and validates `SBA-XXXXXX` references
- `TimeSlot` — start, end, timezone (`Africa/Casablanca`)

```php
final readonly class MoneyMad
{
    public function __construct(public int $centimes)
    {
        if ($centimes < 0) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }
    }

    public static function fromDirhams(float $dirhams): self
    {
        return new self((int) round($dirhams * 100));
    }

    public function dirhams(): float
    {
        return $this->centimes / 100;
    }
}
```

## Enums

Always backed enums, with helper methods on the enum itself.

```php
enum BookingStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case NO_SHOW = 'no_show';

    public function label(): string
    {
        return __('booking.status.' . $this->value);
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::COMPLETED, self::CANCELLED, self::NO_SHOW], true);
    }
}
```

## Blade rules

- One Blade view = one purpose. No multi-page mega-views.
- Use `<x-component>` for any markup repeated more than twice.
- Prefer `@props` on components over passing arrays.
- Never inline PHP logic for business rules — push it into the controller or view model.
- Always `e()` user data — or use `{{ }}` (auto-escapes). Use `{!! !!}` only for known-safe HTML and document why.

## Livewire rules

- One Livewire component = one screen or one widget.
- All public properties are typed.
- Use form objects (`Livewire\Form`) for multi-field forms.
- Validation rules in `rules()` method, return types declared.
- Avoid `wire:model.live` on free-text inputs — use `wire:model.blur` or `.debounce.500ms`.
- Mark long-running methods that should not block the UI with `wire:loading` indicators.
- Loading states are required on every interactive action.

## Tailwind rules

- Compose classes top-down: layout → sizing → spacing → color → typography → state.
- Long class lists go on multiple lines using `class="..."` with PHP string concat or `@class()` directive.
- Use the design tokens from `tailwind.config.js`, never hex colors inline.
- Extract repeated class clusters to a component, not to an `@apply` rule (`@apply` is a last resort).

## Comments and docblocks

- Self-documenting code first; comments only when intent isn't obvious from names.
- Docblocks required on:
  - Public methods of services and value objects (purpose + thrown exceptions)
  - Any method with non-obvious parameter semantics
  - Migrations describing what they do beyond the obvious
- No "what" comments — code shows the what. Only "why" comments.

Good:

```php
// We charge the deposit immediately because Moroccan cards
// often fail on delayed authorization. See ARCHITECTURE/payments.md.
$gateway->charge($intent);
```

Bad:

```php
// Charge the gateway
$gateway->charge($intent);
```

## Clean code rules

- **Functions do one thing.** If you need to write "and" in the method name, split.
- **No magic numbers.** `$expiresAt = now()->addMinutes(15);` → `$expiresAt = now()->add(BookingHold::DURATION);`
- **Guard clauses over nested ifs.** Return early, fail fast.
- **No flag arguments.** `createBooking(true)` is unreadable. Use two methods or an enum parameter.
- **Symmetry.** If you have `start()`, you probably need `stop()`. If you have `create()`, you probably need `cancel()`.
- **Tell, don't ask.** Avoid `if ($x->getStatus() === 'foo') { $x->doFoo(); }` — instead `$x->doFoo()` should know whether to act.
- **Composition over inheritance.** Use traits sparingly; prefer service composition.

## Static analysis

- **Larastan** at level 8 — must pass on CI.
- **Laravel Pint** with the strict ruleset — auto-formatted on pre-commit.
- **Rector** runs in dry-run mode in CI for early warnings.
- All three are required-to-pass in CI.

## Imports

- One import per line.
- Imports sorted alphabetically by tooling.
- No wildcard imports.
- Prefer `use App\Models\Booking;` over fully-qualified names in code.

## Error handling

- Catch only what you can handle. Let everything else propagate to the framework.
- Domain errors throw custom exceptions extending `App\Exceptions\DomainException`.
- Never `catch (\Throwable $e) {}` without re-throwing or logging with context.
- User-facing errors are rendered via Laravel's exception handler, never with raw stack traces.
