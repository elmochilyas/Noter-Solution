# Payments Architecture

## Gateway-agnostic design

We abstract behind a `PaymentGateway` interface so the application doesn't know whether it's talking to Stripe, CMI, or a future provider.

```php
interface PaymentGateway
{
    public function createIntent(CreateIntentRequest $request): GatewayIntent;
    public function verifyWebhook(Request $request): WebhookEvent;
    public function refund(string $chargeId, MoneyMad $amount, string $reason): GatewayRefund;
    public function name(): PaymentGatewayName;
}
```

Implementations:
- `StripeGateway` — v1
- `CmiGateway` — v1.1 (after merchant account active)

Selection is driven by config:

```php
// config/payments.php
return [
    'default' => env('PAYMENT_GATEWAY', 'stripe'),
    'gateways' => [
        'stripe' => StripeGateway::class,
        'cmi' => CmiGateway::class,
    ],
];
```

## Stripe integration

### Why Stripe v1

- Works immediately, no Moroccan bank dependency.
- Supports Moroccan-issued cards via 3D Secure.
- Mature webhook + idempotency story.
- Strong test mode for development.

### Stripe Elements on the client

- Card details captured by Stripe.js in iframes — we never see card numbers.
- This keeps us out of PCI-DSS scope (we touch only tokens).
- Client-side script: minimal — collect element, create token, submit to our backend.

### Payment Intents API

We use **Payment Intents** (not Charges, deprecated). Flow:

```
1. Client picks slot, fills form → POST /booking/intent
2. BookingController::createIntent:
   - Validates input
   - BookingService::createPending(BookingData) → Booking
   - PaymentService::createIntent(Booking) → calls StripeGateway::createIntent
3. StripeGateway creates a Stripe PaymentIntent:
   - amount = booking.total_centimes
   - currency = 'mad'
   - metadata.booking_id = booking.id
   - payment_method_types = ['card']
   - capture_method = 'automatic'
   - idempotency_key = 'booking-' + booking.id
4. Server stores a Payment row with status=pending, gateway_intent_id=pi_xxx
5. Server returns the client_secret to the browser
6. Browser uses client_secret + Stripe Elements to confirm the payment
   (3D Secure may pop up here)
7. On client-side confirmation success: browser redirects to /book/success
   (we DON'T trust this — see webhook below)
8. Webhook arrives at /webhooks/stripe → PaymentService::confirmFromWebhook
9. Server marks Payment status=succeeded, fires PaymentSucceeded event
10. Event triggers BookingConfirmed → notifications + receipt generation
```

### Webhook verification

Critical: never trust the client-side redirect. The webhook is the source of truth.

```php
class StripeWebhookController
{
    public function handle(Request $request, StripeGateway $gateway, PaymentService $payments): Response
    {
        try {
            $event = $gateway->verifyWebhook($request);  // throws on invalid signature
        } catch (InvalidWebhookSignature $e) {
            Log::warning('Invalid Stripe webhook signature', ['ip' => $request->ip()]);
            return response('Invalid signature', 400);
        }

        $payments->handleWebhookEvent($event);

        return response('OK', 200);
    }
}
```

### Signature verification

`StripeGateway::verifyWebhook` uses `Stripe\Webhook::constructEvent($payload, $sigHeader, $secret)` which:
- Validates HMAC signature.
- Validates timestamp tolerance (5 minutes).
- Throws on mismatch.

### Idempotency

- Stripe deduplicates by `idempotency_key` on PaymentIntent creation.
- Webhooks may be delivered multiple times. We deduplicate by Stripe event ID:
  ```php
  $eventId = $event->id;
  if (StripeProcessedEvent::where('stripe_event_id', $eventId)->exists()) {
      return;  // already processed
  }
  StripeProcessedEvent::create(['stripe_event_id' => $eventId, 'received_at' => now()]);
  // ... process
  ```

### Webhook events we handle

| Stripe event | Our handler |
|---|---|
| `payment_intent.succeeded` | Mark payment succeeded, fire PaymentSucceeded |
| `payment_intent.payment_failed` | Mark payment failed, fire PaymentFailed |
| `payment_intent.canceled` | Mark payment cancelled, cancel booking |
| `charge.refunded` | Mark refund completed, fire RefundIssued |
| `charge.dispute.created` | Notify admin (manual intervention) |

Other events are 200-OK'd but ignored.

### Webhook security checklist

- [x] Signature verification before processing
- [x] Timestamp tolerance (5 min) — Stripe-provided
- [x] Event ID deduplication
- [x] No PII or card data in logs
- [x] Webhook secret rotated quarterly
- [x] Failed webhooks alerted via Sentry
- [x] Endpoint excluded from CSRF middleware (signature suffices)
- [x] Rate limit: 1000/min (generous; alerts at 500/min indicate attack)

## CMI integration (v1.1)

Centre Monétique Interbancaire is Morocco's primary card payment gateway. Integration follows the same pattern via `CmiGateway`.

