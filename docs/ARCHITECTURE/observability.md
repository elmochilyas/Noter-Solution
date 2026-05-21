# Observability Architecture

## Goals

We need to answer four operational questions at any time:

1. **Is the site working?** (uptime, error rate)
2. **Is it fast enough?** (latency, query time, queue depth)
3. **What did the user do?** (audit log, conversation log)
4. **What just broke and why?** (errors with full context)

## Components

| Component | Purpose | Tool |
|---|---|---|
| Error tracking | Capture exceptions with stack traces and breadcrumbs | Sentry |
| Performance monitoring | Per-request timing, slow queries, queue lag | Laravel Pulse |
| Logs | Structured event log for everything | Monolog (JSON) → file + Sentry breadcrumbs |
| Audit log | Who did what, when | spatie/laravel-activitylog |
| Uptime | External "is the site up" probe | UptimeRobot (free tier) |
| Status page | Public uptime page | Not in v1 |

## Sentry

### Setup

- DSN in `.env`: `SENTRY_LARAVEL_DSN`.
- Initialized via `sentry/sentry-laravel`.
- Environment tagged: `local`, `staging`, `production`.
- Release tagged from `APP_VERSION` env var, set by deploy script to the commit SHA.

### What we send

- All uncaught exceptions.
- Handled exceptions marked as "noteworthy" via `report($e)` calls.
- Performance traces (sampled — see below).
- Breadcrumbs: HTTP requests, DB queries, queue dispatches, mail sends, cache operations.

### What we don't send

- 4xx errors that are user errors (validation failures, 404, 403) — these are normal traffic.
- PII: scrubbed via Sentry's data scrubbing config + custom `before_send` callback.

### Scrubbing

```php
// config/sentry.php
'before_send' => function (Event $event): ?Event {
    $event->setRequest($event->getRequest()?->setData(
        array_map(
            fn ($value, $key) => in_array($key, ['password', 'password_confirmation', 'card_number', 'cvc', 'national_id', 'token'], true)
                ? '[REDACTED]'
                : $value,
            $event->getRequest()->getData() ?? [],
            array_keys($event->getRequest()->getData() ?? [])
        )
    ));
    return $event;
},
```

### Sampling

- Errors: 100% (always send).
- Performance traces: 10% of requests in production, 100% in staging.
- Adjust via `SENTRY_TRACES_SAMPLE_RATE`.

### Alerts

| Condition | Action |
|---|---|
| New issue (first-seen) | Email to admin |
| Issue regression (was resolved, came back) | Email to admin |
| Error spike (>10 events / 5 min) | Email + (future) Slack |
| Payment-tagged error | Email immediately |
| Webhook signature failure | Email immediately |

Issues tagged with `payment`, `webhook`, `chatbot`, `auth` for filtering.

## Laravel Pulse

### Setup

- `laravel/pulse` enabled.
- Dashboard at `/admin/pulse`, restricted by `users.view.pulse` permission (owner only).
- Recorders: requests, slow queries, slow jobs, queues, exceptions, user requests, servers.

### What we watch on the dashboard

- Slow requests (>500ms p95)
- Slow queries (>200ms)
- Queue lag per queue
- Failed jobs (last 24h)
- Servers (CPU, memory, disk)
- Top users by activity

### Retention

- Pulse data retained for 7 days. Anything older needs Sentry or DB analysis.

## Structured logging

### Format

JSON via Monolog's JSON formatter. Every log line includes:

```json
{
  "timestamp": "2026-04-15T10:23:45Z",
  "level": "info",
  "message": "Booking confirmed",
  "context": {
    "booking_id": 123,
    "booking_reference": "SBA-ABC123",
    "client_id_hash": "sha256:...",
    "request_id": "req_xxx",
    "user_id": null
  },
  "channel": "booking",
  "env": "production"
}
```

### Channels

Configured in `config/logging.php`:

| Channel | Purpose | Driver |
|---|---|---|
| `stack` (default) | Combined | combines `daily` + `sentry` |
| `daily` | File logs, rotated daily | single file |
| `sentry` | Send as breadcrumbs / events to Sentry | sentry |
| `booking` | Booking-specific events | daily + sentry |
| `payment` | Payment events | daily + sentry |
| `chatbot` | Chatbot conversations + LLM calls | daily |
| `security` | Auth events, denied actions | daily + sentry (level=warning+) |

