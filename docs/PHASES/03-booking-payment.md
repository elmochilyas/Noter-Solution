# Phase 3 — Booking + Payment

## Goal

A visitor can pick a plan, choose a slot, fill identity, upload documents, pay (or select cash), and receive a confirmed booking with email + SMS + WhatsApp notifications and a generated receipt. This is the critical-path tentpole.

**Definition of phase complete:** end-to-end booking flow works in production-equivalent staging for all 4 plans, all payment paths (Stripe card + cash-at-office + free orientation), Stripe webhook reliably confirms bookings, receipts are generated and emailed, reminders are scheduled, and the cancel / reschedule flows work for clients and admin.

## Prerequisites

- [ ] Phase 2 complete and merged
- [ ] Stripe account fully activated for Morocco; MAD enabled as a currency
- [ ] Resend domain fully verified and sending in production
- [ ] Twilio SMS sender ID `Bouhamidi` approved (or long-code fallback accepted by Sana)
- [ ] At least 2 WhatsApp Business templates approved by Meta (confirmation + reminder)
- [ ] Sana provides: booking T&Cs, cancellation policy wording, notification copy, receipt template approved by accountant
- [ ] All practice info (ICE, IF, RC, Patente) finalized for receipts

## Scope

In:
- Booking flow (7 steps per `FEATURES/booking.md`)
- Slot picker with holds, availability rules, exceptions
- Stripe Payment Intents integration via `StripeGateway`
- Cash-at-office flow for in-office plans
- Free orientation flow (no payment)
- Stripe webhook handler with signature verification + idempotency
- Receipt PDF generation per `COMPLIANCE/receipts-invoicing.md`
- Notification dispatch on `BookingConfirmed`: email, SMS, WhatsApp
- Scheduled reminders (T-24h, T-1h)
- Cancel / reschedule flows (client + admin)
- Refund request → approve → Stripe API
- Filament `BookingResource` minimum viable (list, view, edit, cancel, reschedule, refund-request)
- Quiet hours enforcement on SMS / WhatsApp
- Notification preferences plumbing (clients table)

Out:
- Full admin panel (phase 5) — only the bare minimum to manage bookings in this phase
- Client portal (phase 4) — bookings created here but viewable only via admin until then
- Chatbot triage → booking (chatbot is phase 6; the `/book` URL accepts `?plan=`/`?category=`/`?format=` params already)
- CMI gateway (v1.1)

## Tasks

### Task 1: PaymentGateway abstraction

Acceptance:
- [ ] `PaymentGateway` interface in `app/Domain/Payment/`
- [ ] `StripeGateway` implementation in `app/Infrastructure/Payment/`
- [ ] Methods: `createIntent`, `verifyWebhook`, `refund`, `name`
- [ ] `CmiGateway` stub committed (throws `not implemented`) so the binding lookup pattern is in place
- [ ] Service container binds `PaymentGateway::class` to the configured driver via `config/payments.php`
- [ ] Unit tests cover gateway selection by config

### Task 2: Domain services

Implement per `ARCHITECTURE/domain-model.md`:

Acceptance:
- [ ] `BookingService` with `createPending`, `confirm`, `complete`, `markNoShow`, `cancel`, `reschedule`
- [ ] `AvailabilityService` with `availableSlots`, `assertSlotIsFree`, `holdSlot`
- [ ] `PaymentService` with `createIntent`, `confirmFromWebhook`, `refund`, `markCashSucceeded`
- [ ] `ReceiptService` with `generate`, `temporaryUrl`
- [ ] `DocumentService` with `attachToBooking`, `temporaryUrl`, `delete`
- [ ] Stateless, injected; no business logic in models / controllers
- [ ] `BookingStatusTransition` guard implemented and called by every status-changing method
- [ ] Each service has ≥ 95% unit test coverage including failure paths

### Task 3: Booking domain events + listeners

Acceptance:
- [ ] Events implemented: `BookingCreated`, `BookingConfirmed`, `BookingCancelled`, `BookingCompleted`, `BookingNoShow`, `BookingRescheduled`, `PaymentSucceeded`, `PaymentFailed`, `RefundIssued`
- [ ] Listeners are queued unless explicitly synchronous
- [ ] `BookingConfirmed` triggers: confirmation notifications, schedule reminders, generate receipt (if paid)
- [ ] `BookingCancelled` triggers: cancellation notification, cancel scheduled reminders, refund job (if applicable)
- [ ] `BookingRescheduled` triggers: confirmation, cancel old reminders, schedule new reminders
- [ ] `Event::fake()` tests for each domain action verify the right events fire

