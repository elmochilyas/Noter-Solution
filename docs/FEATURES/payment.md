# Feature: Payment

## Scope

The payment step of the booking flow, plus all payment lifecycle events (refunds, disputes, receipts). Architecture details in `ARCHITECTURE/payments.md`.

## Design reference

Payment-step screen in `DESIGN/screens-index.md`: #8 (payment with Stripe Elements + cash-at-office option). Failure state: #10. Receipt PDF layout is in `COMPLIANCE/receipts-invoicing.md` (not Stitch-generated). Before writing views, read `DESIGN/README.md` and `DESIGN/design-system.md`.

## Methods supported

| Method | When offered | Plan support |
|---|---|---|
| Card via Stripe | Always (except free orientation) | All paid plans |
| Card via CMI | v1.1 (after CMI account active) | Same as Stripe |
| Cash at office | In-office plans only | Standard in-office, Extended in-office |

Free orientation skips payment entirely.

## Payment step UX

A Livewire component embedded in the booking flow.

### For paid plans

```
┌─────────────────────────────────────────────────────────┐
│ Récapitulatif                                            │
│   Consultation standard en ligne                         │
│   Mardi 14 mars 2026 à 10:30                             │
│   Durée : 30 min                                         │
│   ────────────────────                                   │
│   Total : 250,00 MAD                                     │
├─────────────────────────────────────────────────────────┤
│ Mode de paiement                                         │
│   ◉ Carte bancaire (sécurisé via Stripe)                 │
│   ○ Espèces au cabinet  (in-office plans only)           │
├─────────────────────────────────────────────────────────┤
│ [Stripe Elements card iframe]                            │
│                                                          │
│   Carte :  ____ ____ ____ ____                          │
│   Expiration : __/__       CVC : ___                    │
├─────────────────────────────────────────────────────────┤
│ ⓘ Vos coordonnées de carte ne transitent pas par notre  │
│   serveur. Elles sont traitées directement par Stripe.   │
├─────────────────────────────────────────────────────────┤
│ [ Confirmer et payer ]   [ ← Modifier ]                  │
└─────────────────────────────────────────────────────────┘
```

### For free orientation

```
┌─────────────────────────────────────────────────────────┐
│ Récapitulatif                                            │
│   Orientation gratuite                                   │
│   Mardi 14 mars 2026 à 10:00                             │
│   Durée : 10 min                                         │
│   ────────────────────                                   │
│   Total : 0,00 MAD — orientation offerte                 │
├─────────────────────────────────────────────────────────┤
│ [ Confirmer mon rendez-vous ]   [ ← Modifier ]           │
└─────────────────────────────────────────────────────────┘
```

## Card payment flow (Stripe)

```
1. User on /book/payment with booking in pending state
2. Client-side: load Stripe.js with publishable key
3. Mount Stripe Elements for card input
4. User clicks "Confirmer et payer"
5. Frontend calls backend `/book/intent/create` (Livewire action)
6. Backend:
   - Re-validates the booking state (slot still held, plan/price match)
   - Calls PaymentService::createIntent(Booking)
   - PaymentService → StripeGateway → creates Stripe PaymentIntent
   - Idempotency key: "booking-{booking.id}"
   - Returns client_secret to frontend
7. Frontend: stripe.confirmCardPayment(client_secret, { payment_method })
8. Stripe may trigger 3DS challenge (modal)
9. On success: frontend redirects to /book/success?reference=SBA-XXXXXX
10. Stripe webhook arrives at /webhooks/stripe (potentially in parallel)
11. Webhook handler:
    - Verifies signature
    - Deduplicates by event ID
    - Marks Payment as succeeded
    - Fires PaymentSucceeded event
    - Event listeners: ConfirmBooking, GenerateReceipt, SendConfirmations
```

The webhook is the source of truth. The frontend redirect is a UX optimization; if it never fires (network drop), the email still arrives and the user can see status in the portal.

## Cash payment flow

```
1. User on /book/payment selects "Espèces au cabinet"
2. Submit → BookingService::createPending + PaymentService::createCashPending
3. Booking immediately marked confirmed (we trust the booking)
4. Payment row created with gateway=cash, status=pending
5. Notifications dispatched
6. On appointment day, Sana marks booking completed in Filament
7. Sana (or assistant) marks the Payment as succeeded with reason "Paid in cash"
8. Receipt generated
```

Risks: see `ARCHITECTURE/payments.md` (no-show with no payment received).

## Failure handling

### Card declined

- Frontend shows the Stripe-provided error message (translated).
- User can retry — Payment Intent allows multiple attempts on the same intent.
- After 3 failed attempts within the same intent, suggest cash-at-office (if eligible) or escalation via WhatsApp.

### 3DS abandoned

- Payment Intent remains in `requires_action` until expiry (default 24h).
- We poll only via webhook (not active polling).
- If no success in 30 min, the slot hold expires, the booking is cancelled with reason `payment_timeout`.

### Network drop mid-payment

- Frontend retries the confirmation. Stripe handles idempotency.
- If user closes tab: webhook will still confirm if Stripe captured. They'll get the confirmation email.

### Stripe outage

- `/book/payment` shows a banner: "Le paiement en ligne est temporairement indisponible. Vous pouvez réserver pour paiement au cabinet (in-office plans) ou nous contacter au [phone] / WhatsApp."
- Cash-at-office option becomes the only path for in-office plans.
- Online plans: error page with WhatsApp / phone CTAs.

