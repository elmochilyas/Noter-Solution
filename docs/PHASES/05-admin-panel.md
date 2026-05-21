# Phase 5 — Admin Panel

## Goal

Sana and her assistant can operate the practice from Filament: manage bookings, clients, plans, availability, content, payments, receipts, refunds, documents, notifications, contact messages, users, settings — with a useful dashboard and full audit-logging.

**Definition of phase complete:** every resource and page in `FEATURES/admin-panel.md` exists with the correct permissions, the KPI dashboard renders accurate data, Sana can run the day-to-day from the admin without dropping into the database, and authorization tests pass for owner vs. assistant on every gated action.

## Prerequisites

- [ ] Phase 4 complete and merged
- [ ] BookingResource minimum-viable already exists from phase 3 (extend, not rebuild)
- [ ] ServiceResource / FaqResource / ConsultationPlanResource from phase 2 (extend)
- [ ] Sana ready for 60-min training session at the end of the phase

## Scope

In:
- Extend `BookingResource` to the full spec
- `ClientResource`
- `AvailabilityResource` (Rules + Exceptions, calendar widget)
- `PaymentResource`
- `RefundResource`
- `ReceiptResource` (incl. credit-note generation)
- `DocumentResource`
- `ContactMessageResource`
- `NotificationLogResource`
- `ActivityLogResource` (read-only)
- `UserResource` (owner only)
- KPI dashboard widgets
- Reports page (monthly CSV / PDF zip)
- Settings page (extends phase-1 stub: refund policy, quiet hours, VAT defaults, feature flags)
- Custom Filament theme refinement (brass palette, fonts, sidebar grouping)
- User-invite flow
- Global search
- Sensitive-read audit logging
- Pulse integration link

Out:
- `ChatbotConversationResource` content review (phase 6 — this phase ships the resource skeleton only if chatbot data exists; otherwise wait)
- Customer review / testimonial features (forbidden — see `COMPLIANCE/notary-rules.md`)
- Real-time WebSocket updates
- Custom report builder

## Tasks

### Task 1: BookingResource — full spec

Extend the phase-3 minimum-viable resource per `FEATURES/admin-panel.md` BookingResource section.

Acceptance:
- [ ] List view: all columns, all filters, all bulk actions
- [ ] Detail view: client link, full payment timeline, document list, receipt link, internal notes (encrypted), audit timeline
- [ ] All actions per spec (Cancel, Reschedule, Mark completed, Mark no-show, Resend confirmation, Resend receipt, "View as client" read-only)
- [ ] "View as client" logs an audit entry and renders read-only portal view
- [ ] Permissions enforced (assistant restricted from sensitive actions)
- [ ] N+1 free
- [ ] Search across reference, client name, email, phone working
- [ ] Bulk export to CSV

### Task 2: ClientResource

Acceptance:
- [ ] List: name, email, phone, booking count, last booking date, status
- [ ] Filters: locale, has-active-booking, no-bookings-90d
- [ ] Detail: profile, booking history, documents across bookings, notifications log, activity log filtered to this client
- [ ] Actions: Export data (zips per phase 4), Anonymize, Disable / re-enable
- [ ] Anonymize triggers the same path as portal account deletion
- [ ] Permissions: assistant can view + edit basic; only owner can anonymize / delete

### Task 3: AvailabilityResource

Acceptance:
- [ ] Two sub-resources: Rules (weekly) and Exceptions (date ranges, holidays)
- [ ] Calendar widget on the resource home showing rules + exceptions in current month
- [ ] Edit forms for both
- [ ] Bulk action: "Add Moroccan public holidays for [year]" populates known Moroccan public holidays for the selected year
- [ ] Availability changes invalidate the slot-picker cache (60s TTL takes care of most; immediate clear on save is even better)
- [ ] Permissions: both roles can edit

### Task 4: PaymentResource

Acceptance:
- [ ] List: gateway intent ID, booking ref, amount, status, paid_at, refund-status column
- [ ] Filters: status, gateway, date range
- [ ] Detail: read-only payment, linked booking, refund history, "Request refund" action
- [ ] Permissions: assistant can request refund; only owner approves
- [ ] Search by intent ID or booking reference

