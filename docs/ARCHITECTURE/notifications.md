# Notifications Architecture

## Channels

| Channel | Provider | Use cases |
|---|---|---|
| Email | Resend | Confirmations, receipts, magic links, reminders, marketing-style (none yet) |
| SMS | Twilio | Confirmations, reminders, cancellations |
| WhatsApp | Twilio (WhatsApp Business API) | Confirmations, reminders, light Q&A handover |

## Why these providers

- **Resend** — modern API, good deliverability, simple webhooks, EU region available.
- **Twilio** — same vendor for SMS + WhatsApp, mature Moroccan SMS coverage, signature verification on webhooks.

## Orchestrator

`NotificationService` is the single entry point. Callers don't pick channels — they specify a template key and recipient.

```php
$notifications->send(
    templateKey: 'booking.confirmation',
    recipient: $booking->client,
    data: ['booking' => $booking],
    channels: [NotificationChannel::EMAIL, NotificationChannel::SMS, NotificationChannel::WHATSAPP],
);
```

The service:
1. Resolves the recipient to the channel-specific address (email / phone / WhatsApp number).
2. Resolves the template per channel + locale.
3. Dispatches one queued job per channel.
4. Logs each attempt in `notifications_log`.

## Template structure

Templates live in `resources/lang/{locale}/notifications.php`:

```php
return [
    'booking.confirmation' => [
        'email' => [
            'subject' => 'Votre rendez-vous est confirmé — :reference',
            'preview' => 'Confirmation de votre consultation du :date',
            'view' => 'emails.booking.confirmation',
        ],
        'sms' => 'Maître Bouhamidi — RDV confirmé le :date à :time. Réf :reference. Détails: :portal_url',
        'whatsapp' => '*Rendez-vous confirmé* 📅\n\nDate : :date\nHeure : :time\nRéférence : :reference\n\nDétails : :portal_url',
    ],
    'booking.reminder.24h' => [ ... ],
    'booking.reminder.1h' => [ ... ],
    'booking.cancelled' => [ ... ],
    'booking.rescheduled' => [ ... ],
    'payment.receipt' => [ ... ],
    'refund.issued' => [ ... ],
    'magic_link' => [ /* email only */ ],
    'admin.new_booking' => [ /* email only — to assistant */ ],
    'admin.contact_message' => [ /* email only */ ],
    'admin.dispute' => [ /* email only — high priority */ ],
];
```

## Template keys

| Key | Trigger | Channels | Recipient |
|---|---|---|---|
| `booking.confirmation` | BookingConfirmed | email + sms + whatsapp | client |
| `booking.reminder.24h` | Scheduled 24h before | email + sms + whatsapp | client |
| `booking.reminder.1h` | Scheduled 1h before | sms + whatsapp | client |
| `booking.cancelled` | BookingCancelled | email + sms | client |
| `booking.rescheduled` | BookingRescheduled | email + sms + whatsapp | client |
| `payment.receipt` | PaymentSucceeded | email | client |
| `payment.failed` | PaymentFailed | email | client |
| `refund.issued` | RefundIssued | email + sms | client |
| `magic_link` | MagicLinkRequested | email | client |
| `admin.new_booking` | BookingConfirmed | email | owner + assistant |
| `admin.contact_message` | ContactMessageReceived | email | owner + assistant |
| `admin.escalation` | ChatbotConversationEscalated | email | owner + assistant |
| `admin.dispute` | DisputeCreated | email (high priority) | owner |
| `admin.failed_jobs` | Daily summary | email | owner |
| `admin.new_admin_device` | New device login | email | the admin user |

## Per-channel formatting rules

### Email

- Templates rendered via Blade in `resources/views/emails/`.
- Use Laravel's `Mail` and `Mailable` classes.
- Plain-text alternative auto-generated.
- All emails include a footer with the practice address, phone, and an unsubscribe note (where applicable).
- From: `Maître Sana Bouhamidi <noreply@sana-bouhamidi.ma>`.
- Reply-To: `sana.bouhamidi@gmail.com` until a dedicated reply inbox is set up.

### SMS

- Maximum 160 characters (1 segment). Longer = split into multiple billed segments.
- Plain text only — no emoji (some Moroccan carriers strip them).
- Always include a short URL for follow-up (e.g. portal link via a URL shortener).
- Sender ID: `Bouhamidi` (alphanumeric — Twilio approval required, fall back to long code if not).

### WhatsApp

- Uses Twilio WhatsApp Business API.
- Templates approved in advance by Meta (mandatory).
- Plain text with light markdown: `*bold*`, line breaks.
- Emoji allowed and used sparingly.
- 24-hour customer-service window — outside it, must use a pre-approved template.
- Initial outbound messages must be from approved templates only.

