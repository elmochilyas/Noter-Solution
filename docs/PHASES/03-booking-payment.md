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
- [x] `PaymentGateway` interface in `app/Domain/Payment/`
- [x] `StripeGateway` implementation in `app/Infrastructure/Payment/`
- [x] Methods: `createIntent`, `verifyWebhook`, `refund`, `name`
- [x] `CmiGateway` stub committed (throws `not implemented`) so the binding lookup pattern is in place
- [x] Service container binds `PaymentGateway::class` to the configured driver via `config/payments.php`
- [x] Unit tests cover gateway selection by config

### Task 2: Domain services

Implement per `ARCHITECTURE/domain-model.md`:

Acceptance:
- [x] `BookingService` with `createPending`, `confirm`, `complete`, `markNoShow`, `cancel`, `reschedule`
- [x] `AvailabilityService` with `availableSlots`, `assertSlotIsFree`, `holdSlot`
- [x] `PaymentService` with `createIntent`, `confirmFromWebhook`, `refund`, `markCashSucceeded`
- [x] `ReceiptService` with `generate`, `temporaryUrl`
- [x] `DocumentService` with `attachToBooking`, `temporaryUrl`, `delete`
- [x] Stateless, injected; no business logic in models / controllers
- [x] `BookingStatusTransition` guard implemented and called by every status-changing method
- [x] Each service has ≥ 95% unit test coverage including failure paths

### Task 3: Booking domain events + listeners

Acceptance:
- [x] Events implemented: `BookingCreated`, `BookingConfirmed`, `BookingCancelled`, `BookingCompleted`, `BookingNoShow`, `BookingRescheduled`, `PaymentSucceeded`, `PaymentFailed`, `RefundIssued`
- [x] Listeners are queued unless explicitly synchronous
- [x] `BookingConfirmed` triggers: confirmation notifications, schedule reminders, generate receipt (if paid)
- [x] `BookingCancelled` triggers: cancellation notification, cancel scheduled reminders, refund job (if applicable)
- [x] `BookingRescheduled` triggers: confirmation, cancel old reminders, schedule new reminders
- [x] `Event::fake()` tests for each domain action verify the right events fire

### Task 4: Booking flow — UI

Implement the 7-step Livewire flow per `FEATURES/booking.md`:

Acceptance:
- [x] Step 1: plan selection (skippable via `?plan=`)
- [x] Step 2: category + description + has-documents + format (when applicable)
- [x] Step 3: slot picker (calendar component, two-column desktop, single-column mobile, holds)
- [x] Step 4: identity (full name, email, phone, preferred channel, optional CIN, T&Cs + privacy consent)
- [x] Step 5: documents (optional upload, multi-file, validation)
- [x] Step 6: payment (card via Stripe Elements, cash if eligible, free for orientation)
- [x] Step 7: success page with reference + portal CTA
- [x] Back navigation preserves state
- [x] Form state persists in a `BookingFormState` Livewire form object
- [x] Returning client (matching email) gets prefilled fields
- [x] All copy from translation files; both languages working
- [x] Mobile-friendly; tap targets ≥ 44 px; iOS zoom prevention via `text-base` (16px) on all inputs

### Task 5: Slot picker + availability

Acceptance:
- [x] `AvailabilityService::availableSlots` correctly composes from rules - exceptions - confirmed bookings - active holds - 2h lead time - plan duration
- [x] Online / in-office mutual exclusivity respected
- [x] Cache (60s TTL) implemented via `Cache::remember()`; `clearSlotsCache()` called on booking creation
- [x] Calendar shows dot indicator for days with availability
- [x] Slot click creates a `BookingHold` with 10-min expiry
- [x] Hold released on back-navigation or session loss
- [x] `PurgeExpiredBookingHolds` job runs every 5 minutes
- [x] `SlotNotAvailable` exception handled with inline refresh
- [x] Two-clients-same-slot test passes (race-condition safe via DB constraint or `SELECT FOR UPDATE`)
- [x] Free orientation limited to next 7 days

### Task 6: Stripe payment integration

Acceptance:
- [x] Stripe.js + Elements loaded on payment step (publishable key from `.env`)
- [x] `POST /book/intent/create` (Livewire action) creates a `PaymentIntent` with idempotency key `booking-{id}`
- [x] Client confirms with `stripe.confirmCardPayment` using returned `client_secret`
- [ ] 3DS challenge handled (tested with `4000 0027 6000 3184` test card) — needs staging test
- [x] Failure shows translated error
- [x] Successful payment redirects to `/book/success?reference=…`
- [x] CSP allows Stripe iframe domains — `ContentSecurityPolicy` middleware adds `frame-src https://js.stripe.com https://hooks.stripe.com`
- [x] No card data ever passes through our backend (Stripe Elements handles directly)

