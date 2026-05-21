# Feature: Notifications

Architecture in `ARCHITECTURE/notifications.md`. This doc is the catalog of every notification we send: when, to whom, on which channel, with what content.

## Convention

Each notification has:
- A unique **template key** (`booking.confirmation`, `payment.receipt`, etc.)
- A **trigger** (the event that fires it)
- A **recipient resolution rule**
- **Channels** it goes out on
- **Content per channel** (email subject/body, SMS body, WhatsApp template name)

## Client-facing notifications

### `booking.confirmation`

**Trigger:** `BookingConfirmed` event.
**Recipient:** the client.
**Channels:** email + sms + whatsapp.
**Suppressible:** no (operational necessity).

**Email subject:** `Votre rendez-vous est confirmé — :reference` / `موعدك مؤكد — :reference`

**Email body** (Blade view `emails.booking.confirmation`):
- Greeting with first name
- Booking summary: plan, date (long format with weekday), time, format
- For online: "Le lien de votre consultation vidéo vous sera envoyé 1 heure avant"
- For in-office: office address + map link + "près du Tribunal de Première Instance"
- Booking reference prominent: `SBA-XXXXXX`
- "Vos prochaines étapes" section
- Portal link: "Suivre votre dossier"
- Cancellation policy summary
- Contact info footer

**SMS body** (160 chars target):
```
M. Bouhamidi: RDV confirmé le 14/03 à 10:30. Réf SBA-ABC123. Détails: https://sb.ma/p/X
```

**WhatsApp template:**
```
*Rendez-vous confirmé* ✓

📅 :date_long
⏰ :time
📍 :location_short
🔖 Réf : :reference

Détails : :portal_url
```

### `booking.reminder.24h`

**Trigger:** scheduled job, 24h before `booking.starts_at`.
**Channels:** email + sms + whatsapp.
**Skipped if:** booking cancelled, completed, or rescheduled in the meantime.

**Email subject:** `Rappel : votre rendez-vous demain à :time`

**Email body:** brief reminder, same info as confirmation, with "Préparez vos documents" tip.

**SMS:** `Rappel: RDV demain 14/03 à 10:30 avec M. Bouhamidi. Réf SBA-ABC123.`

**WhatsApp:** similar to confirmation but with a "Rappel" header.

### `booking.reminder.1h`