## Locale resolution

Order:
1. `Client.preferred_locale` if set.
2. The locale of the originating booking / session.
3. Default to `ar`.

Each channel renders in the resolved locale.

## Recipient resolution

| Recipient input | Email source | SMS source | WhatsApp source |
|---|---|---|---|
| `Client` | `client.email` | `client.phone` (E.164) | `client.phone` (whatsapp: prefix) |
| `User` (admin) | `user.email` | n/a | n/a |
| `string` (raw email) | the string | n/a | n/a |
| `MoroccanPhoneNumber` | n/a | the number | the number with whatsapp: prefix |

If a recipient lacks the address for a requested channel, that channel is skipped (logged as `failed` with reason `no_address`) — the other channels still proceed.

## Sending pipeline

```
NotificationService::send
        │
        ▼
For each requested channel:
        │
        ▼
    Create notifications_log row (status=queued)
        │
        ▼
    Dispatch SendNotification job (queued)
        │
        ▼ (background)
    Job:
      - Resolve template
      - Render content
      - Call provider (Resend / Twilio)
      - Update log with provider_message_id, status=sent
      - On exception: status=failed, failure_reason, retry per policy
```

## Retry policy

- 3 attempts maximum per job.
- Backoff: 1 min, 5 min, 30 min.
- After final failure: status=failed in log, alert via Sentry.
- Permanent failures (invalid email, invalid phone) bypass retries.

## Provider webhooks

Providers report delivery status via webhooks.

### Resend webhook

`POST /webhooks/resend`

Events handled:
- `email.delivered` → update `notifications_log.delivered_at`
- `email.bounced` → mark `failed` with reason `bounced`
- `email.complained` → mark `failed` with reason `spam_complaint`, flag the client's email as undeliverable
- `email.opened` → not tracked (privacy)

Signature: Resend signs with `svix-id`, `svix-timestamp`, `svix-signature` headers. Verified before processing.

### Twilio webhook

`POST /webhooks/twilio`

Events:
- `delivered` / `read` → update log
- `failed` / `undelivered` → mark failed
- Inbound messages (replies to SMS / WhatsApp) → forward to contact inbox, notify admin

Signature: validated via `X-Twilio-Signature` header using Twilio's PHP SDK validator.

## Reminders — scheduled dispatch

When a booking is confirmed:

```php
SendNotification::dispatch(...)->delay($booking->starts_at->subDay());
SendNotification::dispatch(...)->delay($booking->starts_at->subHour());
```

If the booking is cancelled or rescheduled:
- Delayed jobs are cancelled (queue-level `Bus::findBatch` or job-key lookup).
- New reminders scheduled for the new time.

Implementation uses Laravel's job batching with a `key` per booking + per reminder type so cancellation is targeted.

## Opt-out and consent

- All transactional notifications (confirmations, reminders, receipts) are sent by default — operational necessity.
- Marketing messages: none in v1.
- A client can request "phone-only" or "email-only" via the portal:
  - `clients.notification_preferences` (jsonb): `{"sms": false, "whatsapp": true, "email": true}`
- Critical messages (receipts, refunds) always send via email regardless of preferences.
- Unsubscribe link in every email (except magic-link / receipt) updates preferences.

## Quiet hours

- No SMS / WhatsApp sent between 22:00 and 07:00 Morocco time.
- If a reminder falls in that window, it's deferred to 07:00 the next day (or skipped if the appointment is during that morning).
- Email is sent at any time (users open at their convenience).

## Anti-abuse

- Max 5 outbound SMS per phone per hour (prevents loops on bad triggers).
- Max 10 outbound emails per address per hour.
- Bounce / complaint flags suppress further sends to the same address until manually cleared.

## Cost monitoring

- Per-channel monthly cost tracked in the admin dashboard.
- Estimated v1 cost at 50 bookings/week:
  - Resend: ~$0 (5k emails/month free tier covers it)
  - SMS: ~$15/month (Twilio MA SMS pricing)
  - WhatsApp: ~$10/month (Twilio template message pricing)

## Failure communication

If notifications fail systemically (provider outage), Sana sees:
- A red banner in the admin dashboard.
- A daily summary email of failed jobs.
- Sentry alerts on > 5 failures in 15 minutes.

Even if all automated notifications fail, the practice can fall back to manual outreach using the admin Bookings list filtered by `confirmation_sent=false`.

## Audit trail

Every notification attempt persisted in `notifications_log` with:
- Template key, channel, recipient (pseudonymized in logs, FK in DB)
- Status transitions with timestamps
- Provider message ID for reconciliation
- Failure reason if applicable

Retention: 12 months.
