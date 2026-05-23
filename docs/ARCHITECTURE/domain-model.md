# Domain Model

The vocabulary of the codebase. Every concept here is reflected in the code: a class, an enum, or a value object.

## Aggregate roots

These are the entities other parts of the system orbit around.

### Client

A person who has booked at least once.

- Identified by email.
- May have many bookings.
- Receives notifications via email, SMS, WhatsApp.
- Logs in via magic link only.

```php
class Client extends Model
{
    public function bookings(): HasMany { ... }
    public function documents(): HasMany { ... }
    public function preferredPhoneNumber(): MoroccanPhoneNumber { ... }
}
```

### Booking

A scheduled consultation. Central aggregate.

- Belongs to a Client.
- References a ConsultationPlan.
- Has zero or one Payment (free orientation has none).
- Has many Documents.
- Has zero or one Receipt.
- Goes through a strict lifecycle (see Booking lifecycle below).

### ConsultationPlan

One of the four offered plans. Configuration, not transactional.

- Identified by `slug`.
- Has translated name, description, features.
- Has a price (or 0 for free orientation).
- Has a format (`online`, `in_office`, or `both`).
- Has a duration in minutes.

### Payment

A money movement.

- Belongs to a Booking.
- Created by a PaymentGateway driver.
- Status moves through `pending` → `succeeded` / `failed`.
- May be refunded one or more times.

## Booking lifecycle

```
                       ┌─────────────────┐
                       │ pending_payment │
                       └────────┬────────┘
                                │ payment succeeds OR cash-at-office chosen
                                ▼
                       ┌────────────────┐
                       │   confirmed    │◄────────────┐
                       └────────┬───────┘             │
                                │                     │ reschedule (creates
              ┌─────────────────┼─────────────────┐   │ new pending, cancels
              │                 │                 │   │ this one)
              ▼                 ▼                 ▼   │
       ┌────────────┐    ┌────────────┐    ┌─────────────┐
       │ completed  │    │  no_show   │    │  cancelled  │
       └────────────┘    └────────────┘    └─────────────┘
                       (terminal states)
```

Status transition rules:
- `pending_payment → confirmed`: on successful payment or admin confirms cash-at-office
- `pending_payment → cancelled`: client abandons or payment times out
- `confirmed → completed`: admin marks after the consultation
- `confirmed → no_show`: admin marks if client didn't show
- `confirmed → cancelled`: client or admin cancels
- Reschedule: new pending_payment booking created, old one cancelled with reason `rescheduled`

Enforced by:
```php
class BookingStatusTransition
{
    public static function assertCanTransition(BookingStatus $from, BookingStatus $to): void
    {
        match ([$from, $to]) {
            [BookingStatus::PENDING_PAYMENT, BookingStatus::CONFIRMED],
            [BookingStatus::PENDING_PAYMENT, BookingStatus::CANCELLED],
            [BookingStatus::CONFIRMED, BookingStatus::COMPLETED],
            [BookingStatus::CONFIRMED, BookingStatus::NO_SHOW],
            [BookingStatus::CONFIRMED, BookingStatus::CANCELLED] => null,
            default => throw new InvalidBookingTransition($from, $to),
        };
    }
}
```

## Enums

```php
enum BookingStatus: string {
    case PENDING_PAYMENT = 'pending_payment';
    case CONFIRMED = 'confirmed';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case NO_SHOW = 'no_show';
}

enum BookingFormat: string {
    case ONLINE = 'online';
    case IN_OFFICE = 'in_office';
}

enum PlanFormat: string {
    case ONLINE = 'online';
    case IN_OFFICE = 'in_office';
    case BOTH = 'both';
}

enum ServiceCategory: string {
    case FAMILY = 'family';
    case REAL_ESTATE = 'real_estate';
    case FINANCIAL = 'financial';
    case CONTRACTS = 'contracts';
}

enum PaymentStatus: string {
    case PENDING = 'pending';
    case SUCCEEDED = 'succeeded';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';
    case PARTIALLY_REFUNDED = 'partially_refunded';
}

enum PaymentGatewayName: string {
    case STRIPE = 'stripe';
    case CMI = 'cmi';
    case CASH = 'cash';
}

enum DocumentScanStatus: string {
    case PENDING = 'pending';
    case CLEAN = 'clean';
    case INFECTED = 'infected';
}

enum NotificationChannel: string {
    case EMAIL = 'email';
    case SMS = 'sms';
    case WHATSAPP = 'whatsapp';
}

enum UserRole: string {
    case OWNER = 'owner';
    case ASSISTANT = 'assistant';
}

enum ChatbotIntent: string {
    case GREETING = 'greeting';
    case FAQ_QUERY = 'faq_query';
    case BOOKING_INTENT = 'booking_intent';
    case PRICING_QUERY = 'pricing_query';
    case ESCALATION = 'escalation';
    case OUT_OF_SCOPE = 'out_of_scope';
}

enum Locale: string {
    case AR = 'ar';
    case FR = 'fr';
}
```

