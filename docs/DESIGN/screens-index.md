# Screens Index

The lookup table the AI agent uses to find the design for any UI task. For each screen:

- **#** — Stitch prompt number in `stitch-prompts.md` (anchors like `#1-home`)
- **Screen** — short name
- **Route** — primary route in the app
- **Stitch HTML** — file in `stitch-output/` (or `—` if no Stitch output expected)
- **Blade view path** — where the implementation lives in code
- **Feature doc** — the FEATURES doc covering this screen's behavior
- **Notes** — anything the agent needs to know up front

## How to use this index

When you have a UI task:

1. Find the row matching the route or screen you're building.
2. Open the linked Stitch HTML (if any).
3. Open the linked feature doc for behavior, validation, states.
4. Read `design-system.md` for tokens and component conventions.
5. Read `STANDARDS/accessibility-i18n.md` for RTL + a11y obligations.
6. Build the Blade view at the listed path.

If a screen isn't in this table, no Stitch output was produced for it. Design it from scratch using `design-system.md` and the relevant FEATURES doc.

## Public pages

| # | Screen | Route | Stitch HTML | Blade view path | Feature doc | Notes |
|---|---|---|---|---|---|---|
| 1 | Home | `/{locale}/` | `stitch-output/01-home.html` | `resources/views/public/home.blade.php` | `FEATURES/public-site.md` | Hero CTA chains to chatbot widget; chatbot lives in layout, not on this page |
| 2 | About | `/{locale}/maitre-bouhamidi` | `stitch-output/02-about.html` | `resources/views/public/about.blade.php` | `FEATURES/public-site.md` | Bio + credentials; copy editable via Filament `AboutResource` (settings-style) |
| 3 | Services overview | `/{locale}/services` | `stitch-output/03-services-overview.html` | `resources/views/public/services/index.blade.php` | `FEATURES/public-site.md` | Pulls active services from `services` table |
| 4 | Service detail | `/{locale}/services/{slug}` | `stitch-output/04-service-detail.html` | `resources/views/public/services/show.blade.php` | `FEATURES/public-site.md` | One template, four content variants (slugs: famille, immobilier, financier, contrats). Stitch generated `famille` only — agent clones for the others. |
| 5 | Consultation plans | `/{locale}/consultation` | `stitch-output/05-consultation-plans.html` | `resources/views/public/consultation.blade.php` | `FEATURES/public-site.md`, `FEATURES/booking.md` | Each plan card links to `/{locale}/book?plan=<slug>` |
| 6 | Booking — step 1 (slot) | `/{locale}/book` (step 1) | `stitch-output/06-booking-slot.html` | `resources/views/livewire/booking/slot-picker.blade.php` | `FEATURES/booking.md` | Livewire component; holds enforced server-side, see `ARCHITECTURE/domain-model.md` for `AvailabilityService` |
| 7 | Booking — step 2 (info) | `/{locale}/book` (step 2) | `stitch-output/07-booking-info.html` | `resources/views/livewire/booking/identity.blade.php` | `FEATURES/booking.md`, `FEATURES/document-management.md` | Includes optional doc upload (step 5 in feature spec collapses into this if user has docs ready) |
| 8 | Booking — step 3 (payment) | `/{locale}/book` (step 3) | `stitch-output/08-booking-payment.html` | `resources/views/livewire/booking/payment.blade.php` | `FEATURES/payment.md` | Stripe Elements iframe — see `ARCHITECTURE/payments.md` for the integration |
| 9 | Booking confirmation | `/{locale}/book/success` | `stitch-output/09-booking-success.html` | `resources/views/public/booking/success.blade.php` | `FEATURES/booking.md` | Receives `?reference=SBA-XXXXXX` |
| 10 | Payment failed | `/{locale}/book/failed` | `stitch-output/10-payment-failed.html` | `resources/views/public/booking/failed.blade.php` | `FEATURES/payment.md` | Retry path back to `/book/payment/<intent>` |
| 11 | FAQ | `/{locale}/faq` | `stitch-output/11-faq.html` | `resources/views/public/faq.blade.php` | `FEATURES/public-site.md` | Meilisearch-backed live search; Livewire-debounced |
| 12 | Contact | `/{locale}/cabinet` | `stitch-output/12-contact.html` | `resources/views/public/contact.blade.php` | `FEATURES/public-site.md` | Stitch calls this `/contact`; we use `/cabinet` for SEO and brand reasons. Path differs, content unchanged. |
| 13 | Legal page template | `/{locale}/mentions-legales`, `/{locale}/politique-confidentialite`, `/{locale}/conditions-utilisation` | `stitch-output/13-legal-template.html` | `resources/views/public/legal/show.blade.php` | `FEATURES/public-site.md`, `COMPLIANCE/loi-09-08.md` | One template, three legal pages backed by CMS rows |
| 14 | 404 | (any unmatched route) | `stitch-output/14-404.html` | `resources/views/errors/404.blade.php` | `FEATURES/public-site.md` | Also adapt for 500 / 503 with same composition |