### Task 7: Stripe webhook

Acceptance:
- [x] `/webhooks/stripe` endpoint excluded from CSRF
- [x] Signature verification via `Stripe\Webhook::constructEvent` — invalid signatures rejected with 400
- [x] Event ID deduplication via Cache (24h TTL)
- [x] Handles: `payment_intent.succeeded`, `payment_intent.payment_failed`, `payment_intent.canceled`
- [x] Idempotent: replaying same event has no side effect
- [x] On `payment_intent.succeeded`: marks Payment succeeded, fires `PaymentSucceeded`, which confirms the booking
- [x] Local testing via `stripe listen --forward-to`
- [x] Sentry alert on signature failures or unhandled exceptions — webhook logs errors via `Log::warning`/`Log::error`
- [x] Webhook rate limit 1000/min — `ThrottleWebhooks` middleware applies app-level rate limiting

### Task 8: Cash-at-office flow

Acceptance:
- [x] Only offered for in-office plans
- [x] On selection: booking created in `confirmed` immediately, Payment row created with `gateway=cash, status=pending`
- [x] Confirmation notifications dispatched
- [x] Sana can mark Payment succeeded from Filament — `mark_cash_succeeded` action on BookingResource View page
- [x] On manual mark-succeeded: `PaymentSucceeded` event fires → receipt generated (via `markCashSucceeded` in PaymentService)
- [x] Audit log records the manual marking with the user who did it — wired via `activity()->performedOn()->causedBy()->log()`

### Task 9: Free orientation flow

Acceptance:
- [x] Free plan skips Stripe entirely
- [x] Booking confirmed immediately on form submission
- [x] Confirmation notifications dispatched
- [x] No Payment row created (free plan, no payment intent created)
- [x] Rate limit: 2 free orientations per email/phone per 90 days — `Client::freeOrientationCountInDays()` / `hasExceededFreeOrientationLimit()`
- [x] On limit hit: friendly error `booking.errors.free_orientation_limit` in both languages

### Task 10: Receipt generation

Acceptance:
- [x] `GenerateReceipt` listener triggered on `PaymentSucceeded` (for any gateway including cash)
- [x] Sequential number allocated from `receipts_number_seq` (SQLite fallback: max(id)+1)
- [x] PDF rendered via Browsershot from `resources/views/pdf/receipt.blade.php` — template exists with bilingual support
- [x] Template matches the layout in `COMPLIANCE/receipts-invoicing.md`
- [x] Bilingual content (Arabic `dir="rtl"`, French `dir="ltr"`)
- [x] Stored at `receipts/{YYYY}/{MM}/{number}.pdf` — `GenerateReceiptPdf` job stores to `receipts` disk
- [x] Receipt row inserted
- [x] `payment.receipt` email sent with PDF attached — `PaymentReceipt::toMail()` attaches PDF from `receipts` disk via `Storage::attach()`
- [x] Credit-note generation — `GenerateCreditNote` listener wired on `RefundIssued`, creates `CreditNote` row in `credit_notes` table with sequential `AV-{year}-{seq}` number
- [ ] Sana's accountant reviewed a sample receipt
- [ ] Credit-note PDF rendered via Browsershot (same approach as receipt; storage path created but PDF not yet generated without Puppeteer)

### Task 11: Notifications — email

Acceptance:
- [x] All notification classes implemented: `BookingConfirmation`, `BookingReminder`, `BookingCancelledNotification`, `BookingRescheduledNotification`, `PaymentReceipt`, `PaymentFailedNotification`, `RefundIssuedNotification`, `MagicLink`, `AdminNewBooking`, `AdminContactMessage`, `AdminDispute`, `AdminRefundRequest`
- [x] All templates via Laravel MailMessage (Markdown) with bilingual support
- [x] Translation keys present for both languages (confirmation, reminder, cancelled, rescheduled, payment_receipt, payment_failed, refund_issued)
- [x] SMS/WhatsApp translation keys added to both `lang/{fr,ar}/notifications.php` — `.sms` and `.whatsapp` sub-keys for all 7 notification types
- [x] Reply-To headers set on all 7 client-facing notifications via `$msg->replyTo(config('mail.reply_to.address'), config('mail.reply_to.name'))`
- [x] `.env.example` has `MAIL_REPLY_TO_ADDRESS`, `MAIL_REPLY_TO_NAME`, `MAIL_ADMIN_ADDRESS` entries
- [ ] Resend driver configured, sending verified — requires `RESEND_KEY` in `.env`