## Value objects

### MoroccanPhoneNumber

```php
final readonly class MoroccanPhoneNumber implements Stringable
{
    private function __construct(public string $e164) {}

    public static function fromInput(string $input): self
    {
        // Accept: "0612345678", "+212612345678", "212 612 345 678", "06 12 34 56 78"
        // Returns: "+212612345678"
    }

    public function national(): string { /* "06 12 34 56 78" */ }
    public function whatsapp(): string { /* "whatsapp:+212612345678" */ }
    public function __toString(): string { return $this->e164; }
}
```

### MoneyMad

```php
final readonly class MoneyMad
{
    public function __construct(public int $centimes)
    {
        if ($centimes < 0) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }
    }

    public static function zero(): self { return new self(0); }
    public static function fromDirhams(float $dirhams): self { ... }
    public function add(self $other): self { ... }
    public function subtract(self $other): self { ... }
    public function isZero(): bool { ... }
    public function dirhams(): float { ... }
    public function formatted(Locale $locale): string { ... }
}
```

### BookingReference

```php
final readonly class BookingReference
{
    public function __construct(public string $value)
    {
        if (! preg_match('/^SBA-[A-Z0-9]{6}$/', $value)) {
            throw new InvalidArgumentException("Invalid reference: {$value}");
        }
    }

    public static function generate(): self
    {
        // Generates SBA-XXXXXX with 6 base32-like chars, avoiding 0/O/1/I
        return new self('SBA-' . Str::upper(Str::random(6)));
    }

    public function __toString(): string { return $this->value; }
}
```

### TimeSlot

```php
final readonly class TimeSlot
{
    public function __construct(
        public CarbonImmutable $startsAt,
        public CarbonImmutable $endsAt,
    ) {
        if ($startsAt->greaterThanOrEqualTo($endsAt)) {
            throw new InvalidArgumentException('Slot end must be after start');
        }
    }

    public function durationMinutes(): int { ... }
    public function overlaps(self $other): bool { ... }
    public function contains(CarbonImmutable $moment): bool { ... }
    public function isPast(): bool { return $this->endsAt->isPast(); }
}
```

### BookingData (input DTO)

```php
final readonly class BookingData
{
    public function __construct(
        public int $consultationPlanId,
        public ServiceCategory $serviceCategory,
        public BookingFormat $format,
        public TimeSlot $slot,
        public string $clientFullName,
        public string $clientEmail,
        public MoroccanPhoneNumber $clientPhone,
        public string $description,
        public Locale $locale,
        /** @var UploadedFile[] */
        public array $documents = [],
    ) {}
}
```

## Services

Services hold business logic. Stateless, injected, one responsibility per service.

### BookingService

```php
final class BookingService
{
    public function createPending(BookingData $data): Booking { ... }
    public function confirm(Booking $booking, ?Payment $payment = null): void { ... }
    public function complete(Booking $booking): void { ... }
    public function markNoShow(Booking $booking): void { ... }
    public function cancel(Booking $booking, string $reason, User|Client $by): void { ... }
    public function reschedule(Booking $booking, TimeSlot $newSlot): Booking { ... }
}
```

### AvailabilityService

```php
final class AvailabilityService
{
    /** @return iterable<TimeSlot> */
    public function availableSlots(
        CarbonImmutable $from,
        CarbonImmutable $to,
        ConsultationPlan $plan,
        BookingFormat $format,
    ): iterable { ... }

    public function assertSlotIsFree(TimeSlot $slot, BookingFormat $format): void { ... }
    public function holdSlot(TimeSlot $slot, string $sessionId, ?Client $client = null): BookingHold { ... }
}
```

### PaymentService

```php
final class PaymentService
{
    public function __construct(private readonly PaymentGateway $gateway) {}

    public function createIntent(Booking $booking): PaymentIntent { ... }
    public function confirmFromWebhook(WebhookEvent $event): void { ... }
    public function refund(Payment $payment, MoneyMad $amount, string $reason, User $by): Refund { ... }
}
```