### Task 5: RefundResource

Acceptance:
- [ ] List: payment link, amount, reason, requested_by, approved_by, status
- [ ] Filter: status (requested / approved / succeeded / failed)
- [ ] Detail: full row + Approve / Reject actions
- [ ] Approve triggers the Stripe API refund (or cash credit-note for cash payments)
- [ ] Failed refund surfaces error + retry option
- [ ] Permissions: owner approves; assistant can only request

### Task 6: ReceiptResource

Acceptance:
- [ ] List: number, booking ref, amount, issued_at, download button
- [ ] Filters: year, month
- [ ] Detail: download PDF, view metadata, "Generate credit note" action (creates referencing credit note)
- [ ] Search by number / booking ref
- [ ] Permissions: both roles can view + download

### Task 7: DocumentResource

Acceptance:
- [ ] List: filename, booking ref, client, mime, size, scan_status, purge_after
- [ ] Filters: scan_status, booking, expired
- [ ] Detail: preview thumbnail (images), download, delete (with reason; audit-logged)
- [ ] Permissions: `documents.view.all` for both roles; delete owner-only

### Task 8: ContactMessageResource

Acceptance:
- [ ] List: name, email, subject, created_at, handled badge, snippet
- [ ] Detail: full message + "Mark as handled" action (capture handler + note)
- [ ] Filters: handled, subject
- [ ] Both roles can manage

### Task 9: NotificationLogResource

Acceptance:
- [ ] List: timestamp, channel, template_key, recipient (pseudonymized), status, sent/delivered/failed timestamps
- [ ] Filters: channel, status, template key, date range
- [ ] Search by recipient
- [ ] Detail: full row, raw payload, "Re-send" action (re-queues original send)
- [ ] Permissions: both roles can view; only owner can re-send sensitive templates (e.g. magic_link)

### Task 10: ActivityLogResource (read-only)

Acceptance:
- [ ] List: timestamp, actor, action, subject type + id
- [ ] Filters: actor, action, subject type
- [ ] Detail: full event with before/after diff
- [ ] Sortable / searchable
- [ ] Cannot be edited via UI (model has no edit form)
- [ ] Permissions: owner only

### Task 11: UserResource (owner only)

Acceptance:
- [ ] List: name, email, role, last login, active
- [ ] Detail: profile + 2FA status + active toggle + "Reset 2FA" + "Force logout" actions
- [ ] Create: invite flow (email signed link 72h, invitee sets password + 2FA)
- [ ] Edit limited to non-sensitive fields (cannot edit email of another user without explicit confirm)
- [ ] Cannot disable the last active owner (system enforces)
- [ ] All actions audit-logged

### Task 12: KPI dashboard

Acceptance:
- [ ] Widgets: Bookings this week / month, Revenue this week / month, Pending payments, Active bookings (next 7d), Chatbot conversations + deflection rate (will be 0 until phase 6), Notification success rate (24h), Failed jobs (24h)
- [ ] Charts: Bookings per day (last 30d), Bookings by category (pie), Bookings by plan (pie)
- [ ] Quick action cards: Today's bookings, Pending refund approvals, Unhandled contact messages
- [ ] Loads in < 800ms p95
- [ ] Widgets lazy-loaded individually
- [ ] Polling every 30s (no WebSocket in v1)

### Task 13: Reports page

