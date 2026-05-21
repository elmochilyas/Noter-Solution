# Database Schema

PostgreSQL 16 via Supabase. All tables use `bigserial` primary keys unless noted. Timestamps (`created_at`, `updated_at`) are `timestamptz`. UUIDs use `uuid` type. Money in centimes as `integer`.

## Tables

### `users` (admin users only)

| Column | Type | Notes |
|---|---|---|
| `id` | bigserial PK | |
| `name` | varchar(120) | |
| `email` | varchar(160) UNIQUE | |
| `email_verified_at` | timestamptz NULL | |
| `password` | varchar(255) | bcrypt cost ≥ 12 |
| `two_factor_secret` | text NULL | encrypted |
| `two_factor_recovery_codes` | text NULL | encrypted JSON array |
| `two_factor_confirmed_at` | timestamptz NULL | |
| `role` | varchar(32) | `owner` or `assistant` |
| `last_login_at` | timestamptz NULL | |
| `last_login_ip` | inet NULL | |
| `is_active` | boolean DEFAULT true | |
| `created_at`, `updated_at` | timestamptz | |

Indexes: `email` (unique), `role`.

### `clients`

End users who book consultations. Magic-link auth.

| Column | Type | Notes |
|---|---|---|
| `id` | bigserial PK | |
| `uuid` | uuid UNIQUE DEFAULT gen_random_uuid() | for public URLs |
| `email` | varchar(160) UNIQUE | |
| `phone` | varchar(20) | E.164 format |
| `full_name` | varchar(160) | |
| `preferred_locale` | varchar(5) DEFAULT 'ar' | |
| `national_id` | text NULL | encrypted |
| `national_id_last4` | varchar(8) NULL | for search |
| `last_login_at` | timestamptz NULL | |
| `created_at`, `updated_at` | timestamptz | |

Indexes: `email` (unique), `uuid` (unique), `phone`.

### `consultation_plans`

The 4 plans (free orientation, standard online, in-office, extended).

| Column | Type | Notes |
|---|---|---|
| `id` | bigserial PK | |
| `slug` | varchar(64) UNIQUE | `standard-online`, `in-office`, etc. |
| `name_translations` | jsonb | `{"fr": "...", "ar": "..."}` |
| `description_translations` | jsonb | |
| `included_features` | jsonb | list of features per locale |
| `duration_minutes` | smallint | 10, 30, 60, 90 |
| `price_centimes` | integer | 0 for free |
| `format` | varchar(16) | `online`, `in_office`, `both` |
| `is_recommended` | boolean DEFAULT false | |
| `is_active` | boolean DEFAULT true | |
| `display_order` | smallint | |
| `created_at`, `updated_at` | timestamptz | |

Indexes: `slug` (unique), `is_active`.

### `availability_rules`

Sana's weekly schedule.

| Column | Type | Notes |
|---|---|---|
| `id` | bigserial PK | |
| `day_of_week` | smallint | 1=Mon, 7=Sun |
| `starts_at` | time | local time, Africa/Casablanca |
| `ends_at` | time | |
| `format` | varchar(16) | `online`, `in_office`, `both` |
| `is_active` | boolean DEFAULT true | |
| `created_at`, `updated_at` | timestamptz | |

Indexes: `day_of_week`, `is_active`.

### `availability_exceptions`

Holidays, vacations, manual blocks.

| Column | Type | Notes |
|---|---|---|
| `id` | bigserial PK | |
| `starts_at` | timestamptz | inclusive |
| `ends_at` | timestamptz | exclusive |
| `reason` | varchar(255) NULL | |
| `is_holiday` | boolean DEFAULT false | |
| `created_at`, `updated_at` | timestamptz | |

Indexes: `starts_at`, `ends_at`, composite `(starts_at, ends_at)`.

### `bookings`