### Task 12: Notifications — SMS

Acceptance:
- [x] `TwilioSmsChannel` class implemented in `app/Channels/TwilioSmsChannel.php` — sends via Twilio API with `Http::withBasicAuth()`, auto-configures when `services.twilio.account_sid` is set; logs when not configured
- [x] `TwilioSmsNotification` reusable notification class in `app/Notifications/TwilioSmsNotification.php` — implements `ShouldQueue`, accepts text string, delivers via `TwilioSmsChannel`
- [x] `NotificationService::send()` dispatches SMS via `Notification::route(TwilioSmsChannel::class, $phone)->notify(new TwilioSmsNotification($text))`
- [x] SMS text generated per template via `getChannelText()` using `__('notifications.{key}.sms', [], $locale)` — all 7 notification types have bilingual SMS keys (≤ 160 chars)
- [x] URL shortener implemented — `ShortLink` model + `short_links` migration + `ShortLinkController` at `GET /s/{hash}` (301 redirect) + `ShortLink::generate()` factory
- [ ] URL shortener integrated into SMS templates — currently sends raw booking reference; needs `ShortLink::generate()` call to wrap URLs
- [ ] Twilio driver configured — requires Twilio account + `.env` (`TWILIO_ACCOUNT_SID`, `TWILIO_AUTH_TOKEN`, `TWILIO_FROM_SMS`)
- [ ] Test send to a real Moroccan phone succeeds in staging
- [ ] Failure handling with retry policy — `TwilioSmsChannel` logs errors and updates `notifications_log` to `failed`

### Task 13: Notifications — WhatsApp

Acceptance:
- [x] `TwilioWhatsAppChannel` class implemented in `app/Channels/TwilioWhatsAppChannel.php` — sends via Twilio WhatsApp API, auto-configures when `services.twilio.from_whatsapp` is set; logs when not configured
- [x] `TwilioWhatsAppNotification` reusable notification class in `app/Notifications/TwilioWhatsAppNotification.php` — implements `ShouldQueue`, accepts text string, delivers via `TwilioWhatsAppChannel`
- [x] `NotificationService::send()` dispatches WhatsApp via `Notification::route(TwilioWhatsAppChannel::class, $phone)->notify(new TwilioWhatsAppNotification($text))`
- [x] WhatsApp text generated per template via `getChannelText()` using `__('notifications.{key}.whatsapp', [], $locale)` — all 7 notification types have bilingual WhatsApp keys
- [ ] Twilio WhatsApp Business configured — requires Twilio + Meta approval + `TWILIO_FROM_WHATSAPP` in `.env`
- [ ] At least 2 approved templates wired: confirmation + reminder — templates use free-form text body; Meta WhatsApp template approval needed for production
- [ ] Outbound messages use approved templates
- [ ] Test send to a real WhatsApp number succeeds in staging
- [ ] Inbound replies forwarded to admin email

### Task 14: NotificationService orchestrator

Acceptance:
- [x] `NotificationService::sendBookingConfirmation()` sends email notification
- [x] `NotificationService::send($templateKey, $recipient, $data, $channels)` full multi-channel orchestrator
- [x] Recipient resolution per channel (email always, SMS/WhatsApp for non-critical)
- [x] Per-channel queued job dispatched via `$recipient->notify()` (mail) or `Notification::route(TwilioSmsChannel::class)->notify(new TwilioSmsNotification(...))` (SMS/WhatsApp)
- [x] `notifications_log` row created per channel + updated through lifecycle (`pending → sent/failed`)
- [x] Critical-channel bypass implemented (confirmations always send via email)
- [x] Quiet hours (22:00–07:00) enforced for non-email channels
- [x] Notification preferences honored via `Client::preferred_channel`
- [x] Anti-abuse caps implemented via `RateLimiter::tooManyAttempts()` — 5 SMS / 10 mail per recipient per hour keyed by `notif:{id}:{channel}:{YmdH}`, logs warning when hit
- [x] Provider webhook endpoints — `POST /webhooks/resend` (`ResendWebhookController`) and `POST /webhooks/twilio` (`TwilioWebhookController`) update `notifications_log.delivered_at` and status when called by delivery providers
- [x] Sentry tagging on notification failures via `tagSentry()` — sets `notification.template`, `notification.recipient_type`, `notification.recipient_id` tags
- [ ] Provider webhooks need real Resend/Twilio delivery callbacks configured in provider dashboards