Acceptance:
- [ ] Date-range picker
- [ ] Monthly receipts CSV export (per `COMPLIANCE/receipts-invoicing.md`)
- [ ] Monthly receipt PDFs as a single ZIP
- [ ] Annual summary CSV
- [ ] Format imports cleanly into common Moroccan accounting tools (test with Sana's accountant)
- [ ] Export queued (large exports don't block UI)
- [ ] Link emailed to admin when ready

### Task 14: Settings page

Acceptance:
- [ ] Practice info (ICE, IF, RC, Patente, address, phones, email, hours, bank info for receipts) — owner only edits
- [ ] Refund policy thresholds (default 24h / 2h)
- [ ] Quiet hours (default 22:00–07:00)
- [ ] Booking lead time (default 2h)
- [ ] VAT defaults
- [ ] Feature flags (chatbot enabled, online payment enabled, free orientation enabled)
- [ ] Stored in a `settings` table (key-value), cached
- [ ] All changes audit-logged

### Task 15: Filament theme + sidebar grouping

Acceptance:
- [ ] Brass primary color in Filament theme
- [ ] Inter font for UI, Fraunces accents on detail-page headings
- [ ] Sidebar grouping per `FEATURES/admin-panel.md`: Quotidien / Clients / Paiements / Contenu / Chatbot / Système
- [ ] Login page styled to match the brand (sober, ink + parchment + brass)
- [ ] Dark mode disabled
- [ ] Mobile sidebar collapses correctly

### Task 16: User-invite flow

Acceptance:
- [ ] Owner creates a new user → email invitation signed link valid 72 h
- [ ] Invitee clicks → password + 2FA setup form
- [ ] On completion, account active, redirected to `/admin`
- [ ] Resend invite option
- [ ] Revoke invite option
- [ ] Audit logged

### Task 17: Sensitive-read audit logging

Acceptance:
- [ ] Admin viewing a document via DocumentResource is audit-logged
- [ ] Admin viewing internal notes is audit-logged (one entry per booking detail page open, throttled to 1/hr/booking/user to avoid log spam)
- [ ] Admin viewing client's full data export action is audit-logged

### Task 18: Global search

Acceptance:
- [ ] Filament's global search (Cmd+K) indexes: Bookings (by reference, client), Clients (by name/email/phone), FAQs, Services
- [ ] Returns results in <200ms locally
- [ ] Respects authorization (assistant doesn't see hidden resources)

### Task 19: Pulse integration

Acceptance:
- [ ] `/admin/pulse` link visible only to owner role
- [ ] Filament navigation entry under "Système"
- [ ] Recorders enabled: requests, slow queries, slow jobs, queues, exceptions, user requests, servers
- [ ] 7-day retention configured

### Task 20: Tests

Acceptance:
- [ ] Authorization tests for every action: owner allowed, assistant allowed/denied as per matrix
- [ ] Feature tests for refund-approval flow, anonymize action, user-invite flow, data-export action
- [ ] Dusk test: admin logs in, navigates to a booking, cancels it, observes refund request created
- [ ] N+1 verified absent on all list pages via Pulse

### Task 21: Sana training

Acceptance:
- [ ] 60-min session walking Sana through the admin
- [ ] Cheat sheet PDF generated (1-pager: common actions, where to find things)
- [ ] Sana's questions captured + addressed in follow-up PRs

## Phase exit criteria

- [ ] All 21 tasks complete
- [ ] Permissions matrix enforced for owner vs. assistant on every action
- [ ] KPI dashboard accurate (cross-checked against raw DB queries)
- [ ] All sensitive admin actions audit-logged
- [ ] CI green; coverage maintained
- [ ] Performance: every list page < 500ms p95
- [ ] Sana trained and signs off

## Risks

- **Filament resource creep.** Mitigation: stick to the spec; extras go to a v1.x list.
- **N+1 in detail pages.** Mitigation: explicit eager-loading on every Filament resource's query; Pulse used as a regression catch.
- **Sensitive-read audit logging volume.** Mitigation: throttling per user per resource to keep audit table tame.
- **Training fatigue.** Mitigation: cheat sheet + recorded screencast for re-watching.

## Demo to Sana

90-min session:
1. Show the new KPI dashboard
2. Walk through a booking lifecycle from admin: confirmed → completed
3. Process a refund end-to-end (request → approve → Stripe API → receipt credit note → client email)
4. Edit a FAQ entry, add a new one, verify it appears on the public site
5. Edit availability rules, add a holiday exception
6. View activity log filtered to a client
7. Run a monthly CSV export
8. Invite a (test) assistant user, complete the password + 2FA setup as them, log back in as Sana

Sign-off requested on:
- Admin usability for Sana's day-to-day
- Permissions are right for the assistant
- KPI dashboard shows the metrics Sana cares about

## Files / artifacts produced

- Complete Filament admin panel
- KPI dashboard widgets
- Reports page
- Settings page
- User-invite flow
- Sana training cheat sheet