### What gets logged

- All HTTP requests (auto, via middleware) with method, path, status, duration, request_id.
- Domain events at info level: booking created/confirmed/cancelled, payment succeeded/failed, etc.
- External API calls at info level: provider, endpoint, duration, status code, request_id.
- Security events at notice or warning level: login success/failure, denied policy check, rate limit hit.

### What never gets logged

See `STANDARDS/security.md` — passwords, tokens, card data, full national IDs, document contents.

## Correlation IDs

Every HTTP request gets an `X-Request-ID` header. If the client supplies one, we respect it; otherwise we generate `req_<random>`.

The ID is:
- Stamped on every log line for that request.
- Stamped on every queued job's payload (so background work can be traced back).
- Stamped on every outbound external API call.
- Returned in the response header (helps support).

Implemented via middleware that sets `Log::withContext(['request_id' => $id])`.

## Audit log

`spatie/laravel-activitylog`. Used for **state changes by humans**.

### What we audit

- Admin login / logout
- Permission / role changes
- Booking status changes
- Payment refunds
- Document deletions
- FAQ / service content edits
- Plan / availability changes
- Settings changes
- User invitations / disablements

### What we don't audit

- Automated background changes (e.g. a job auto-purging expired holds — that's in the regular logs).
- Read events (too noisy).

### Retention

- 24 months minimum.
- Cannot be deleted via the app. Sana can purge older entries via direct DB access if needed (rare).

### Example

```php
class BookingService
{
    public function cancel(Booking $booking, string $reason, User|Client $by): void
    {
        $booking->update(['status' => BookingStatus::CANCELLED, 'cancellation_reason' => $reason, 'cancelled_at' => now()]);

        activity('booking')
            ->causedBy($by)
            ->performedOn($booking)
            ->withProperties(['reason' => $reason])
            ->log('booking_cancelled');

        // ... events, notifications
    }
}
```

## Uptime monitoring

- UptimeRobot polls `https://sana-bouhamidi.ma/up` every 5 minutes.
- `/up` is a Laravel route returning 200 if DB and Redis are reachable.
- On downtime: email + SMS to Sana and lead developer.

## Health endpoint

`GET /up` returns:

```json
{
  "status": "ok",
  "checks": {
    "db": "ok",
    "redis": "ok",
    "storage": "ok"
  },
  "version": "v1.2.3",
  "uptime_seconds": 12345
}
```

Each check has a 500ms timeout. If any fails, returns 503.

## Metrics we track (manually for v1)

Pulse covers most. For things Pulse doesn't:

| Metric | How |
|---|---|
| Bookings per day | Filament KPI widget |
| Conversion rate (visitor → booking) | Manual via Plausible Analytics (privacy-respecting) |
| Chatbot deflection rate | Filament KPI widget computed from chatbot_conversations |
| Average response time per page | Pulse |
| Notification success rate per channel | Filament KPI widget computed from notifications_log |
| Payment success rate | Filament KPI widget computed from payments table |

## Analytics

**Plausible Analytics** (self-hosted or paid cloud, EU region). Cookie-free, GDPR-friendly, no consent banner required under that posture.

Goals tracked:
- Booking flow steps reached (`/consultation`, `/book/calendar`, `/book/payment`, `/book/success`)
- Contact form submission
- Chatbot opened
- Language switched
- Phone link clicked
- WhatsApp link clicked

No PII sent.

## Alerting matrix

| Severity | Channel | Examples |
|---|---|---|
| **Critical** (P0) | Email + SMS to Sana + lead dev | Site down, payment gateway broken, data loss suspected |
| **High** (P1) | Email | Webhook signature failures, dispute opened, error spike |
| **Medium** (P2) | Email (daily digest) | Failed jobs, slow queries, bounced emails |
| **Low** (P3) | Dashboard only | Individual handled exceptions, single 4xx |

## Operational runbooks

See `OPERATIONS/`:
- Deployment runbook
- Backup/restore drill
- Incident response process

## On-call

For v1, the only "on-call" person is the lead developer. Alerts route to one email + one phone number. Future: rotation via PagerDuty or similar.

## Privacy in observability

- Sentry data hosted in EU region.
- All scrubbing rules verified before launch.
- Audit log of *who accessed audit logs* (Sentry's own audit trail).
- Quarterly review of logs for inadvertent PII leakage.