### Task 4: Booking flow — UI

Implement the 7-step Livewire flow per `FEATURES/booking.md`:

Acceptance:
- [ ] Step 1: plan selection (skippable via `?plan=`)
- [ ] Step 2: category + description + has-documents + format (when applicable)
- [ ] Step 3: slot picker (calendar component, two-column desktop, single-column mobile, holds)
- [ ] Step 4: identity (full name, email, phone, preferred channel, optional CIN, T&Cs + privacy consent)
- [ ] Step 5: documents (optional upload, multi-file, validation)
- [ ] Step 6: payment (card via Stripe Elements, cash if eligible, free for orientation)
- [ ] Step 7: success page with reference + portal CTA
- [ ] Back navigation preserves state
- [ ] Form state persists in a `BookingFormState` Livewire form object
- [ ] Returning client (matching email) gets prefilled fields
- [ ] All copy from translation files; both languages working
- [ ] Mobile-friendly; tap targets ≥ 44 px; no zoom-on-focus on iOS

### Task 5: Slot picker + availability

Acceptance:
- [ ] `AvailabilityService::availableSlots` correctly composes from rules - exceptions - confirmed bookings - active holds - 2h lead time - plan duration
- [ ] Online / in-office mutual exclusivity respected
- [ ] Cache (60s TTL) implemented and invalidated on booking write
- [ ] Calendar shows dot indicator for days with availability
- [ ] Slot click creates a `BookingHold` with 10-min expiry
- [ ] Hold released on back-navigation or session loss
- [ ] `PurgeExpiredBookingHolds` job runs every 5 minutes
- [ ] `SlotNotAvailable` exception handled with inline refresh
- [ ] Two-clients-same-slot test passes (race-condition safe via DB constraint or `SELECT FOR UPDATE`)
- [ ] Free orientation limited to next 7 days

### Task 6: Stripe payment integration

Acceptance:
- [ ] Stripe.js + Elements loaded on payment step (publishable key from `.env`)
- [ ] `POST /book/intent/create` (Livewire action) creates a `PaymentIntent` with idempotency key `booking-{id}`
- [ ] Client confirms with `stripe.confirmCardPayment` using returned `client_secret`
- [ ] 3DS challenge handled (tested with `4000 0027 6000 3184` test card)
- [ ] Failure shows translated error
- [ ] Successful payment redirects to `/book/success?reference=…`
- [ ] CSP allows Stripe iframe domains (`js.stripe.com`)
- [ ] No card data ever passes through our backend (verified via traffic inspection in staging)

### Task 7: Stripe webhook

Acceptance:
- [ ] `/webhooks/stripe` endpoint excluded from CSRF
- [ ] Signature verification via `Stripe\Webhook::constructEvent` — invalid signatures rejected with 400 + Sentry warning
- [ ] Event ID deduplication via `stripe_processed_events` table or similar
- [ ] Handles: `payment_intent.succeeded`, `payment_intent.payment_failed`, `payment_intent.canceled`, `charge.refunded`, `charge.dispute.created`
- [ ] Idempotent: replaying same event has no side effect
- [ ] On `payment_intent.succeeded`: marks Payment succeeded, fires `PaymentSucceeded`, which confirms the booking
- [ ] Local testing via `stripe listen --forward-to`
- [ ] Sentry alert on signature failures or unhandled exceptions
- [ ] Webhook rate limit 1000/min

### Task 8: Cash-at-office flow

Acceptance:
- [ ] Only offered for in-office plans
- [ ] On selection: booking created in `confirmed` immediately, Payment row created with `gateway=cash, status=pending`
- [ ] Confirmation notifications dispatched (with "règlement en espèces au cabinet" note)
- [ ] Sana can mark Payment succeeded from Filament when the cash is received
- [ ] On manual mark-succeeded: `PaymentSucceeded` event fires → receipt generated
- [ ] Audit log records the manual marking with the user who did it

### Task 9: Free orientation flow

Acceptance:
- [ ] Free plan skips Stripe entirely
- [ ] Booking confirmed immediately on form submission
- [ ] Confirmation notifications dispatched
- [ ] No Payment row created (or Payment with zero amount and immediate `succeeded`)
- [ ] Rate limit: 2 free orientations per email per 90 days; 2 per phone per 90 days
- [ ] On limit hit: friendly message + suggest a paid plan or contact

