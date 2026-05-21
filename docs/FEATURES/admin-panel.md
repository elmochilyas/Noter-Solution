# Feature: Admin Panel

## Scope

Filament-based admin panel for Sana (owner) and assistant. Mounted at `/admin`.

Responsibilities:
- Manage bookings (view, edit, status changes)
- Manage availability rules and exceptions
- Manage consultation plans (pricing, descriptions)
- Manage services / FAQ / legal pages content
- View payments and process refunds
- View documents and download
- Review chatbot conversations
- Manage clients (view, anonymize, deactivate)
- View notifications log
- View activity log
- Manage users (invite, disable)
- View KPI dashboard

## Design reference

Admin screens in `DESIGN/screens-index.md`: #18 (login), #19 (dashboard), #20 (calendar), #21 (availability), #22 (bookings list), #23 (booking detail), #24 (clients), #25 (content management), #26 (chatbot logs), #27 (settings). These drive the Filament theme overrides, sidebar grouping, and any custom Filament pages. Resources without Stitch outputs use Filament defaults with design tokens applied. Before writing views, read `DESIGN/README.md` and `DESIGN/design-system.md`.

## Stack

- Filament 3 (pinned to minor version).
- Tailwind base, customized via Filament theme override to match the brand (Brass + Ink palette).
- 2FA enforced (see `ARCHITECTURE/auth.md`).
- Custom dark-mode disabled — the practice has one canonical visual identity.

## Resources

### BookingResource

The most-used resource.

**List view:**
- Columns: Reference, Client name, Plan, Date/Time, Status badge, Total, Format icon
- Filters: status, format, plan, date range, has-documents
- Search: by reference, by client name, by client email, by phone
- Default sort: starts_at descending
- Quick actions per row: View, Cancel, Reschedule, Send reminder, Complete
- Bulk actions: Export selected to CSV, Mark as completed (with confirmation)

**Detail view:**
- Booking info: plan, date, format, status, total
- Client info: name, email, phone, link to ClientResource
- Service category, description (read-only — set at booking time)
- Documents: list with download buttons
- Payments: list with status, refund button per payment
- Receipt: download button (if generated)
- Internal notes (encrypted textarea, owner + assistant write)
- Timeline (activity log filtered to this booking)
- Actions:
  - Edit (limited fields — internal notes, status)
  - Cancel (with reason)
  - Reschedule
  - Mark completed
  - Mark no-show
  - Resend confirmation
  - Resend receipt
  - View as client (read-only, for support — audited)

**Create:** disabled (admin doesn't create bookings — they're client-initiated). If an admin needs to book on behalf, they do it via a separate "Manual booking" page.

### ClientResource

- List: name, email, phone, booking count, last booking date, status (active/anonymized)
- Filters: locale, has-active-booking, no-bookings-90d
- Search: name, email, phone
- Detail:
  - Profile fields
  - Booking history
  - Documents (across all bookings)
  - Notification log
  - Activity log (admin actions on this client)
- Actions:
  - Export data (Loi 09-08 "right to access")
  - Anonymize (deletion request)
  - Disable / re-enable

### ConsultationPlanResource

- List: name (FR + AR), price, duration, format, active toggle, recommended toggle
- Detail/edit form:
  - Slug (immutable after creation)
  - Name translations (FR + AR)
  - Description translations
  - Included features (repeater per locale)
  - Duration (select: 10/30/60/90 min)
  - Price (in MAD; converted to centimes on save)
  - Format (online/in_office/both)
  - Recommended toggle
  - Active toggle
  - Display order
- Permissions: only `owner` can edit prices; assistant read-only on this resource.

### AvailabilityResource

Two sub-resources:

**Rules** (weekly schedule):
- List: day of week, time range, format, active
- Edit: simple form per rule
- Default rules seeded; Sana can adjust

**Exceptions** (holidays, vacations, manual blocks):
- List: date range, reason, holiday flag
- Edit: dates, reason, is_holiday checkbox
- Bulk: "Add Moroccan public holidays for [year]" action