| Column | Type | Notes |
|---|---|---|
| `id` | bigserial PK | |
| `reference` | varchar(16) UNIQUE | `SBA-XXXXXX`, public |
| `client_id` | bigint FK → clients.id | |
| `consultation_plan_id` | bigint FK → consultation_plans.id | |
| `service_category` | varchar(32) | `family`, `real_estate`, `financial`, `contracts` |
| `description` | text | client's description of need |
| `format` | varchar(16) | `online` or `in_office` |
| `starts_at` | timestamptz | |
| `ends_at` | timestamptz | |
| `status` | varchar(20) DEFAULT 'pending_payment' | enum, see below |
| `meeting_url` | varchar(500) NULL | Jitsi room URL |
| `total_centimes` | integer | snapshot of price at booking time |
| `currency` | varchar(3) DEFAULT 'MAD' | |
| `cancellation_reason` | text NULL | |
| `cancelled_at` | timestamptz NULL | |
| `completed_at` | timestamptz NULL | |
| `internal_notes` | text NULL | encrypted, admin-only |
| `created_at`, `updated_at` | timestamptz | |
| `deleted_at` | timestamptz NULL | soft delete |

Status enum: `pending_payment`, `confirmed`, `completed`, `cancelled`, `no_show`.

Indexes:
- `reference` (unique)
- `client_id`
- `starts_at`
- `(status, starts_at)` composite
- `(consultation_plan_id, starts_at)` composite

### `booking_holds`

Temporary 10-minute holds while a client is on the payment step.

| Column | Type | Notes |
|---|---|---|
| `id` | bigserial PK | |
| `slot_starts_at` | timestamptz | |
| `slot_ends_at` | timestamptz | |
| `client_id` | bigint NULL FK → clients.id | nullable for anonymous early hold |
| `session_id` | varchar(128) | browser session for anonymous holds |
| `expires_at` | timestamptz | |
| `created_at` | timestamptz | |

Indexes: `(slot_starts_at, slot_ends_at)`, `expires_at`, `session_id`.

Cleanup: job deletes expired rows every 5 minutes.

### `payments`

| Column | Type | Notes |
|---|---|---|
| `id` | bigserial PK | |
| `uuid` | uuid UNIQUE | |
| `booking_id` | bigint FK → bookings.id | |
| `gateway` | varchar(16) | `stripe` or `cmi` |
| `gateway_intent_id` | varchar(255) | e.g. `pi_xxx` |
| `gateway_charge_id` | varchar(255) NULL | e.g. `ch_xxx` |
| `amount_centimes` | integer | |
| `currency` | varchar(3) | |
| `status` | varchar(20) | `pending`, `succeeded`, `failed`, `refunded`, `partially_refunded` |
| `paid_at` | timestamptz NULL | |
| `metadata` | jsonb | gateway-specific |
| `created_at`, `updated_at` | timestamptz | |

Indexes: `booking_id`, `gateway_intent_id` (unique within gateway), `status`.

### `refunds`

| Column | Type | Notes |
|---|---|---|
| `id` | bigserial PK | |
| `payment_id` | bigint FK → payments.id | |
| `amount_centimes` | integer | |
| `reason` | varchar(255) | |
| `gateway_refund_id` | varchar(255) | |
| `requested_by` | bigint FK → users.id | |
| `approved_by` | bigint NULL FK → users.id | NULL until Sana approves |
| `status` | varchar(20) | `requested`, `approved`, `succeeded`, `failed` |
| `processed_at` | timestamptz NULL | |
| `created_at`, `updated_at` | timestamptz | |

Indexes: `payment_id`, `status`.

### `documents`

Files uploaded by clients per booking.

| Column | Type | Notes |
|---|---|---|
| `id` | bigserial PK | |
| `uuid` | uuid UNIQUE | filename in storage |
| `booking_id` | bigint FK → bookings.id | |
| `client_id` | bigint FK → clients.id | denormalized for fast access checks |
| `original_filename` | varchar(255) | |
| `mime_type` | varchar(100) | |
| `size_bytes` | bigint | |
| `storage_path` | varchar(500) | |
| `scan_status` | varchar(16) DEFAULT 'pending' | `pending`, `clean`, `infected` |
| `scanned_at` | timestamptz NULL | |
| `purge_after` | timestamptz | for auto-deletion |
| `created_at`, `updated_at` | timestamptz | |

Indexes: `booking_id`, `client_id`, `purge_after`.

### `receipts`

Generated PDFs.