**Trigger:** scheduled, 1h before.
**Channels:** sms + whatsapp (no email — they won't check it in time).

**SMS:** `Votre RDV avec M. Bouhamidi est dans 1h. Lien: <jitsi-url-short> (online) OR adresse: <office>.`

**WhatsApp:** Same with WhatsApp formatting.

### `booking.cancelled`

**Trigger:** `BookingCancelled` event.
**Channels:** email + sms.

**Email:** confirmation of cancellation, refund amount + ETA if applicable, contact info.

**SMS:** `Votre RDV SBA-ABC123 est annulé. :refund_note`

`:refund_note` = "Remboursement de 250 MAD en cours (5 jours ouvrables)." or "Aucun remboursement dû." depending on policy.

### `booking.rescheduled`

**Trigger:** `BookingRescheduled` event.
**Channels:** email + sms + whatsapp.

Content notes: shows OLD slot crossed out + NEW slot prominent. New reference shown.

### `payment.receipt`

**Trigger:** receipt PDF generated (post-`PaymentSucceeded`).
**Channels:** email (only).

**Email subject:** `Reçu n° SBA-2026-000123`
**Body:** brief acknowledgment + PDF attached + portal link.

### `payment.failed`

**Trigger:** `PaymentFailed` (3+ failed attempts within an Intent).
**Channels:** email (only — to avoid spamming SMS for retry-able errors).

Content: friendly explanation, retry link, alternative methods (cash-at-office if eligible, contact us).

### `refund.issued`

**Trigger:** `RefundIssued` event.
**Channels:** email + sms.

**Email:** confirmation of refund, amount, expected arrival date on bank statement (typically 5-10 days), receipt PDF for the refund (credit note).

### `magic_link`

**Trigger:** `MagicLinkRequested` event.
**Channels:** email (only).
**Suppressible:** no.

**Email subject:** `Votre lien de connexion à l'espace client`
**Body:** big button "Me connecter", expiry note (15 min), security note.

### `account.deletion.confirmation`

**Trigger:** client requests deletion via portal.
**Channels:** email.

Final magic-link to confirm permanent action.

## Admin-facing notifications

### `admin.new_booking`

**Trigger:** `BookingConfirmed`.
**Recipient:** owner + assistant.
**Channels:** email.

Quick summary of the new booking with links to Filament booking detail.

### `admin.contact_message`

**Trigger:** `ContactMessageReceived`.
**Channels:** email.

Full message content + link to Filament admin.

### `admin.dispute`

**Trigger:** `DisputeCreated` (Stripe webhook).
**Channels:** email (high priority).

Marked `Importance: High` in headers + bold subject prefix `[ACTION REQUISE]`.

### `admin.refund_request`

**Trigger:** an assistant requests a refund.
**Recipient:** owner.
**Channels:** email.

Pending approval, link to approve in Filament.

### `admin.failed_jobs`

**Trigger:** scheduled daily 09:00 if any job failed in last 24h.
**Recipient:** owner + lead dev.
**Channels:** email.

Digest of failures with job names + counts.

### `admin.new_device`

**Trigger:** admin login from a new device.
**Recipient:** the admin user themselves.
**Channels:** email.

"Was this you?" with IP, UA, time. Link to reset password if not.

### `admin.escalation`

**Trigger:** `ChatbotConversationEscalated`.
**Channels:** email.

Short summary + transcript link.

### `admin.capacity_alert`

**Trigger:** Sana's calendar approaching saturation (e.g. <10% slots remain in next 7 days).
**Channels:** email.

So Sana can open up more slots or block off time.

### `admin.weekly_digest`

**Trigger:** scheduled Monday 08:00.
**Channels:** email.

KPIs summary: bookings, revenue, top FAQ queries, deflection rate, etc.

## Translation keys

All template content in `resources/lang/{locale}/notifications.php`:

```php
return [
    'booking' => [
        'confirmation' => [
            'email' => [
                'subject' => 'Votre rendez-vous est confirmé — :reference',
                'greeting' => 'Bonjour :name,',
                'intro' => 'Votre rendez-vous avec Maître Sana Bouhamidi est confirmé. Voici les détails :',
                'next_steps_title' => 'Vos prochaines étapes',
                'cta' => 'Voir mon rendez-vous',
                'sig' => 'Cordialement,\nCabinet de Maître Sana Bouhamidi',
            ],
            'sms' => 'M. Bouhamidi: RDV confirmé le :date à :time. Réf :reference. Détails: :url',
            'whatsapp' => '*Rendez-vous confirmé* ✓\n\n📅 :date_long\n⏰ :time\n📍 :location_short\n🔖 Réf : :reference\n\nDétails : :url',
        ],
        // ...
    ],
];
```

Both `ar` and `fr` files maintained.

## Variables available in templates

| Variable | Source |
|---|---|
| `:name`, `:first_name` | recipient.full_name |
| `:reference` | booking.reference |
| `:date` | booking.starts_at formatted short |
| `:date_long` | booking.starts_at formatted long (weekday + date + time) |
| `:time` | booking.starts_at formatted time-only |
| `:plan_name` | booking.consultationPlan.name (locale-aware) |
| `:plan_price` | booking.total formatted |
| `:format` | 'En ligne' or 'En personne' |
| `:location_short` | "En ligne" or "Cabinet, Hay Bensergao" |
| `:location_full` | full address or jitsi URL |
| `:portal_url`, `:url` | client portal booking detail URL |
| `:refund_amount` | refund.amount formatted |
| `:refund_note` | computed refund note |
| `:office_phone` | from settings |
| `:office_whatsapp` | from settings |

## Per-channel rendering rules

### Email

- HTML version (Blade) + auto-generated plain text alternative
- Sender: `Maître Sana Bouhamidi <noreply@sana-bouhamidi.ma>`
- Reply-To: `sana.bouhamidi@gmail.com`
- Headers: `X-Mailer: SanaBouhamidi/1.0`, `List-Unsubscribe: <...>` where applicable (not for transactional)
- Inline CSS via Maizzle or Tailwind compiled for email

### SMS

- Max 160 chars for one segment
- No emoji
- Use URL shortener (we maintain one: `https://sb.ma/p/<hash>` → portal URL)
- Sender ID: "Bouhamidi" (Twilio approval required)

### WhatsApp

- Templates approved with Meta in advance
- Markdown-light: `*bold*`, line breaks, emoji
- Initial outbound messages must use approved templates (we don't initiate freeform)
- Within 24-hour customer-service window, freeform allowed (e.g. replies from clients)

## Reminder scheduling and cancellation

When a booking is confirmed:

```php
SendBookingNotification::dispatch($booking, 'booking.reminder.24h')
    ->delay($booking->starts_at->subDay());

SendBookingNotification::dispatch($booking, 'booking.reminder.1h')
    ->delay($booking->starts_at->subHour());
```

Each job checks at execution time whether the booking is still in `confirmed` status. If not (cancelled, rescheduled, completed), the job exits without sending.

## Notification preferences

Stored on `clients.notification_preferences` (jsonb):

```json
{
  "email": true,
  "sms": true,
  "whatsapp": true
}
```

`NotificationService::send` filters channels by preferences, except for critical channels (always-on):

- `booking.confirmation` → email always
- `booking.cancelled` → email always
- `payment.receipt` → email always
- `magic_link` → email always
- `refund.issued` → email always

## Quiet hours

- 22:00–07:00 Africa/Casablanca: no SMS / WhatsApp dispatch.
- Reminders falling in this window deferred to 07:00 (or skipped if the appointment is during that morning).
- Email sent at any time.

## Re-send

For each user-facing notification, admin has a "Re-send" action in Filament. This re-queues the original send. Used for support cases ("I didn't get my confirmation").

## Failure handling

See `ARCHITECTURE/notifications.md`:
- 3 attempts with exponential backoff
- Status tracked in `notifications_log`
- Permanent failures (bad address) bypass retries
- Daily digest of failures to admin

## Anti-abuse

- Max 5 SMS per phone per hour
- Max 10 emails per address per hour
- Bounce / complaint flag suppresses further sends to that address until cleared

## Webhook integration

- Resend webhook updates `notifications_log` with delivery / bounce status
- Twilio webhook updates SMS / WhatsApp status

## Acceptance criteria

- [ ] All 12+ notification templates implemented and tested
- [ ] Both locales rendered correctly for every template
- [ ] Multi-channel dispatch works (email + SMS + WhatsApp arrive)
- [ ] Reminder jobs scheduled with correct delays
- [ ] Reminders skipped if booking no longer confirmed
- [ ] Preferences respected (suppressible channels suppressed)
- [ ] Critical notifications bypass preferences
- [ ] Quiet hours enforced for SMS/WhatsApp
- [ ] Webhooks update delivery status
- [ ] Bounce flag prevents further sends
- [ ] Re-send action works from Filament
- [ ] All sends logged with correlation
- [ ] Failure retry policy works
- [ ] Daily failure digest delivered if failures exist
- [ ] No PII in logs

## Out of scope (v1)

- Marketing newsletters
- Push notifications (no PWA in v1)
- In-app notifications (admin uses email)
- Reminder day-of-week customization per client
- A/B testing of subject lines