A calendar widget on the resource home view shows the current month overlaid with rules and exceptions.

### ServiceResource

CMS for the 4 service-detail pages.
- List: slug, title (FR/AR), active
- Detail/edit:
  - Slug
  - Icon (Lucide picker)
  - Title, intro, body (Markdown), transactions, required documents — all per locale
  - Active toggle
  - Display order
- Preview button → opens the public page in a new tab

### FaqResource

- List: question (current locale), category, published, view_count, embeddings status
- Filters: category, published
- Search: question and answer content
- Detail/edit:
  - Category
  - Question + answer in both locales
  - Published toggle
  - Display order
  - "Re-embed now" action (queued)
- Bulk: Publish, Unpublish, Re-embed selected

### PaymentResource

- List: gateway intent ID, booking reference, amount, status, paid_at, refund actions
- Filters: status, gateway, date range
- Detail:
  - Read-only payment info
  - Linked booking
  - Refund history
  - "Request refund" action — opens a form (amount, reason)
- Permissions:
  - `payments.view` (both)
  - `payments.refund.request` (assistant)
  - `payments.refund.approve` (owner only)

### RefundResource

- List: payment, amount, reason, requested_by, approved_by, status
- Filters: status (requested / approved / succeeded / failed)
- Detail:
  - View
  - Approve / reject (owner only)
- On approve: triggers actual Stripe refund (see `FEATURES/payment.md`)

### ReceiptResource

- List: number, booking reference, amount, issued_at, download
- Filters: year, month
- Detail:
  - Read-only
  - Download PDF
  - "Generate credit note" action (creates a new credit-note receipt referencing this one)
- Search: by number, by booking reference

### DocumentResource

- List: original filename, booking ref, client, mime, size, scan_status, purge_after
- Filters: scan_status, booking_id, expired
- Detail:
  - Preview / download
  - Delete (logged)
- Permissions: `documents.view.all`

### ChatbotConversationResource

- List: started_at, locale, intent_resolved, message count, client (or anonymous), reviewed
- Filters: intent_resolved (booked / escalated / abandoned / info_only), reviewed
- Detail:
  - Full transcript (all messages, retrieved FAQ IDs shown)
  - Token usage / cost
  - Linked booking (if any)
  - Actions:
    - "Promote question to FAQ" (pre-fills FaqResource create form)
    - "Mark as reviewed"
    - "Flag for review"

### NotificationLogResource

- List: created_at, channel, template_key, recipient, status, sent/delivered/failed timestamps
- Filters: channel, status, template_key, date range
- Search: by recipient
- Detail: full log entry, raw payload, provider message ID
- Actions: "Re-send" (re-queues the original send)

### ContactMessageResource

- List: name, email, subject, created_at, handled flag, snippet of message
- Filters: handled, subject
- Detail: full message, "Mark as handled" action with optional note

### ActivityLogResource

- Read-only.
- List: timestamp, actor, action, subject
- Filters: actor, action, subject type
- Detail: full event with before/after diff

### UserResource (owner only)

- List: name, email, role, last login, active
- Detail/edit:
  - Name, email, role
  - 2FA status (read-only)
  - Active toggle (can disable other users; cannot disable self if only owner)
  - "Reset 2FA" action (with audit log)
  - "Force logout" action
- Create: invite flow — email sent with signed link; new user sets password and 2FA
- Search: by name, email

## Pages (non-resource)

### Dashboard (home)

KPI widgets:
- Bookings this week / month
- Revenue this week / month
- Pending payments
- Active bookings (confirmed in next 7 days)
- Chatbot conversations this week + deflection rate
- Notification success rate (last 24h)
- Failed jobs (last 24h)

Charts:
- Bookings per day (last 30 days)
- Bookings by category (pie)
- Bookings by plan (pie)

Quick actions:
- "Today's bookings"
- "Pending refund approvals"
- "Unhandled contact messages"

### Pulse