### Task 10: Receipt generation

Acceptance:
- [ ] `GenerateReceiptPdf` job triggered on `PaymentSucceeded` (for any gateway including cash)
- [ ] Sequential number allocated from `receipts_number_seq`
- [ ] PDF rendered via Browsershot from `resources/views/pdf/receipt.blade.php`
- [ ] Template matches the layout in `COMPLIANCE/receipts-invoicing.md`
- [ ] Bilingual content (Arabic right, French left)
- [ ] Stored at `receipts/{YYYY}/{MM}/{number}.pdf` on Supabase
- [ ] Receipt row inserted
- [ ] `payment.receipt` email sent with PDF attached
- [ ] Sana's accountant reviewed a sample receipt
- [ ] Credit-note generation tested (refund triggers credit note with its own number)

### Task 11: Notifications — email

Acceptance:
- [ ] Templates per `FEATURES/notifications.md`: `booking.confirmation`, `booking.reminder.24h`, `booking.reminder.1h`, `booking.cancelled`, `booking.rescheduled`, `payment.receipt`, `payment.failed`, `refund.issued`, `magic_link` (link only — auth wiring in phase 4), `admin.new_booking`, `admin.contact_message`, `admin.dispute`, `admin.refund_request`
- [ ] Blade templates with HTML + auto plain-text alternative
- [ ] All translation keys present for both languages
- [ ] Resend driver configured, sending verified
- [ ] Sender, Reply-To, headers set per spec

### Task 12: Notifications — SMS

Acceptance:
- [ ] Twilio driver configured with `Bouhamidi` sender ID (or fallback)
- [ ] SMS templates fit 160 chars where possible
- [ ] URL shortener (`https://sb.ma/<hash>`) implemented as a simple route + table
- [ ] Test send to a real Moroccan phone succeeds in staging
- [ ] Failure handling with retry policy

### Task 13: Notifications — WhatsApp

Acceptance:
- [ ] Twilio WhatsApp Business configured
- [ ] At least 2 approved templates wired: confirmation + reminder (others added incrementally)
- [ ] Outbound messages use approved templates
- [ ] Test send to a real WhatsApp number succeeds in staging
- [ ] Inbound replies forwarded to admin email (basic, no full conversation UI in v1)

### Task 14: NotificationService orchestrator

Acceptance:
- [ ] `NotificationService::send($templateKey, $recipient, $data, $channels)` works
- [ ] Recipient resolution per channel (Client → email/phone/whatsapp)
- [ ] Per-channel queued job dispatched
- [ ] `notifications_log` row created + updated through lifecycle
- [ ] Critical-channel bypass implemented (confirmations always send via email)
- [ ] Quiet hours (22:00–07:00) enforced for SMS / WhatsApp
- [ ] Notification preferences honored
- [ ] Anti-abuse caps (5 SMS / 10 emails per recipient / hour) enforced
- [ ] Provider webhooks update delivery status (Resend `email.*`, Twilio `delivered/failed`)

### Task 15: Scheduled reminders

Acceptance:
- [ ] On `BookingConfirmed`: dispatch `SendBookingNotification` jobs with delays for T-24h and T-1h
- [ ] Jobs check booking status at execution; abort if not `confirmed`
- [ ] On `BookingCancelled` / `BookingRescheduled`: delayed jobs effectively no-op due to status check
- [ ] Both reminders observed firing on time in staging (using `Carbon::setTestNow` for fast verification + one real-clock test)

### Task 16: Cancel + reschedule