| Column | Type | Notes |
|---|---|---|
| `id` | bigserial PK | |
| `number` | varchar(32) UNIQUE | sequential, e.g. `SBA-2026-000123` |
| `booking_id` | bigint FK → bookings.id | |
| `payment_id` | bigint FK → payments.id | |
| `amount_centimes` | integer | |
| `vat_centimes` | integer DEFAULT 0 | |
| `storage_path` | varchar(500) | |
| `issued_at` | timestamptz | |
| `created_at`, `updated_at` | timestamptz | |

Indexes: `number` (unique), `booking_id`, `payment_id`.

### `services` (CMS — service-detail pages)

| Column | Type | Notes |
|---|---|---|
| `id` | bigserial PK | |
| `slug` | varchar(64) UNIQUE | `family`, `real_estate`, etc. |
| `title_translations` | jsonb | |
| `intro_translations` | jsonb | |
| `body_translations` | jsonb | markdown content |
| `transactions_translations` | jsonb | list per locale |
| `required_documents_translations` | jsonb | list per locale |
| `icon` | varchar(64) | Lucide icon name |
| `display_order` | smallint | |
| `is_active` | boolean DEFAULT true | |
| `created_at`, `updated_at` | timestamptz | |

Indexes: `slug` (unique), `is_active`.

### `faqs`

| Column | Type | Notes |
|---|---|---|
| `id` | bigserial PK | |
| `category` | varchar(32) | `family`, `real_estate`, `financial`, `contracts`, `practical` |
| `question_translations` | jsonb | |
| `answer_translations` | jsonb | |
| `embedding_fr` | vector(1024) NULL | Voyage embedding |
| `embedding_ar` | vector(1024) NULL | Voyage embedding |
| `is_published` | boolean DEFAULT true | |
| `display_order` | smallint | |
| `view_count` | integer DEFAULT 0 | |
| `created_at`, `updated_at` | timestamptz | |

Indexes:
- `(category, is_published)`
- `embedding_fr` (ivfflat or hnsw, vector_cosine_ops)
- `embedding_ar` (ivfflat or hnsw, vector_cosine_ops)

### `chatbot_conversations`

One row per conversation session.

| Column | Type | Notes |
|---|---|---|
| `id` | bigserial PK | |
| `uuid` | uuid UNIQUE | |
| `session_id` | varchar(128) | browser session |
| `client_id` | bigint NULL FK → clients.id | NULL if anonymous |
| `locale` | varchar(5) | |
| `intent_resolved` | varchar(64) NULL | e.g. `booked`, `escalated`, `info_only`, `abandoned` |
| `led_to_booking_id` | bigint NULL FK → bookings.id | |
| `started_at` | timestamptz | |
| `last_message_at` | timestamptz | |
| `ended_at` | timestamptz NULL | |
| `is_reviewed` | boolean DEFAULT false | |

Indexes: `uuid` (unique), `session_id`, `client_id`, `started_at`, `is_reviewed`.

### `chatbot_messages`

| Column | Type | Notes |
|---|---|---|
| `id` | bigserial PK | |
| `conversation_id` | bigint FK → chatbot_conversations.id | |
| `role` | varchar(10) | `user`, `assistant`, `system` |
| `content` | text | |
| `retrieved_faq_ids` | jsonb NULL | array of FAQ IDs surfaced |
| `tokens_in` | integer NULL | |
| `tokens_out` | integer NULL | |
| `latency_ms` | integer NULL | |
| `created_at` | timestamptz | |

Indexes: `conversation_id`, `created_at`.

### `notifications_log`

Outbound notification record.

| Column | Type | Notes |
|---|---|---|
| `id` | bigserial PK | |
| `recipient_type` | varchar(16) | `client`, `user`, `phone`, `email` |
| `recipient_id` | bigint NULL | |
| `channel` | varchar(16) | `email`, `sms`, `whatsapp` |
| `template_key` | varchar(64) | e.g. `booking.confirmation` |
| `status` | varchar(16) | `queued`, `sent`, `delivered`, `failed` |
| `provider_message_id` | varchar(255) NULL | |
| `metadata` | jsonb | |
| `sent_at` | timestamptz NULL | |
| `delivered_at` | timestamptz NULL | |
| `failed_at` | timestamptz NULL | |
| `failure_reason` | text NULL | |
| `created_at`, `updated_at` | timestamptz | |