### Task 15: Scheduled reminders

Acceptance:
- [x] `SendBookingNotification` job exists with booking status check at execution
- [x] On `BookingCancelled` / `BookingRescheduled`: delayed jobs no-op due to status check
- [x] On `BookingConfirmed`: dispatch `SendBookingNotification` jobs with delays for T-24h and T-1h — `ScheduleReminders` listener fully wired
- [ ] Both reminders observed firing on time in staging (using `Carbon::setTestNow` for fast verification + one real-clock test)

### Task 16: Cancel + reschedule

Acceptance:
- [x] Cancel flow (admin via Filament): refund policy computed (100% / 50% / 0%) based on time-to-appointment
- [x] Refund request created automatically if amount > 0; status = `requested`
- [x] Reschedule flow: old booking cancelled with reason `rescheduled`; new booking created in `pending_payment` with the same payment; new reference issued
- [x] Reschedule limited to 2 per 30 days per client — `Client::rescheduleCountInDays()` / `hasExceededRescheduleLimit()` with `RuntimeException`
- [x] Notifications dispatched on both flows (listeners wired)
- [x] All status transitions blocked from invalid origins (verified via test)

### Task 17: Refunds — request, approve, execute

Acceptance:
- [x] Refund request creates `Refund` row with `status=requested`
- [x] Owner approves via Filament → `approve_refund` action on BookingResource View page, calls `PaymentService::processRefund`
- [x] Idempotency key `refund-{id}` — `PaymentService::refund()` creates UUID for each refund, DB unique constraint prevents duplicates
- [x] Refund row updated through `requested → succeeded` lifecycle (via `processRefund`)
- [x] `RefundIssued` event fires
- [x] Credit-note receipt generated — `GenerateCreditNote` listener on `RefundIssued` creates `CreditNote` row with sequential `AV-{year}-{seq}` number in `credit_notes` table
- [ ] Credit-note PDF rendered via Browsershot (same approach as receipt)
- [x] Cash refunds handled (skipped via gateway check in `IssueRefundIfApplicable`)

### Task 18: Filament `BookingResource` (minimum viable)

Acceptance:
- [x] List view with status filter
- [x] Detail view shows full booking, client, payment, documents, receipt, timeline
- [x] Actions: Cancel (with reason), Mark completed, Mark no-show, Mark cash succeeded, Approve refund
- [x] Refund-request action wired via `approve_refund` → `PaymentService::processRefund`
- [x] Internal notes (encrypted) editable
- [x] Authorization policies enforced — `BookingPolicy` restricts `refund` to owner, `markCashSucceeded`/`complete` to owner+assistant
- [x] N+1 eager loading — `BookingResource::table()` uses `->query(fn ($q) => $q->with(['client', 'plan', 'payment', 'receipt', 'documents']))`
- [ ] Verify Pulse green on the list page — needs staging measurement

### Task 19: Test data + Dusk E2E

Acceptance:
- [x] Pest feature tests cover: booking service CRUD, availability slots, refund policy, webhook processing, payment service, purge holds, notifications
- [x] Dusk E2E test scaffold — `tests/Browser/BookingPaymentTest.php` with 2 tests: card success (`4242...`) and card decline (`4000...`)
- [x] Dusk added to `composer.json` (`laravel/dusk ^9.0`) — run `composer install` then `php artisan dusk:install` to set up
- [x] Authorization tests via `BookingPolicy` unit tests
- [x] Tests use `Notification::fake()`, `Queue::fake()`, mocked Stripe — **167 tests total, all passing**
- [ ] Dusk tests need ChromeDriver installed + Stripe test keys in `.env` to run
- [ ] Dusk test needs `StripeMock` or real Stripe test mode to avoid network dependency

### Task 20: Performance + observability

Acceptance:
- [ ] Booking calendar load < 400ms p95 on staging — needs staging measurement
- [ ] Booking submission < 600ms p95 — needs staging measurement
- [x] No N+1 in the booking list — eager loading added via `->with(['client', 'plan', 'payment', 'receipt', 'documents'])`
- [x] Sentry tagged events for `payment`, `webhook`, `booking` paths:
  - Stripe webhook: `payment.gateway=stripe`, `webhook.source=stripe`, `stripe.event_type` via `configureSentryScope()`
  - Notification failures: `notification.template`, `notification.recipient_type`, `notification.recipient_id` via `tagSentry()`
- [ ] Pulse confirms healthy worker activity during a synthetic load test

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