## Refunds

Triggered by:
- Client cancellation within refund window (portal action)
- Admin cancellation (Filament)
- Dispute / chargeback resolution

### Client-initiated refund

```
1. Client on portal clicks "Annuler ma réservation"
2. Modal: confirms cancellation reason (free-text optional) and refund amount preview (100% / 50% / 0%)
3. Confirms
4. BookingService::cancel(booking, reason, $client)
5. If refundable amount > 0:
   - RefundRequest created with status=requested
   - Notifies Sana (email)
6. Client receives email: "Votre demande d'annulation est enregistrée. Le remboursement est en cours de traitement (sous 5 jours ouvrables)."
7. Sana approves in Filament → PaymentService::refund called
8. Stripe refund processed
9. Refund row updated to status=succeeded
10. Client receives email confirming refund
```

### Admin-initiated refund

- Same as above but starts from Filament.
- Admin can specify any amount up to the original payment.
- Reason required.
- Approval required if amount > 0 and the admin is not the owner.

### Refund policy

Defined in `services` or `consultation_plans` settings (configurable, but defaults):

| Time before appointment | Refund % |
|---|---|
| ≥ 24 hours | 100% |
| 2–24 hours | 50% |
| < 2 hours | 0% |

Configurable per plan if Sana wants different rules.

Cancellation fee (the non-refunded portion) is documented on the receipt as "Frais d'annulation".

## Receipts

Generated immediately on `PaymentSucceeded` (cash or card).

See `COMPLIANCE/receipts-invoicing.md` for content rules.

Process:
- `GenerateReceiptPdf` job queued
- Generates PDF via Browsershot using the Blade template at `resources/views/pdf/receipt.blade.php`
- Allocates the next sequential number from `receipts_number_seq`
- Stores PDF at `receipts/{YYYY}/{MM}/{number}.pdf` on Supabase
- Sends email to client with PDF attached
- Receipt visible in client portal

## Disputes / chargebacks

- `charge.dispute.created` webhook → email to Sana (high priority)
- Booking status unchanged automatically (Sana decides)
- Sana collects evidence: receipt, communication log, attendance proof
- Submitted via Stripe Dashboard before the deadline
- Sentry alert if a dispute exists past 5 days without action (Sana might miss the email)

## Payment Intent re-use

For a single booking, multiple payment attempts use the **same** PaymentIntent (Stripe-recommended). Idempotency key bound to booking ID prevents duplicate intents.

If the booking expires (slot hold gone, payment not completed in 30 min):
- The PaymentIntent is canceled by Stripe automatically after 24h.
- We mark the Payment as cancelled when we cancel the booking.

## Currency

- All amounts in MAD.
- Stripe configured with MAD as supported currency (needs to be enabled in dashboard).
- Stored in centimes (integer) — see `ARCHITECTURE/domain-model.md` MoneyMad.

## Pricing changes

Prices stored on `consultation_plans.price_centimes`. When a plan price changes:

- Future bookings use the new price.
- Existing bookings keep their snapshotted price (`bookings.total_centimes`).
- The Plan history is preserved via the activity log.

## Test mode

- Stripe test keys in dev and staging.
- Test card `4242 4242 4242 4242` (any future date, any CVC, any postal) for success.
- Test card `4000 0000 0000 9995` (insufficient funds) for failure.
- Test card `4000 0027 6000 3184` (3DS required) for SCA.

Documented in `OPERATIONS/environment-setup.md`.

## Webhook security

See `ARCHITECTURE/payments.md`. Highlights:
- Signature verification
- Event ID deduplication
- Timestamp tolerance
- CSRF exempted
- Rate limit 1000/min

## Acceptance criteria

- [ ] Full Stripe payment flow works on test cards
- [ ] 3DS challenge handled
- [ ] Declined card shows translated error
- [ ] Cash-at-office flow works
- [ ] Free orientation skips payment cleanly
- [ ] Webhook signature verification rejects invalid signatures
- [ ] Webhook deduplication prevents double-processing
- [ ] Receipt generated on successful payment
- [ ] Receipt sent by email with PDF attachment
- [ ] Receipt visible in portal
- [ ] Refund flow works end-to-end (request → approve → Stripe API → notify client)
- [ ] Refund policy correctly computes percentages by time-to-appointment
- [ ] Dispute webhook fires Sana email alert
- [ ] No PCI-scope code (we don't touch card data)
- [ ] Sequential receipt numbering verified
- [ ] All payment-related events audit-logged
- [ ] Failed payment retried within same PaymentIntent
- [ ] Network drop scenarios don't double-charge

## Out of scope

- Subscription / recurring billing
- Multi-currency (MAD only)
- Installments / payment plans
- Voucher / discount codes
- Saved cards / customer portal (Stripe Billing portal)
- Apple Pay / Google Pay (deferred; Stripe supports both — can be enabled in dashboard later)
- Pay-by-link (separate Stripe feature)

## Risks

- **CMI integration timing.** Stripe is the v1 carrier; CMI is the v1.1 target. The gateway interface keeps the switch low-risk, but live testing in CMI's environment will take 1-2 weeks.
- **Moroccan card acceptance.** Some Moroccan cards may not work with international Stripe acceptance — that's why CMI is needed for v1.1.
- **3DS friction.** Most Moroccan cards require 3DS; the UX must handle it cleanly. Stripe Elements does this well, but we test on at least one Moroccan card before launch.