Embedded Laravel Pulse dashboard at `/admin/pulse` (owner only). See `ARCHITECTURE/observability.md`.

### Reports

- Monthly receipts CSV export (per `COMPLIANCE/receipts-invoicing.md`)
- Annual summary
- VAT report (if applicable)
- Client list export (anonymized for marketing analytics — not for v1)
- Configurable date range

### Settings

A simple settings page covering:
- Practice info (name, address, phone, email, ICE, IF, RC, Patente)
- Bank info (if relevant for receipt footer)
- Default refund policy thresholds
- Quiet hours (for SMS / WhatsApp suppression)
- Default booking lead time (no-online-bookings-within-N-hours)
- VAT defaults
- Feature flags (chatbot enabled, online payment enabled, etc.)

Stored as a simple `settings` table (key-value). Cached. Owner only.

## Custom Filament theme

- Color tokens overridden to match brand (Brass `#B68A3E` for primary)
- Fonts: Inter for UI, Fraunces for headers in detail views (subtle, not heavy)
- Sidebar grouping:
  - **Quotidien**: Bookings, Calendar, Contact Messages
  - **Clients**: Clients, Documents
  - **Paiements**: Payments, Refunds, Receipts
  - **Contenu**: Services, FAQ, Consultation Plans, Availability
  - **Chatbot**: Conversations
  - **Système**: Notifications, Activity Log, Users, Settings, Pulse

## Permissions

Per `ARCHITECTURE/auth.md`:

| Resource | Owner | Assistant |
|---|---|---|
| Bookings | full | view, edit basic, cancel |
| Clients | full | view, edit basic |
| ConsultationPlans | full | view |
| Availability | full | full |
| Services / FAQ | full | full |
| Payments | full | view |
| Refunds | approve | request |
| Receipts | full | view |
| Documents | view, delete | view |
| Chatbot | view, review | view |
| Notifications log | view | view |
| Contact messages | full | full |
| Activity log | view | (hidden) |
| Users | full | (hidden) |
| Settings | full | view |
| Pulse | view | (hidden) |

## Search

Filament's global search (Cmd+K) searches across:
- Bookings (by reference, client name, email)
- Clients (by name, email, phone)
- FAQs (by question)
- Services (by title)

## Audit log integration

Every create/update/delete on Filament resources auto-logged via `spatie/laravel-activitylog` model observers. Sensitive read events (viewing a document, viewing internal notes) are also logged.

## Bilingual fields

Filament form fields for translatable content (titles, descriptions) render as tabs (Arabic / French) using a custom component.

## Live updates

For Sana's main dashboard, Livewire polling (every 30s) refreshes KPI widgets. Filament's default real-time mechanism via Echo is not used in v1 (no WebSocket server set up).

## Acceptance criteria

- [ ] All resources implemented per spec
- [ ] Permissions matrix enforced — assistant cannot access hidden resources or actions
- [ ] 2FA enforced for admin login
- [ ] Sensitive actions audit-logged
- [ ] Bilingual form fields work for all translatable models
- [ ] Cancel / reschedule actions from admin call the same domain services as portal
- [ ] Refund request → approve → process flow works end-to-end
- [ ] Receipt download from admin works (signed URL)
- [ ] FAQ re-embed action queues the job
- [ ] Reports export valid CSV
- [ ] KPI widgets show accurate data
- [ ] Performance: list pages load in <500ms (with pagination)
- [ ] No N+1 on listing pages (verified via Pulse)
- [ ] All actions tested with authorization (positive + negative)
- [ ] Settings changes audit-logged
- [ ] User invite flow works (email link, password + 2FA setup)
- [ ] Disabled user cannot log in
- [ ] Pulse accessible to owner only

## Out of scope (v1)

- In-app notifications (toast/bell) — emails suffice
- Custom report builder
- WhatsApp from admin (uses external app)
- Billing / accounting integrations
- Multi-language admin UI (Filament in French only is fine; admin team speaks French/Arabic)