Indexes: `(recipient_type, recipient_id)`, `(template_key, status)`, `created_at`.

### `contact_messages`

Contact-form submissions.

| Column | Type | Notes |
|---|---|---|
| `id` | bigserial PK | |
| `name` | varchar(160) | |
| `email` | varchar(160) | |
| `subject` | varchar(64) | enum: `family`, `real_estate`, etc. |
| `message` | text | |
| `ip` | inet | |
| `user_agent` | varchar(500) | |
| `is_handled` | boolean DEFAULT false | |
| `handled_by` | bigint NULL FK → users.id | |
| `handled_at` | timestamptz NULL | |
| `created_at` | timestamptz | |

Indexes: `is_handled`, `created_at`.

### `magic_links`

Used for client login.

| Column | Type | Notes |
|---|---|---|
| `id` | bigserial PK | |
| `client_id` | bigint FK → clients.id | |
| `token_hash` | varchar(64) UNIQUE | sha256 hex of the actual token |
| `expires_at` | timestamptz | |
| `consumed_at` | timestamptz NULL | |
| `ip` | inet NULL | |
| `user_agent` | varchar(500) NULL | |
| `created_at` | timestamptz | |

Indexes: `token_hash` (unique), `client_id`, `expires_at`.

### `permissions`, `roles`, `role_has_permissions`, `model_has_roles`, `model_has_permissions`

Provided by `spatie/laravel-permission`. Default schema; do not modify.

### `activity_log`

Provided by `spatie/laravel-activitylog`. Default schema.

### `jobs`, `failed_jobs`, `job_batches`

Provided by Laravel queues.

### `cache`, `cache_locks`, `sessions`

Provided by Laravel — backed by Redis in prod (not these tables), kept for local SQLite testing.

## Migrations order

Migrations live in `database/migrations/` with timestamp prefixes. Order:

1. `users`
2. `permissions` (spatie tables)
3. `clients`
4. `consultation_plans`
5. `availability_rules`
6. `availability_exceptions`
7. `bookings`
8. `booking_holds`
9. `payments`
10. `refunds`
11. `documents`
12. `receipts`
13. `services`
14. `faqs`
15. `chatbot_conversations`
16. `chatbot_messages`
17. `notifications_log`
18. `contact_messages`
19. `magic_links`
20. `activity_log` (spatie)
21. Vector index migration (after FAQ data exists)

## Vector index migration

Run after the `faqs` table is populated with embeddings:

```sql
CREATE INDEX faqs_embedding_fr_idx ON faqs
  USING hnsw (embedding_fr vector_cosine_ops)
  WITH (m = 16, ef_construction = 64);

CREATE INDEX faqs_embedding_ar_idx ON faqs
  USING hnsw (embedding_ar vector_cosine_ops)
  WITH (m = 16, ef_construction = 64);
```

## Foreign key conventions

- All FKs declared with `ON DELETE` clauses:
  - `RESTRICT` for legally retained records (bookings, payments, receipts)
  - `CASCADE` for child records that have no value without their parent (booking_holds, chatbot_messages)
  - `SET NULL` for soft references (notifications_log.recipient_id)
- All FKs indexed.

## Data retention

| Data | Retention |
|---|---|
| Bookings | Indefinite (legal record) |
| Payments / receipts | 10 years (Moroccan fiscal law) |
| Documents (client-uploaded) | 90 days post-appointment, then auto-purged unless flagged |
| Chatbot conversations | 18 months |
| Notifications log | 12 months |
| Activity log | 24 months |
| Magic links | 30 days after expiry |
| Booking holds | 1 day after expiry |
| Contact messages | 24 months |

Retention enforced by scheduled job `RunDataRetention` running nightly.

## Backups

- Supabase daily snapshots (Pro tier).
- 7-day point-in-time recovery (Pro tier).
- Weekly logical dump to a separate region via `pg_dump` (held 90 days).
- Quarterly restore drill (see `OPERATIONS/backup-recovery.md`).
