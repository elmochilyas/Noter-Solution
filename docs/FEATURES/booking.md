# Feature: Booking

## Scope

The end-to-end flow that lets a visitor become a confirmed booking. Everything from "I want to book" to "you're confirmed" — excluding the payment step itself (covered in `FEATURES/payment.md`).

## Design reference

Booking-flow screens in `DESIGN/screens-index.md`: #6 (slot picker), #7 (identity + documents), #9 (success), #10 (payment failed). Step 8 (payment) is in `FEATURES/payment.md`. Before writing views, read `DESIGN/README.md` and `DESIGN/design-system.md`.

## User flow

```
Plan selection ──→ Category & description ──→ Slot picker ──→ Identity ──→ Documents ──→ Payment ──→ Confirmation
       │                    │                      │              │            │             │             │
      step 1               step 2                step 3        step 4       step 5        step 6      success
```

> **Note:** The implementation uses 5 steps after plan selection (6 total). Identity (step 4) includes the document upload toggle. If "No documents" is selected, a "skip to payment" button is shown instead of the full document upload UI. The separate "Description" step from earlier designs was collapsed into step 2 (Category & description).

Each step is a Livewire component. State persists across steps in a single Livewire form object so the user can navigate back without losing data.

## Step 1: Plan selection

Entry point: `/book` (with optional `?plan=<slug>` and `?category=<cat>` from the consultation page or chatbot triage).

If `?plan=` query param is present and valid, skip to step 2.

Display: the 4 plan cards (reused from public site `FEATURES/public-site.md`). Click → select plan, advance to step 2.

## Step 2: Category and description

- "Quelle est la matière de votre rendez-vous ?" — radio selection: Family / Real estate / Financial / Contracts / Other
  - Pre-filled if `?category=` provided.
- "Décrivez brièvement votre situation" — textarea (20–2000 chars, 500 char visual recommendation)
- "Avez-vous déjà tous vos documents ?" — Yes / No (the "Not sure" option was collapsed into "Yes" — if uncertain, the client can upload whatever they have)
- "Préférez-vous en personne ou en vidéo ?" — only shown if the selected plan has `format=both`
  - Pre-filled if `?format=` provided.

Validation:
- Category required.
- Description 20-2000 chars.
- Has-documents required.
- Format required if plan allows both.

Click "Continuer" → step 3.

## Step 3: Slot picker

A calendar component (`<livewire:booking.calendar>`) showing available slots.

### Loading rules

- Show the next 30 days at most.
- Free orientation plan: only the next 7 days (kept available for casual reach).
- Slot duration matches the plan's duration (10 / 30 / 60 / 90 min).
- Slot increments: 30 minutes by default.

### Computing availability

`AvailabilityService::availableSlots`:

