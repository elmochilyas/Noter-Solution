# Architecture Overview

## System context

```
                           ┌──────────────────────────┐
                           │   Visitors / Clients      │
                           │   (browser, mobile web)   │
                           └────────────┬─────────────┘
                                        │ HTTPS
                                        ▼
        ┌─────────────────────────────────────────────────────────────┐
        │                  Sana Bouhamidi Web App                      │
        │                  (Laravel 13 + Filament 3)                   │
        │                                                              │
        │   Public site ─ Chatbot ─ Booking ─ Portal ─ Admin           │
        │                                                              │
        │   Hosted on Hetzner CCX13 (Frankfurt) via Laravel Forge      │
        └──┬──────────┬──────────┬──────────┬──────────┬──────────────┘
           │          │          │          │          │
           ▼          ▼          ▼          ▼          ▼
        Supabase   Supabase    Stripe    Twilio     Cerebras
        Postgres   Storage     (cards)   (SMS+WA)    (LLM)
        + pgvector (docs)

           │          │
           ▼          ▼
       Resend      Jitsi Meet
       (email)     (video rooms)
```

## Application layers

```
┌─────────────────────────────────────────────────────────────────┐
│ HTTP Layer                                                       │
│   Controllers · Livewire components · Filament resources         │
│   FormRequests · Middleware · Webhook handlers                   │
├─────────────────────────────────────────────────────────────────┤
│ Application Layer                                                │
│   Services · Jobs · Listeners · Events · Notifications · Mail    │
│   Value objects · Form objects                                   │
├─────────────────────────────────────────────────────────────────┤
│ Domain Layer                                                     │
│   Eloquent models · Enums · Policies · Domain exceptions         │
│   Repository interfaces                                          │
├─────────────────────────────────────────────────────────────────┤
│ Infrastructure Layer                                             │
│   Stripe client · Cerebras client · Twilio client · S3 driver       │
│   Repository implementations · External adapters                 │
└─────────────────────────────────────────────────────────────────┘
```

Calls go down only. The domain never knows about Stripe, Cerebras, or HTTP.

## Request lifecycle (typical booking submission)

```
1. Browser POST /booking/submit
2. Routes → BookingController::store
3. StoreBookingRequest validates input
4. Controller calls BookingService::create(BookingData)
5. BookingService:
   - Calls AvailabilityService to verify slot
   - Uses DB transaction
   - Persists Booking model
   - Dispatches BookingCreated event
6. Event listeners (async):
   - SendBookingConfirmationEmail
   - SendBookingConfirmationSms
   - SendBookingConfirmationWhatsapp
   - GenerateReceiptPdf (if pre-paid)
7. Controller redirects to /booking/payment
```

## Major subsystems

| Subsystem | Doc | Key responsibility |
|---|---|---|
| Auth | `auth.md` | Client magic link, admin 2FA, roles |
| Booking | `domain-model.md`, `FEATURES/booking.md` | Slots, holds, lifecycle |
| Payment | `payments.md`, `FEATURES/payment.md` | Stripe today, CMI later, receipts |
| Chatbot | `chatbot.md`, `FEATURES/chatbot.md` | Triage, RAG, LLM, escalation |
| Notifications | `notifications.md`, `FEATURES/notifications.md` | Email, SMS, WhatsApp orchestration |
| Storage | `storage.md`, `FEATURES/document-management.md` | Documents, signed URLs, retention |
| Admin | `FEATURES/admin-panel.md` | Filament resources, content management |
| Observability | `observability.md` | Sentry, Pulse, audit log |

## Deployment topology

```
┌─────────────────────────── Hetzner CCX13 ───────────────────────────┐
│  Frankfurt, 4 vCPU / 16 GB RAM, NVMe                                 │
│                                                                      │
│  Nginx ──→ PHP-FPM ──→ Laravel 13                                    │
│                                                                      │
│  Redis (queue, cache, sessions)                                      │
│  Horizon supervisor (queue workers)                                  │
│  Meilisearch (FAQ search)                                            │
│  Scheduler (cron via Laravel)                                        │
│  Forge agent                                                         │
└──────────────────────────────────────────────────────────────────────┘

External managed services:
  - Supabase (Postgres + Storage)  — EU Central, Frankfurt
  - Stripe                         — Ireland data center
  - Twilio                         — Ireland data center
   - Cerebras API                  — US (only path with non-EU dependency)
  - Resend                         — US
```

## Data flow — typical session

### Client books a consultation