## Chatbot widget

| # | Screen | Route | Stitch HTML | Blade view path | Feature doc | Notes |
|---|---|---|---|---|---|---|
| 15 | Chatbot — closed + open | (widget on all public + portal pages) | `stitch-output/15-chatbot-widget.html` | `resources/views/livewire/chatbot/widget.blade.php` | `FEATURES/chatbot.md` | Stitch shows static states; agent implements: disclaimer modal, streaming SSE messages, suggestion chips, triage state machine, escalation panel |

## Client portal

| # | Screen | Route | Stitch HTML | Blade view path | Feature doc | Notes |
|---|---|---|---|---|---|---|
| 16 | Portal home | `/{locale}/portal` | `stitch-output/16-portal-home.html` | `resources/views/portal/home.blade.php` | `FEATURES/client-portal.md` | Stitch uses `/me` — our route is `/portal` |
| 17 | Booking detail (client) | `/{locale}/portal/bookings/{reference}` | `stitch-output/17-portal-booking-detail.html` | `resources/views/portal/bookings/show.blade.php` | `FEATURES/client-portal.md` | Stitch uses `/me/bookings/:id` — we use the booking `reference` not the id in the URL |

Routes without Stitch outputs in this section (agent designs from `design-system.md`):
- `/{locale}/portal/login` — magic-link request form
- `/{locale}/portal/login/verify` — server-side handler, no UI beyond a redirect (errors render in `portal/login.blade.php`)
- `/{locale}/portal/bookings` — list of bookings (adapt portal home's booking cards)
- `/{locale}/portal/bookings/{ref}/reschedule` — reuses screen 6's slot picker
- `/{locale}/portal/receipts` — simple list view
- `/{locale}/portal/preferences` — form
- Account deletion modal — see `FEATURES/client-portal.md`

## Admin panel

The admin panel is generated by Filament. Stitch outputs document the **intended visual style** of the Filament-rendered pages — used to drive the Filament theme overrides, sidebar grouping, dashboard composition, and any custom pages.

| # | Screen | Filament page | Stitch HTML | Filament source path | Feature doc | Notes |
|---|---|---|---|---|---|---|
| 18 | Admin login | `/admin/login` | `stitch-output/18-admin-login.html` | Customize Fortify login view at `resources/views/vendor/filament-panels/pages/auth/login.blade.php` | `ARCHITECTURE/auth.md` | Full-screen split layout, 2FA challenge appears after first step |
| 19 | Admin dashboard | `/admin` | `stitch-output/19-admin-dashboard.html` | `app/Filament/Pages/Dashboard.php` + widgets in `app/Filament/Widgets/` | `FEATURES/admin-panel.md` | KPI strip, table, recent chatbot conversations, activity chart |
| 20 | Admin calendar | `/admin/calendar` | `stitch-output/20-admin-calendar.html` | `app/Filament/Pages/CalendarPage.php` (custom page with embedded FullCalendar.js) | `FEATURES/admin-panel.md` | Filament has no native calendar — embed FullCalendar.js. Right-side drawer for the selected booking |
| 21 | Admin availability | `/admin/availability` | `stitch-output/21-admin-availability.html` | `app/Filament/Resources/AvailabilityResource/` | `FEATURES/admin-panel.md` | Two tabs: weekly rules and exceptions |
| 22 | Admin bookings list | `/admin/bookings` | `stitch-output/22-admin-bookings-list.html` | `app/Filament/Resources/BookingResource.php` | `FEATURES/admin-panel.md` | Standard Filament resource table — restyle via theme override, not custom templates |
| 23 | Admin booking detail | `/admin/bookings/{id}` | `stitch-output/23-admin-booking-detail.html` | `app/Filament/Resources/BookingResource/Pages/ViewBooking.php` | `FEATURES/admin-panel.md` | Custom infolist composition; sticky action panel via custom Blade component |
| 24 | Admin clients | `/admin/clients` | `stitch-output/24-admin-clients.html` | `app/Filament/Resources/ClientResource.php` | `FEATURES/admin-panel.md` | Right-side drawer on row click (Filament's slide-over) |
| 25 | Admin content management | `/admin/content/*` | `stitch-output/25-admin-content.html` | Three Filament resources: `ServiceResource`, `FaqResource`, `ConsultationPlanResource` | `FEATURES/admin-panel.md` | Bilingual edit forms with tab switcher per language |
| 26 | Admin chatbot logs | `/admin/chatbot-conversations` | `stitch-output/26-admin-chatbot.html` | `app/Filament/Resources/ChatbotConversationResource.php` | `FEATURES/admin-panel.md`, `FEATURES/chatbot.md` | Conversation review with "Promote to FAQ" action |
| 27 | Admin settings | `/admin/settings` | `stitch-output/27-admin-settings.html` | `app/Filament/Pages/SettingsPage.php` (custom page with sub-nav) | `FEATURES/admin-panel.md` | Sub-nav inside the page (Cabinet / Facturation / etc.) — different sections, single URL |

## Missing screens (no Stitch output expected)

These screens have no Stitch output and the agent should design from `design-system.md`:

- Empty / loading / error states across all surfaces
- All email templates (use the design system for HTML emails)
- Receipt PDF layout (see `COMPLIANCE/receipts-invoicing.md` for the spec — Sana's accountant signs off)
- Maintenance page (503)
- 500 page
- Cookie banner
- Modal dialogs (cancel-confirmation, document-delete-confirmation, etc.)
- Slide-over drawers in Filament beyond the calendar booking detail
- All Filament resource forms not specifically listed (e.g. `PaymentResource`, `RefundResource`, `DocumentResource`, `NotificationLogResource`, `ContactMessageResource`, `ActivityLogResource`, `UserResource`)

For Filament resources without a Stitch output, the agent uses Filament's default form / table layouts with theme tokens applied. Custom layouts only when the default is inadequate.

## Notes on path differences between Stitch and the codebase

Stitch was prompted with shorter, English-style paths (e.g. `/contact`, `/me`). The codebase uses the locale-prefixed and brand-aligned paths (`/{locale}/cabinet`, `/{locale}/portal`). The agent translates these on implementation — the layout intent transfers as-is.

## Updating this index

When a new screen is added, designed, or renamed:

1. Update the relevant row (or add one).
2. If a new Stitch output is generated, drop the HTML into `stitch-output/` with the same filename convention `NN-screen-slug.html`.
3. Mention in PR description that the index changed.

When a Stitch output is regenerated:

1. Replace the file in `stitch-output/`.
2. Bump no row unless filename changed.
3. Mention in PR description.