Implementation notes (to be detailed when CMI account is active):
- CMI uses a redirect-based flow (not iframe).
- HMAC-SHA256 signature on requests and responses.
- Test environment available on request.
- Webhook equivalent is a "notification URL" POST.
- Receipt generation must include CMI transaction reference.

The gateway swap requires:
1. Setting `PAYMENT_GATEWAY=cmi` in `.env`.
2. Adding CMI credentials.
3. Whitelisting our IP with CMI.
4. Soak testing in CMI's test environment.
5. Coordinated go-live with the bank.

No code changes in the rest of the app.

## Cash-at-office flow

For in-office bookings, the client may choose to pay in cash on the day.

```
1. Booking submitted with payment_method=cash
2. BookingService::createPending creates the booking
3. No Stripe intent created
4. Payment row created with status=pending, gateway=cash, amount=plan price
5. Booking status set to confirmed immediately (cash bookings don't need pre-payment)
6. Notification sent: "Confirmé — règlement en espèces au cabinet"
7. On the appointment day, Sana marks the booking completed in Filament
8. At that moment, the assistant or Sana marks the Payment status=succeeded
9. PaymentSucceeded event fires → receipt generated
```

Risks and mitigations:
- **No-show with no payment received:** acceptable risk for in-office cash. Mitigated by:
  - Reminders 24h and 1h before.
  - Tracking no-show rate per client.
  - Admin can require pre-payment for future bookings if a client no-shows once.

## Refunds

### Policy (from `COMPLIANCE/`)

- Cancel ≥24h before: 100% refund.
- Cancel 24h–2h before: 50% refund.
- Cancel <2h before or no-show: 0% refund.
- Free orientation: no refund applies.

### Flow

```
1. Refund initiated:
   - By client via portal (within policy window) → automatic
   - By admin via Filament → manual amount and reason
2. RefundRequest created with status=requested
3. Requires approval if amount > 0 (owner-only permission)
4. On approval:
   - PaymentService::refund called
   - Gateway issues refund (Stripe API call)
   - Refund row updated with gateway_refund_id, status=succeeded
   - RefundIssued event fires
5. Client notified by email + SMS
```

### Idempotency on refunds

- Idempotency key: `refund-{refund_id}`.
- Reissuing a refund returns the same Stripe refund object — no double refund.

## Receipts

Generated as PDF when a Payment moves to `succeeded`.

Contents (required by Moroccan invoicing rules — see `COMPLIANCE/receipts-invoicing.md`):
- Practice name, address, ICE, IF, RC, Patente
- Receipt number (sequential, never reused, format: `SBA-YYYY-NNNNNN`)
- Issue date (in Morocco timezone)
- Client name and address
- Service description (e.g. "Consultation standard en ligne — 30 min")
- Booking reference
- Amount in MAD
- VAT line (if applicable — adoul services typically VAT-exempt; subject to Sana's accountant confirmation)
- Payment method (Carte / Espèces)
- Gateway transaction reference (Stripe charge ID or "Espèces")

### Sequential numbering

- Each PDF gets a sequential number from a dedicated DB sequence (`receipts_number_seq`).
- The sequence increments atomically per generation.
- Format: `SBA-2026-000123` where `2026` is the current year (resets the visual prefix but not the underlying sequence to ensure uniqueness).
- Numbers are never reused, even if a receipt is voided.

### Voided receipts

If a refund is issued:
- The original receipt is kept (legal record).
- A "Note de crédit" (credit note) PDF is generated with its own number.
- Both are visible in the admin and client portal.

### Storage

- PDF stored in Supabase Storage, `receipts` bucket, private.
- Accessed via signed URL (5-minute TTL).
- Permanent retention (10 years per Moroccan fiscal law).

## Payment failure UX

When a payment fails (3DS rejected, insufficient funds, etc.):
1. Client lands on `/book/failed` with the failure reason translated.
2. Booking remains in `pending_payment` for 30 minutes (slot is held).
3. Client can retry payment from the same page.
4. After 30 min without a successful payment, the slot hold is released and the booking is cancelled with reason `payment_timeout`.

## Disputes / chargebacks

- `charge.dispute.created` webhook → emails Sana immediately.
- Booking status not auto-changed (Sana decides).
- Dispute evidence (receipt, communication, attendance proof) collected manually and submitted via Stripe Dashboard within their deadline.

## Testing

- Stripe test mode in dev and staging.
- Test cards documented in `OPERATIONS/environment-setup.md`.
- Webhook signatures replayed locally via Stripe CLI: `stripe listen --forward-to localhost:8000/webhooks/stripe`.
- Integration tests mock the Stripe SDK — never hit real API.
- One Dusk E2E test runs against Stripe test mode in CI (manual trigger, weekly).

## Auditability

All payment-related events recorded in:
- `payments` table (current state)
- `activity_log` (history of changes)
- Stripe Dashboard (gateway record)

Three-way reconciliation possible at any time.

## Compliance touch-points

See `COMPLIANCE/`:
- `loi-09-08.md` for PII in payment records
- `receipts-invoicing.md` for receipt requirements
- `notary-rules.md` for fee disclosure restrictions