Acceptance:
- [ ] Cancel flow (admin via Filament + spec'd portal route — portal UI in phase 4): refund policy computed (100% / 50% / 0%) based on time-to-appointment
- [ ] Refund request created automatically if amount > 0; status = `requested`
- [ ] Reschedule flow: old booking cancelled with reason `rescheduled`; new booking created in `confirmed` with the same payment; new reference issued
- [ ] Reschedule limited to 2 per 30 days per client
- [ ] Notifications dispatched on both flows
- [ ] All status transitions blocked from invalid origins (verified via test)

### Task 17: Refunds — request, approve, execute

Acceptance:
- [ ] Refund request creates `RefundRequest` row, notifies owner
- [ ] Owner approves via Filament → `PaymentService::refund` calls Stripe API
- [ ] Idempotency key `refund-{id}` prevents double-refund
- [ ] Refund row updated through `requested → approved → succeeded` lifecycle
- [ ] `RefundIssued` event fires; `refund.issued` notification sent
- [ ] Credit-note receipt generated
- [ ] Cash refunds handled (no Stripe call; just admin records and credit note)

### Task 18: Filament `BookingResource` (minimum viable)

Acceptance:
- [ ] List view with filters per `FEATURES/admin-panel.md` BookingResource section
- [ ] Detail view shows full booking, client, payment, documents, receipt, timeline
- [ ] Actions: Cancel (with reason), Reschedule, Mark completed, Mark no-show, Resend confirmation, Resend receipt
- [ ] Refund-request action wired to the refund flow
- [ ] Internal notes (encrypted) editable
- [ ] Authorization policies enforced (assistant restricted)
- [ ] N+1 free (Pulse green on the list page)

### Task 19: Test data + Dusk E2E

Acceptance:
- [ ] Pest feature tests cover: booking creation per plan, payment success, payment failure, webhook idempotency, refund flow, cancel + reschedule, free orientation, cash flow
- [ ] Dusk E2E test: card payment through Stripe test card `4242 4242 4242 4242` from `/consultation` to `/book/success`
- [ ] Authorization tests for the BookingResource actions (assistant denied where appropriate)
- [ ] Tests use `Notification::fake()`, `Queue::fake()`, mocked Stripe

### Task 20: Performance + observability

Acceptance:
- [ ] Booking calendar load < 400ms p95 on staging
- [ ] Booking submission < 600ms p95
- [ ] No N+1 in the booking detail Filament page
- [ ] Sentry tagged events for `payment`, `webhook`, `booking` paths
- [ ] Pulse confirms healthy worker activity during a synthetic load test (20 concurrent booking submissions over 5 min)

### Task 21: Sana review of receipt + notifications

Acceptance:
- [ ] Sample receipt PDF reviewed by Sana's accountant for fiscal compliance
- [ ] Confirmation, reminder, cancellation, and receipt email reviewed by Sana for tone in both languages
- [ ] SMS / WhatsApp templates reviewed
- [ ] Any wording changes captured as PRs

## Phase exit criteria

- [ ] All 21 tasks complete
- [ ] All payment paths work end-to-end (card success, card decline, 3DS, cash, free)
- [ ] Webhook handles all expected events idempotently
- [ ] Receipts generated, validated by accountant, retained per spec
- [ ] All notifications dispatching reliably with proper localization
- [ ] Reminders firing on time
- [ ] Cancel / reschedule / refund flows validated end-to-end with real Stripe test mode
- [ ] CI green; coverage threshold maintained
- [ ] Sentry clean for 48h staging soak with synthetic traffic
- [ ] Sana signs off on user-visible copy and receipt

## Risks

- **WhatsApp template approvals can slip.** Mitigation: submit templates in Foundation; if not approved by end of phase, ship email + SMS, defer WhatsApp until templates approved (no code change needed once approved).
- **Stripe MA card acceptance edge cases.** Test with a real Moroccan card mid-phase, not at the end.
- **3DS UX surprises.** Test on a slow mobile network. Stripe Elements handles most of it; verify ours doesn't fight it.
- **Reminder timezone bugs.** Use `Carbon::setTestNow` in tests and at least one real-clock staging check.
- **Receipt numbering race conditions.** Sequence-based allocation handles this, but verify via parallel insertion test.

## Demo to Sana

90-min session:
1. Walk through a booking in both languages (one online, one in-office), card and cash
2. Show 3DS challenge flow
3. Show the confirmation email + SMS + WhatsApp landing on her phone
4. Receive the receipt PDF in the email
5. Show the booking in Filament: detail view, timeline, actions
6. Cancel a booking from Filament → show refund request + approval flow
7. Reschedule a booking
8. Show the failed-payment fallback message
9. Show the free-orientation flow
10. Discuss the client portal direction for phase 4

Sign-off requested on:
- Booking flow UX
- Notification tone and timing
- Receipt template
- Filament booking management is usable

## Files / artifacts produced

- Full booking + payment system working end-to-end
- All notification templates implemented in both languages
- Receipt generator
- `BookingResource` in Filament with cancel/reschedule/refund actions
- Stripe webhook live on staging
- Comprehensive test suite for the critical path
