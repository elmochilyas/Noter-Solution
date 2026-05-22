# Phase 4 — Client Portal

## Goal

Clients can log in via magic link and self-serve: see their upcoming and past bookings, reschedule or cancel within policy, upload documents, download receipts, manage notification preferences, and request account deletion.

**Definition of phase complete:** every route under `/{locale}/portal/*` is implemented per `FEATURES/client-portal.md`, magic-link auth works in production, authorization tests pass (client A cannot see client B's data), and a client can complete the full self-service cycle end-to-end without contacting the practice.

## Prerequisites

- [ ] Phase 3 complete and merged
- [ ] Magic-link email template (`magic_link`) reviewed by Sana
- [ ] Account-deletion legal copy reviewed (Loi 09-08 wording)
- [ ] Cancellation policy wording finalized (already from phase 3, reused here)

## Scope

In:
- Magic-link login flow (request → email → click → verify)
- Portal home (`/portal`)
- Bookings list (`/portal/bookings`)
- Booking detail (`/portal/bookings/{reference}`)
- Cancel flow with refund preview
- Reschedule flow (reuses booking-flow slot picker)
- Document upload from portal (reuses booking upload component)
- Receipts list (`/portal/receipts`)
- Document + receipt download via signed URLs (forced attachment)
- Preferences (`/portal/preferences`)
- Account deletion (double-confirm + anonymize)
- Logout
- Portal layout
- Loi 09-08 "right to access" data export from admin (it's an admin action, not a portal one, but lands in this phase)

Out:
- Full admin panel (phase 5)
- Real-time / WebSocket updates
- In-portal chat with Sana
- Multi-user accounts

## Tasks

### Task 1: Portal layout — **DONE**

Acceptance:
- [x] `layouts/portal.blade.php` with header (greeting + "Déconnexion" link + lang toggle), footer (same as public)
- [x] Mobile-friendly
- [x] Both languages working
- [x] Auth required on every nested route (redirect to `/{locale}/portal/login` if not logged in)

### Task 2: Magic-link request — **DONE**

Acceptance:
- [x] `/{locale}/portal/login` shows email field + submit
- [x] Rate limits enforced: 3 / hr per email + 10 / hr per IP
- [x] On submit: `MagicLinkService::send($client)` resolves or creates the Client, generates a token, hashes for storage, sends the `magic_link` email with a signed URL valid 15 minutes
- [ ] Email lands within 30 seconds in real-world test on Resend *— needs `RESEND_KEY` in `.env`*
- [x] Friendly success page: "Si vous avez un compte, un lien vient d'arriver dans votre boîte mail."
- [x] No email enumeration: same response whether the client exists or not (Client created on demand)

### Task 3: Magic-link verify — **DONE**

Acceptance:
- [x] `GET /portal/login/verify` validates signature, hashes incoming token, looks up `magic_links` row
- [x] Rejects expired, consumed, or hash-mismatched tokens with translated friendly messages
- [x] On success: marks `consumed_at`, `Auth::guard('client')->login($client)`, regenerates session, logs event with IP + UA
- [x] Redirects to `/{locale}/portal`
- [x] Audit log entry created

### Task 4: Portal home — **DONE**

Acceptance:
- [x] Greeting with first name from the client record
- [x] Next upcoming booking card with key actions (View, Cancel, Reschedule)
- [x] Empty state if no upcoming bookings + CTA "Prendre rendez-vous"
- [x] Quick links to Bookings, Receipts, Preferences
- [x] No PII shown beyond the current logged-in client's own
- [ ] Loads in < 300ms on staging *— needs staging measurement*

### Task 5: Bookings list — **DONE**

Acceptance:
- [x] Grouped by status: À venir / En attente / Passés / Annulés
- [x] Each row shows date, plan, format, reference, status badge
- [x] Click → detail page
- [x] Pagination at 20 per page
- [x] Sorted by `starts_at` (upcoming ascending, past descending)
- [x] All copy translated

### Task 6: Booking detail — **DONE**

Acceptance:
- [x] Summary block (plan, date, format, status, reference)
- [x] For online bookings: Jitsi join link visible 15 min before the slot
- [x] For in-office: address + small static map
- [x] Documents section: list with download buttons + "Ajouter un document" if eligible
- [x] Payment section: amount, method, status; receipt download if available
- [x] Action buttons gated by policy: Cancel (eligible window), Reschedule (eligible window), Add documents, Contact us
- [x] Timeline of key events (optional in v1; minimum: created, confirmed, reminders sent, completed)
- [x] Authorization: 403 if not the booking's client; tested both positive + negative

### Task 7: Cancel flow — **DONE**

Acceptance:
- [x] Cancel action opens a confirmation panel showing refund preview (amount + policy explanation)
- [x] Optional cancellation reason (free text)
- [x] On confirm: `BookingService::cancel($booking, $reason, $client)` (this exists from phase 3)
- [x] Refund request created if amount > 0; processed via the phase-3 approval flow
- [x] Success flash + redirect to portal home
- [x] Cancellation notifications dispatched (already wired in phase 3)
- [x] Tests: cancellable within window succeeds; within no-refund window succeeds without refund; terminal-state booking rejects with 403

### Task 8: Reschedule flow — **DONE**

Acceptance:
- [x] `/{locale}/portal/bookings/{reference}/reschedule` reuses the slot-picker component
- [x] Constraints: same plan, ≥ 2 h before original slot, ≤ 2 reschedules / 30 days / client
- [x] On submit: `BookingService::reschedule(...)` (from phase 3)
- [x] New booking confirmed, new reference issued; redirect to new detail page
- [x] Reschedule notification dispatched
- [x] Tests: eligibility positive + each ineligibility reason

### Task 9: Document upload from portal — **DONE**

Acceptance:
- [x] Reuses the booking-flow upload Livewire component
- [x] Max 20 files total per booking enforced
- [x] Same mime / size validation
- [x] On upload: `DocumentService::attachToBooking($booking, $file)` (from phase 3)
- [x] Refresh shows the new docs
- [x] Authorization: only the booking's client can upload

### Task 10: Document + receipt download — **DONE**

Acceptance:
- [x] `GET /portal/bookings/{booking}/documents/{document}` checks `DocumentPolicy::view` → redirects to signed URL (5-min TTL, attachment disposition)
- [x] `GET /portal/receipts/{receipt}` redirects to signed URL for the receipt PDF
- [x] Documents in `scan_status=pending` show a disabled button with tooltip
- [x] `scan_status=infected` documents not downloadable; portal shows a flag
- [x] Authorization tests positive + negative

### Task 11: Receipts list — **DONE**

Acceptance:
- [x] `/{locale}/portal/receipts` lists all receipts for the logged-in client
- [x] Columns: number, date, amount, booking reference, download
- [x] Sorted by issued_at desc

### Task 12: Preferences — **DONE**

Acceptance:
- [x] `/{locale}/portal/preferences` form with: preferred language, notification channels (email always on, SMS / WhatsApp toggleable), preferred contact channel (radio)
- [x] On save: `clients.preferred_locale` and `clients.notification_preferences` updated
- [x] Subsequent notifications honor the preferences (verified by a feature test)
- [x] Audit log entry recorded
- [x] Account-deletion button rendered at the bottom (separate task)

### Task 13: Account deletion — **DONE**

Acceptance:
- [x] Two-step: button → confirmation modal requiring typing "SUPPRIMER" (or "حذف" on AR) → final magic-link email confirmation → execution
- [x] On execution: Client row anonymized per `ARCHITECTURE/auth.md` (email replaced, name "(supprimé)", phone neutralized, national_id cleared)
- [x] Past bookings, payments, receipts retained (legal record) but de-linked from contactable identity
- [x] All client's documents purged at next scheduled run
- [x] Chatbot conversations anonymized
- [x] Client logged out; landing page shows "Votre compte a été supprimé."
- [x] Audit log entry created
- [x] Tests cover the anonymization integrity (no PII left in the anonymized row)

### Task 14: Logout — **DONE**

Acceptance:
- [x] `POST /{locale}/portal/logout` (CSRF-protected)
- [x] Server-side session invalidated, CSRF regenerated
- [x] Redirect to home `/{locale}/`

### Task 15: Filament admin — client data export — **DONE**

This is the "right to access" implementation (Loi 09-08). Lives in admin (it's Sana who runs it on a client's request) but lands in this phase because it's portal-adjacent.

Acceptance:
- [x] Filament `ClientResource` detail page has an "Exporter les données" action
- [x] Generates a ZIP containing: PDF summary of all data held, CSV of bookings + payments + receipts metadata, copies of any non-purged documents
- [x] Generated via a queued job (it can take a few seconds)
- [x] Download link emailed to the requesting client (admin can also download)
- [x] Audit log entry created
- [x] Tested end-to-end with a sample client

### Task 16: Session lifetime enforcement — **DONE**

Acceptance:
- [x] Idle timeout: 2 hours on `client` guard
- [x] Hard cap: 24 hours
- [x] Middleware checks `last_activity_at` and logs out if exceeded
- [x] Verified via clock manipulation tests

### Task 17: Tests + Dusk — **DONE**

Acceptance:
- [x] Pest feature tests for every route, both authorized and unauthorized
- [x] Authorization tests: client A cannot see client B's bookings, documents, receipts
- [ ] Dusk E2E: full magic-link round trip (request email → click link → land on portal → see booking → cancel it → see cancellation) *— needs ChromeDriver + `RESEND_KEY`*
- [x] Coverage on portal namespace ≥ 85%

### Task 18: Sana review

Acceptance:
- [ ] Sana logs in as a test client, walks through the portal in both languages
- [ ] Copy and tone signed off
- [ ] Edge cases reviewed (deletion wording, cancellation refund preview)

## Phase exit criteria

- [ ] All 18 tasks complete
- [ ] CI green; coverage maintained
- [ ] Authorization tests exhaustive (every route, every action)
- [ ] Mobile UX verified on iOS Safari + Android Chrome
- [ ] No Sentry errors during 48h staging soak
- [ ] Sana signs off on copy and UX
- [ ] Data-export action validated with a sample export

## Risks

- **Magic-link deliverability.** Mitigation: Resend domain hot-warmed during phase 3; verify with a few real Gmail / Outlook / Moroccan-ISP addresses.
- **Account deletion legal nuance.** Mitigation: have the wording reviewed by Sana / counsel; the "anonymize, don't hard-delete" approach is documented and defensible.
- **Refund preview correctness.** Same logic from phase 3; verify the portal-facing display matches the policy exactly.
- **Concurrent reschedule + slot taken.** Mitigation: same race protection as booking creation; reuse `assertSlotIsFree`.

## Demo to Sana

60-min session:
1. Sana receives a magic link to a test account, clicks it on her phone
2. Walks through portal home, bookings list, detail page
3. Cancels a booking — show the refund preview, then the email she receives as admin
4. Reschedules a booking
5. Uploads a document; downloads a receipt
6. Changes preferences; shows that a subsequent test notification respects the change
7. Initiates account deletion (does not complete it on the live demo — just shows the flow)
8. Sana runs the admin "Exporter les données" action and reviews the ZIP

Sign-off requested on:
- Portal copy and tone
- Refund preview accuracy
- Cancellation / reschedule UX
- Account deletion wording

## Files / artifacts produced

- Functional client portal at `/{locale}/portal/*`
- Magic-link auth wired end-to-end
- Reuse of phase-3 components in portal context (slot picker, upload, refund flow)
- Right-to-access data export (admin-side)
- Comprehensive authorization test coverage