### ChatbotService

```php
final class ChatbotService
{
    public function startConversation(string $sessionId, Locale $locale): ChatbotConversation { ... }
    public function respondTo(ChatbotConversation $conv, string $userMessage): iterable { ... }
    public function escalateToHuman(ChatbotConversation $conv): void { ... }
}
```

### DocumentService

```php
final class DocumentService
{
    public function attachToBooking(Booking $booking, UploadedFile $file): Document { ... }
    public function temporaryUrl(Document $doc, int $minutes = 5): string { ... }
    public function delete(Document $doc, User|Client $by): void { ... }
    public function markScanResult(Document $doc, DocumentScanStatus $status): void { ... }
}
```

### NotificationService

Orchestrates multi-channel notifications. Implements `notifications.md` architecture.

```php
final class NotificationService
{
    public function send(
        string $templateKey,
        Client|User|MoroccanPhoneNumber|string $recipient,
        array $data = [],
        array $channels = [NotificationChannel::EMAIL],
    ): void { ... }
}
```

### ReceiptService

```php
final class ReceiptService
{
    public function generate(Payment $payment): Receipt { ... }
    public function temporaryUrl(Receipt $receipt, int $minutes = 5): string { ... }
}
```

## Domain events

Events live in `app/Events/`. Listeners are queued unless noted.

| Event | Fired when | Listeners |
|---|---|---|
| `BookingCreated` | Pending booking persisted | None — confirmation comes after payment |
| `BookingConfirmed` | Payment succeeds or admin confirms cash | SendConfirmationEmail, SendConfirmationSms, SendConfirmationWhatsapp, ScheduleReminders |
| `BookingCancelled` | Cancellation persisted | SendCancellationEmail, ReleaseHold, IssueRefundIfApplicable |
| `BookingCompleted` | Admin marks completed | SendThankYouEmail (with review prompt) |
| `BookingNoShow` | Admin marks no-show | (Audit log only) |
| `BookingRescheduled` | Reschedule persisted | SendRescheduleConfirmation, CancelOldReminders, ScheduleNewReminders |
| `PaymentSucceeded` | Webhook confirms charge | ConfirmBooking, GenerateReceipt |
| `PaymentFailed` | Webhook reports failure | NotifyClientOfFailure |
| `RefundIssued` | Refund completes | SendRefundEmail |
| `DocumentUploaded` | Client uploads | QueueVirusScan |
| `DocumentScanCompleted` | Virus scan finishes | NotifyAdminIfInfected |
| `ChatbotConversationEscalated` | Bot hands off to human | NotifyAdmin |
| `ContactMessageReceived` | Contact form submitted | NotifyAdmin |
| `MagicLinkRequested` | Client requests login | SendMagicLinkEmail |

## Repositories (light pattern)

Repositories abstract complex queries. Simple queries stay on the model.

```php
interface ConsultationPlanRepository
{
    public function findBySlug(string $slug): ?ConsultationPlan;
    public function active(): Collection;
    public function recommended(): ?ConsultationPlan;
}

final class EloquentConsultationPlanRepository implements ConsultationPlanRepository { ... }
```

Repositories are interfaces in `app/Domain/Repositories/`, implementations in `app/Infrastructure/Repositories/`.

## Policies

Every model has a policy class. Default deny. Examples:

```php
class BookingPolicy
{
    public function view(Client|User $actor, Booking $booking): bool
    {
        if ($actor instanceof User) {
            return true;  // admin sees all
        }
        return $actor->id === $booking->client_id;
    }

    public function cancel(Client|User $actor, Booking $booking): bool
    {
        if ($booking->status->isTerminal()) return false;

        if ($actor instanceof Client) {
            return $actor->id === $booking->client_id
                && $booking->starts_at->isAfter(now()->addHours(2));
        }
        return $actor->isOwner() || $actor->isAssistant();
    }
}
```

## Boundaries

The domain depends on:
- Eloquent (for persistence — accepted compromise for Laravel-ness)
- Carbon (for time)
- The standard library

The domain does **NOT** depend on:
- HTTP request/response classes
- Stripe SDK, Cerebras SDK, Twilio SDK (interfaces live in domain, implementations in infrastructure)
- Filament
- Livewire

This boundary is enforced by Larastan custom rules and code review.