```
Browser ─ Livewire calendar component ─→ AvailabilityService
                                          │
                                          ▼
                                       Postgres (read availability + bookings)
                                          │
                                          ▼
Browser ─ form submit ─→ BookingController → BookingService
                                              │
                                              ▼ DB transaction
                                          Postgres (insert booking, hold, payment_intent)
                                              │
                                              ▼ dispatch event
                                          Redis queue
                                              │
                              ┌───────────────┼───────────────┐
                              ▼               ▼               ▼
                          Resend          Twilio         Twilio
                          (email)         (SMS)         (WhatsApp)
```

### Chatbot conversation

```
Browser ─→ /chatbot/message
              │
              ▼
        ChatbotController::message
              │
              ▼
        ChatbotService::respond
              │
       ┌──────┴──────┐
       ▼             ▼
    pgvector       Cerebras API
    (retrieve)     (generate)
       │             │
       └──────┬──────┘
              ▼
        Parse structured JSON response → render UI
              │
              ▼
       Async: persist ChatbotMessage
```

## Key architectural decisions

### Why Laravel + Filament + Livewire

- One language (PHP), one runtime, one mental model.
- Filament generates 70% of the admin panel from Eloquent models.
- Livewire keeps interactivity in Blade — no separate JS framework.
- Stack matches Ilyas's existing comfort and his teaching context.

### Why Supabase Postgres (not self-hosted)

- Managed Postgres with pgvector pre-available.
- Daily backups + 7-day PITR on Pro tier.
- S3-compatible storage in the same vendor for documents.
- Cost-effective ($25/mo) for the scale.

### Why no separate frontend SPA

- Marketing site, booking, portal, and admin are all server-rendered with Livewire islands for interactivity.
- SEO and performance favor SSR.
- No JSON API needed for v1 — every "API" call is internal Livewire or controller.

### Why Stripe before CMI

- CMI account setup takes 4–8 weeks via the client's bank.
- Stripe supports Moroccan cards via 3DS.
- Payment gateway is abstracted behind `PaymentGateway` interface so swap to CMI is a driver change.

### Why pgvector inside the same Postgres

- ~200 FAQ entries — a separate vector DB would be over-engineered.
- Same backup, same access control, same migrations.
- Switch to a dedicated vector DB only if the FAQ corpus grows past 10k entries (years away).

### Why no microservices

- One developer, one practice. Microservices solve org problems we don't have.
- A monolith with clear internal layering is easier to operate at this scale.

## Tradeoffs we accept

| Tradeoff | Why we accept it |
|---|---|
| Vendor lock to Supabase | Pure SQL + S3-compatible — exit cost is low |
| Vendor lock to Stripe | Abstracted gateway, swap planned (CMI) |
| US-based Cerebras API | Acceptable — chatbot is informational, no PII sent (only the message + retrieved FAQ excerpts) |
| Single region (Frankfurt) | Audience is Morocco; latency is fine; no multi-region need |
| No multi-tenant | One practice. Can be refactored later if needed (low likelihood) |

## Scalability ceiling

The current architecture handles, conservatively:

- **5k visitors/day** with no changes
- **100 bookings/day** with no changes
- **20 concurrent chatbot sessions** with no changes
- **500 GB of stored documents** within Supabase Pro tier

Above that, the scaling path is:

1. Bump Hetzner instance (CCX23: 8 vCPU / 32 GB — about $60/mo)
2. Move Meilisearch to a separate small instance
3. Move Redis to a managed service
4. Add Cloudflare for edge caching and DDoS protection
5. Consider Supabase Team tier for larger DB and storage

## Cross-cutting concerns

### Logging

Every request gets a correlation ID (`X-Request-ID`). Logs are structured JSON via the `monolog` JSON formatter, shipped to Sentry breadcrumbs and to Forge log files.

### Configuration

- App config in `config/*.php`.
- Environment-specific values via `.env`.
- Feature flags via `laravel-pennant` (planned for v1.1).
- No "config services" or external config sources in v1.

### Time

All times stored as UTC in DB. Display time zone is `Africa/Casablanca` (UTC+1, no DST).

### Currency

All amounts stored in centimes (smallest unit) as integers. `MoneyMad` value object handles arithmetic. Display formatted via `\NumberFormatter`.

### Identity

- Clients identified by email (no signup).
- Admin users identified by email + 2FA.
- Bookings have a public reference (`SBA-XXXXXX`) used in URLs and emails.
- Documents identified by UUID; filenames in storage are UUIDs.

### Failure mode

When in doubt, fail closed. Examples:
- Chatbot service down → show friendly fallback with WhatsApp / phone numbers.
- Payment gateway down → show "Service de paiement temporairement indisponible" + offer cash-at-office.
- Notification dispatch fails → retry up to 3 times, then surface in admin dashboard for manual handling.
