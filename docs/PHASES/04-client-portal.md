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

### Task 1: Portal layout

Acceptance:
- [ ] `layouts/portal.blade.php` with header (greeting + "Déconnexion" link + lang toggle), footer (same as public)
- [ ] Mobile-friendly
- [ ] Both languages working
- [ ] Auth required on every nested route (redirect to `/{locale}/portal/login` if not logged in)

### Task 2: Magic-link request

Acceptance:
- [ ] `/{locale}/portal/login` shows email field + submit
- [ ] Rate limits enforced: 3 / hr per email + 10 / hr per IP
- [ ] On submit: `MagicLinkService::send($client)` resolves or creates the Client, generates a token, hashes for storage, sends the `magic_link` email with a signed URL valid 15 minutes
- [ ] Email lands within 30 seconds in real-world test on Resend
- [ ] Friendly success page: "Si vous avez un compte, un lien vient d'arriver dans votre boîte mail."
- [ ] No email enumeration: same response whether the client exists or not (Client created on demand)

### Task 3: Magic-link verify

Acceptance:
- [ ] `GET /portal/login/verify` validates signature, hashes incoming token, looks up `magic_links` row
- [ ] Rejects expired, consumed, or hash-mismatched tokens with translated friendly messages
- [ ] On success: marks `consumed_at`, `Auth::guard('client')->login($client)`, regenerates session, logs event with IP + UA
- [ ] Redirects to `/{locale}/portal`
- [ ] Audit log entry created

### Task 4: Portal home

Acceptance:
- [ ] Greeting with first name from the client record
- [ ] Next upcoming booking card with key actions (View, Cancel, Reschedule)
- [ ] Empty state if no upcoming bookings + CTA "Prendre rendez-vous"
- [ ] Quick links to Bookings, Receipts, Preferences
- [ ] No PII shown beyond the current logged-in client's own
- [ ] Loads in < 300ms on staging

### Task 5: Bookings list

Acceptance:
- [ ] Grouped by status: À venir / En attente / Passés / Annulés
- [ ] Each row shows date, plan, format, reference, status badge
- [ ] Click → detail page
- [ ] Pagination at 20 per page
- [ ] Sorted by `starts_at` (upcoming ascending, past descending)
- [ ] All copy translated

### Task 6: Booking detail

Acceptance:
- [ ] Summary block (plan, date, format, status, reference)
- [ ] For online bookings: Jitsi join link visible 15 min before the slot (a small countdown banner if before that)
- [ ] For in-office: address + small static map
- [ ] Documents section: list with download buttons + "Ajouter un document" if eligible
- [ ] Payment section: amount, method, status; receipt download if available
- [ ] Action buttons gated by policy: Cancel (eligible window), Reschedule (eligible window), Add documents, Contact us
- [ ] Timeline of key events (optional in v1; minimum: created, confirmed, reminders sent, completed)
- [ ] Authorization: 403 if not the booking's client; tested both positive + negative

### Task 7: Cancel flow

Acceptance:
- [ ] Cancel action opens a confirmation panel showing refund preview (amount + policy explanation)
- [ ] Optional cancellation reason (free text)
- [ ] On confirm: `BookingService::cancel($booking, $reason, $client)` (this exists from phase 3)
- [ ] Refund request created if amount > 0; processed via the phase-3 approval flow
- [ ] Success flash + redirect to portal home
- [ ] Cancellation notifications dispatched (already wired in phase 3)
- [ ] Tests: cancellable within window succeeds; within no-refund window succeeds without refund; terminal-state booking rejects with 403

### Task 8: Reschedule flow

Acceptance:
- [ ] `/{locale}/portal/bookings/{reference}/reschedule` reuses the slot-picker component
- [ ] Constraints: same plan, ≥ 2 h before original slot, ≤ 2 reschedules / 30 days / client
- [ ] On submit: `BookingService::reschedule(...)` (from phase 3)
- [ ] New booking confirmed, new reference issued; redirect to new detail page
- [ ] Reschedule notification dispatched
- [ ] Tests: eligibility positive + each ineligibility reason