1. Generate candidate slots from `availability_rules` for the date range.
2. Subtract `availability_exceptions` (holidays, vacations, manual blocks).
3. Subtract confirmed bookings (`bookings` where `status IN ('confirmed')`).
4. Subtract active booking holds (`booking_holds` where `expires_at > now()`).
5. For online plans: subtract in-office bookings too (Sana can't do both at once).
6. For in-office plans: subtract online bookings.
7. Subtract anything within 2 hours of now (no last-minute bookings online — must call).
8. Apply quiet hours (no slots before 09:00 or after 18:00 local).
9. Cache result for 60 seconds (acceptable freshness vs. cost).

### Layout

- Two-column on desktop: month-view calendar on the left, time slots for the selected date on the right.
- Single-column on mobile: date strip on top, time slots below.
- Days with available slots: dot indicator.
- Days fully booked or unavailable: muted.

### Interaction

- Click a date → slot list updates.
- Click a slot → place a hold + advance to step 4.
- Holds are created server-side via `AvailabilityService::holdSlot`. Hold expires in 10 minutes.
- If the user takes >9 minutes on step 4–5, show a warning. At 10 min, the hold expires and they must restart the slot picker.

### Edge cases

- Slot becomes taken between display and selection: server-side `assertSlotIsFree` throws `SlotNotAvailable`; the calendar refreshes and shows an inline error.
- User picks a slot, then navigates back to step 1 to change the plan: hold released. They re-pick a slot.
- User on mobile leaves the tab: hold remains active for 10 minutes regardless.

## Step 4: Identity

Form:
- Full name (required, 3-160 chars)
- Email (required, valid format)
- Phone (required, Moroccan format — `MoroccanPhoneNumber` value object)
- Preferred contact channel — radio: Email / SMS / WhatsApp (optional, default email)
- Optional: CIN number (asked only if the selected plan + category combo benefits from having it ahead of time — e.g. real estate, succession)
- Locale preference auto-set from current locale
- T&Cs checkbox: "J'accepte les conditions d'utilisation et la politique de confidentialité"
- Loi 09-08 consent line: "Je consens au traitement de mes données conformément à la politique de confidentialité"

If the email matches an existing Client:
- The existing Client record is reused (name, email, phone, national_id are carried over from the first booking).
- Magic-link login NOT required — the booking flow continues as a guest write.
- Booking will be linked to that Client.

Validation:
- Standard rules per fields.
- Email + phone format.
- T&Cs must be accepted.
- Honeypot field (hidden).

Click "Continuer" → step 5 (or skip directly to step 6 if no documents needed).

## Step 5: Documents (optional)

If the user said "Yes" on having documents:
- Allow upload of up to 5 files.
- Each file: PDF, JPG, or PNG; max 10 MB.
- Upload one at a time, progress bar per file.
- Drag-and-drop supported on desktop.
- Mobile: tap to open file picker (camera + photo library).
- Each file shown with thumbnail, name, size; can be removed.

If "No":
- Show a short note: "Vous pourrez les téléverser depuis votre espace client après confirmation."
- Skip the upload UI.

Documents are uploaded via Livewire to a temporary location. On step 6 confirmation, they're moved to permanent storage and `Document` rows created.

If upload fails (network, scan rejection): user sees the error and can retry.

Click "Continuer" → step 6.

## Step 6: Payment

See `FEATURES/payment.md`. Briefly:

- If plan is free (orientation): no payment, skip to success.
- If plan is in-office and "cash" option allowed: client picks card or cash.
- If plan is online: card only.

Submitting payment:
- Server creates `Booking` in `pending_payment`.
- Server creates `Payment` row.
- Server creates Stripe Payment Intent (or marks cash flow).
- For card: Stripe Elements collect details; on success webhook fires.
- For cash: booking immediately confirmed.

On confirmation event, all notifications dispatched.

## Step 7: Success

`/{locale}/book/success?reference=SBA-XXXXXX`

Content:
- "Votre rendez-vous est confirmé !"
- Booking summary: plan, date, time, format, location (office address or "Le lien Jitsi vous sera envoyé 1h avant").
- Booking reference: `SBA-XXXXXX`
- "Vos prochaines étapes" — what to expect (e.g. "Vous recevrez un rappel 24h avant + 1h avant", "Préparez vos documents")
- "Suivi de votre dossier" — link to portal: `/{locale}/portal/login?email=<prefilled>`
- "Modifier ou annuler" — links to portal
- WhatsApp / phone numbers for questions

Generates a confirmation page that's shareable as a URL only with the booking reference embedded (no PII in URL).

## State persistence across steps

Single Livewire form object `BookingFormState` lives in the component:

```php
class BookingFormState extends Form
{
    public ?int $planId = null;
    public ?string $category = null;
    public ?string $description = null;
    public ?bool $hasDocuments = null;
    public ?string $format = null;
    public ?CarbonImmutable $slotStartsAt = null;
    public ?int $holdId = null;
    public ?string $fullName = null;
    public ?string $email = null;
    public ?string $phone = null;
    public ?string $preferredChannel = 'email';
    public ?string $nationalId = null;
    public bool $acceptedTerms = false;
    public bool $acceptedPrivacy = false;
    public array $uploadedFiles = [];  // tempo refs
    public string $paymentMethod = 'card';
}
```

State stored in the Livewire session under a single key. Cleared on success or abandonment (after 30 min).

## Reschedule and cancel

### Cancel

- Client-initiated cancel (from portal):
  - If ≥ 24h before: full refund (if paid by card). Booking → `cancelled`.
  - If 2-24h: 50% refund.
  - If < 2h: no refund.
  - Free orientation: no refund applies; just cancel.
- Admin-initiated cancel (Filament): same policy by default, but admin can override with reason.

Cancel reason captured in `cancellation_reason`. Stored on the booking and in the audit log.

### Reschedule

- Allowed up to 2 hours before the original slot.
- The original booking is cancelled with reason `rescheduled`.
- A new booking is created at the new slot, inheriting plan, category, description, format, payment status.
- New booking gets a new reference. Old reference stays on the cancelled record.
- No refund / re-charge — the payment carries over via the `original_booking_id` linkage on the payment.
- Reminders re-scheduled.

### Limits

- Max 2 reschedules per client per 30 days (anti-abuse).
- Max 3 cancellations in 90 days → manual review by Sana (no automatic block; just flag).

## Edge cases

| Case | Behavior |
|---|---|
| User closes tab mid-flow | Hold expires in 10 min. State recoverable only if same browser session. |
| User loses internet on payment | Payment Intent stays in `requires_action` or `requires_confirmation` for a few hours. User can retry from `/book/payment/<intent>` link in their email. |
| Stripe webhook arrives before user redirected back | Booking marked `confirmed` from webhook. Redirect-back handler is idempotent. |
| Double-submission (two booking attempts at the same slot) | Second submission gets `SlotNotAvailable` and refunded automatically. |
| Network drop after submission but before redirect | Webhook still confirms. User opens email, finds confirmation. |
| User books with an email that exists for another client (typo) | We link to the existing client unless the phone also doesn't match — then we flag for admin review. |
| User books for a different person (e.g. spouse) | One booking, identity is the person attending. We don't model multi-party in v1. |

## Booking status transitions

See `ARCHITECTURE/domain-model.md` for the state machine. Enforced by `BookingService` methods.

## Notifications fired

On `BookingConfirmed`:
- Email: confirmation + receipt (for paid)
- SMS: short confirmation
- WhatsApp: confirmation with portal link
- Admin email: "New booking"

Scheduled:
- T-24h: reminder (email + SMS + WA)
- T-1h: reminder (SMS + WA only)

## Free orientation specifics

The free orientation plan is **online, 10 min, no payment, no card capture**.

To prevent abuse:
- Email rate-limited: 2 free orientations per email per 90 days.
- Phone rate-limited: 2 per phone per 90 days.
- Soft anti-abuse: heuristic (multiple free bookings from same IP / same device fingerprint) gets queued for manual review.
- Admin can revoke a confirmed orientation if abuse suspected.

## Anti-fraud

- Stripe Radar enabled with default rules.
- IP geolocation logged with each booking (informational, not blocking).
- Device fingerprint logged via stripe.js (helps Stripe; we don't process it).
- High-value bookings (>500 MAD) require 3DS by default (Stripe handles automatically).

## Internal admin actions

From Filament, admin can:
- View booking details
- Edit description / internal notes
- Change status (confirmed → completed / no_show / cancelled)
- Trigger a manual refund (subject to approval per `ARCHITECTURE/payments.md`)
- Generate / resend receipt
- Resend confirmation
- Add internal notes (encrypted column)
- View all documents
- View payment timeline
- View activity log for the booking

## Acceptance criteria

- [ ] Full flow completable on mobile and desktop in both languages
- [ ] Holds work — two clients can't double-book the same slot
- [ ] Holds expire on time
- [ ] All 7 status transitions tested with positive + negative cases
- [ ] Reschedule preserves payment and creates new reference
- [ ] Cancel applies refund policy correctly
- [ ] Free orientation respects rate limits
- [ ] All notifications dispatched on `BookingConfirmed`
- [ ] T-24h and T-1h reminders scheduled and visible in Horizon
- [ ] On payment failure, slot eventually released
- [ ] Existing client (returning) gets prefilled form
- [ ] No console errors, no Sentry errors during full flow
- [ ] Lighthouse Performance ≥ 85 on booking pages (acceptable lower than marketing due to interactivity)
- [ ] Authorization tests pass (client A can't see client B's booking, etc.)
- [ ] Documents uploaded during step 5 are linked to the booking on confirmation