### Task 9: Document upload from portal

Acceptance:
- [ ] Reuses the booking-flow upload Livewire component
- [ ] Max 20 files total per booking enforced
- [ ] Same mime / size validation
- [ ] On upload: `DocumentService::attachToBooking($booking, $file)` (from phase 3)
- [ ] Refresh shows the new docs
- [ ] Authorization: only the booking's client can upload

### Task 10: Document + receipt download

Acceptance:
- [ ] `GET /portal/bookings/{booking}/documents/{document}` checks `DocumentPolicy::view` → redirects to signed URL (5-min TTL, attachment disposition)
- [ ] `GET /portal/receipts/{receipt}` redirects to signed URL for the receipt PDF
- [ ] Documents in `scan_status=pending` show a disabled button with tooltip (in v1's null-scanner setup this is rare; ready for real scanner)
- [ ] `scan_status=infected` documents not downloadable; portal shows a flag
- [ ] Authorization tests positive + negative

### Task 11: Receipts list

Acceptance:
- [ ] `/{locale}/portal/receipts` lists all receipts for the logged-in client
- [ ] Columns: number, date, amount, booking reference, download
- [ ] Sorted by issued_at desc

### Task 12: Preferences

Acceptance:
- [ ] `/{locale}/portal/preferences` form with: preferred language, notification channels (email always on, SMS / WhatsApp toggleable), preferred contact channel (radio)
- [ ] On save: `clients.preferred_locale` and `clients.notification_preferences` updated
- [ ] Subsequent notifications honor the preferences (verified by a feature test)
- [ ] Audit log entry recorded
- [ ] Account-deletion button rendered at the bottom (separate task)

### Task 13: Account deletion

Acceptance:
- [ ] Two-step: button → confirmation modal requiring typing "SUPPRIMER" (or "حذف" on AR) → final magic-link email confirmation → execution
- [ ] On execution: Client row anonymized per `ARCHITECTURE/auth.md` (email replaced, name "(supprimé)", phone neutralized, national_id cleared)
- [ ] Past bookings, payments, receipts retained (legal record) but de-linked from contactable identity
- [ ] All client's documents purged at next scheduled run (or immediately — implementation choice; document the chosen behavior)
- [ ] Chatbot conversations anonymized
- [ ] Client logged out; landing page shows "Votre compte a été supprimé."
- [ ] Audit log entry created
- [ ] Tests cover the anonymization integrity (no PII left in the anonymized row)

### Task 14: Logout

Acceptance:
- [ ] `POST /{locale}/portal/logout` (CSRF-protected)
- [ ] Server-side session invalidated, CSRF regenerated
- [ ] Redirect to home `/{locale}/`

### Task 15: Filament admin — client data export

This is the "right to access" implementation (Loi 09-08). Lives in admin (it's Sana who runs it on a client's request) but lands in this phase because it's portal-adjacent.

Acceptance:
- [ ] Filament `ClientResource` detail page has an "Exporter les données" action
- [ ] Generates a ZIP containing: PDF summary of all data held, CSV of bookings + payments + receipts metadata, copies of any non-purged documents
- [ ] Generated via a queued job (it can take a few seconds)
- [ ] Download link emailed to the requesting client (admin can also download)
- [ ] Audit log entry created
- [ ] Tested end-to-end with a sample client

### Task 16: Session lifetime enforcement

Acceptance:
- [ ] Idle timeout: 2 hours on `client` guard
- [ ] Hard cap: 24 hours
- [ ] Middleware checks `last_activity_at` and logs out if exceeded
- [ ] Verified via clock manipulation tests

### Task 17: Tests + Dusk

Acceptance:
- [ ] Pest feature tests for every route, both authorized and unauthorized
- [ ] Authorization tests: client A cannot see client B's bookings, documents, receipts
- [ ] Dusk E2E: full magic-link round trip (request email → click link → land on portal → see booking → cancel it → see cancellation)
- [ ] Coverage on portal namespace ≥ 85%

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
