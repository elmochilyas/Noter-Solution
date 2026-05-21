# Environment Setup

## Prerequisites

- macOS, Linux, or WSL2 on Windows
- PHP 8.3+ (`brew install php@8.3` or [PHP.new](https://php.new))
- Composer 2.6+
- Node.js 22 LTS + npm
- Git
- A code editor with PHP support (VS Code with Intelephense, or PhpStorm)
- Docker Desktop (only if you want to run Postgres locally; SQLite is fine for most tests)

## Recommended dev stack

**Laravel Herd** (macOS / Windows) — easiest. Includes PHP, NGINX, MySQL/PostgreSQL, Redis, Node.

Alternatively: **Laravel Sail** (Docker Compose) if you prefer containerized.

## Repository setup

```bash
git clone git@github.com:elmochilyas/sana-bouhamidi.git
cd sana-bouhamidi

# PHP deps
composer install

# JS deps
npm install

# Environment file
cp .env.example .env
php artisan key:generate
```

## Required `.env` values for local dev

```dotenv
APP_NAME="Sana Bouhamidi"
APP_ENV=local
APP_KEY=                                   # auto-generated above
APP_DEBUG=true
APP_URL=http://sana.test
APP_LOCALE=ar
APP_FALLBACK_LOCALE=fr
APP_TIMEZONE=Africa/Casablanca

# Database — use Supabase dev project or local Postgres
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=sana_dev
DB_USERNAME=postgres
DB_PASSWORD=postgres

# Or use Supabase dev project (recommended for pgvector parity):
# DB_HOST=db.<ref>.supabase.co
# DB_USERNAME=postgres
# DB_PASSWORD=<from supabase dashboard>

# Redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Mail — use Mailpit or log driver in dev
MAIL_MAILER=log
# Or for Mailpit:
# MAIL_MAILER=smtp
# MAIL_HOST=127.0.0.1
# MAIL_PORT=1025

# Resend (use test API key in staging)
RESEND_KEY=re_test_xxx

# Stripe (test mode)
STRIPE_KEY=pk_test_xxx
STRIPE_SECRET=sk_test_xxx
STRIPE_WEBHOOK_SECRET=whsec_xxx

# Twilio (use test credentials)
TWILIO_ACCOUNT_SID=ACxxx
TWILIO_AUTH_TOKEN=xxx
TWILIO_FROM_SMS=Bouhamidi
TWILIO_FROM_WHATSAPP=whatsapp:+14155238886    # Twilio sandbox in dev

# Anthropic
ANTHROPIC_API_KEY=sk-ant-xxx
ANTHROPIC_MODEL=claude-sonnet-4-5

# Voyage AI (embeddings)
VOYAGE_API_KEY=pa-xxx
VOYAGE_MODEL=voyage-3-lite

# Supabase Storage (use a dev project bucket)
SUPABASE_STORAGE_ENDPOINT=https://<ref>.supabase.co/storage/v1/s3
SUPABASE_STORAGE_KEY=xxx
SUPABASE_STORAGE_SECRET=xxx

# Sentry
SENTRY_LARAVEL_DSN=                         # empty in local dev
SENTRY_TRACES_SAMPLE_RATE=0

# Queue
QUEUE_CONNECTION=redis
HORIZON_DASHBOARD_USER=ilyas@example.com    # protects /horizon
```

The `.env.example` in the repo has placeholder values — copy and fill yours.

## Database — two options

### Option A: Local Postgres (faster, but no pgvector / PostGIS parity)

```bash
# macOS with Homebrew
brew install postgresql@16
brew services start postgresql@16

# Create the dev database
createdb sana_dev
```

### Option B: Supabase dev project (recommended)

1. Create a Supabase organization (free tier OK for dev).
2. Create a project named `sana-dev` in EU-Central-1.
3. Enable `pgvector` extension from Dashboard → Database → Extensions.
4. Copy connection details into `.env`.
5. Create the storage buckets manually (see "Supabase buckets" below).

Pro: pgvector parity with prod, no local Postgres install.
Con: dev DB needs internet, slightly slower.

## Run migrations and seed

```bash
php artisan migrate --seed
```

Seeders create:
- An admin user `owner@local.test` with password `password` and dummy 2FA setup (TOTP secret printed to console).
- An assistant user `assistant@local.test` with password `password`.
- The 4 consultation plans.
- Default availability rules (Mon–Fri 9:00–13:00, 15:00–18:00).
- ~30 sample FAQ entries.
- ~5 sample bookings in various states.
- A handful of contact messages.

## Run the app

```bash
# Dev server
php artisan serve  # http://127.0.0.1:8000

# Or via Herd / Valet
# Visit http://sana.test

# Vite dev (for hot module reload on assets)
npm run dev

# Queue worker (so notifications and jobs actually run locally)
php artisan queue:work

# Or Horizon (preferred — matches prod)
php artisan horizon
```

In another terminal, start Stripe CLI for webhook forwarding:

```bash
stripe listen --forward-to http://sana.test/webhooks/stripe
# Copy the webhook signing secret it prints to STRIPE_WEBHOOK_SECRET
```

## Supabase production-project setup

When provisioning the production Supabase project:

1. Region: **EU-Central-1 (Frankfurt)**.
2. Tier: **Pro** (~$25/month).
3. Enable extensions:
   - `pgvector`
   - `pg_stat_statements` (for query observability)
   - `uuid-ossp` (or just use `gen_random_uuid()` from `pgcrypto`)
   - `postgis` (only if you later need geo — not in v1)
4. Configure point-in-time recovery (7 days included on Pro).
5. Set strong DB password (24+ chars, password manager).
6. Configure connection pooling: enable Transaction-mode pooler on port 6543 for the app.

### Supabase buckets

Create via Dashboard → Storage:

| Bucket | Public | File size limit | Allowed MIME |
|---|---|---|---|
| `documents` | No | 10 MB | `application/pdf, image/jpeg, image/png` |
| `receipts` | No | 5 MB | `application/pdf` |
| `internal` | No | 50 MB | (any) |
| `public` | Yes | 5 MB | `image/*` |

### Supabase RLS policy

Disable Postgres-level RLS on tables we manage from Laravel. Laravel policies are the source of truth — RLS on top would just be redundant and confusing.

Storage RLS: keep at defaults (deny all anonymous access on private buckets); signed URLs from the app bypass RLS for the duration of the signature.

### S3 credentials

Dashboard → Storage → S3 connection. Create a new key pair scoped only to the storage buckets above. Store in production `.env`:

```
SUPABASE_STORAGE_ENDPOINT=https://<ref>.supabase.co/storage/v1/s3
SUPABASE_STORAGE_KEY=...
SUPABASE_STORAGE_SECRET=...
```

## Filament admin access

Default admin route: `/admin` (configurable via `Filament::path()`).

After seeding, log in as `owner@local.test` / `password`. The seeder prints the 2FA secret to the console; add it to an authenticator app.

For local development without 2FA friction, you can set `FILAMENT_DISABLE_2FA=true` in `.env` — **never in staging or prod.**

## Testing

```bash
# Unit + feature tests
vendor/bin/pest

# With coverage
vendor/bin/pest --coverage

# A single test file
vendor/bin/pest tests/Feature/BookingTest.php

# Dusk (requires Chrome)
php artisan dusk
```

### Test database

- SQLite in-memory by default (fast).
- Set `DB_CONNECTION=pgsql_test` in `phpunit.xml` env block if a specific test needs Postgres parity (e.g. pgvector).

## Mail in development

Use **Mailpit** (recommended) — runs locally, captures all mail, web UI at `localhost:8025`.

```bash
brew install mailpit
mailpit  # starts SMTP on 1025 and UI on 8025
```

Set `.env`:

```
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
```

## Useful Tinker snippets

```bash
php artisan tinker
```

```php
// Generate a fresh magic link to test login flow
$client = \App\Models\Client::factory()->create(['email' => 'me@example.com']);
app(\App\Services\Auth\MagicLinkService::class)->send($client);

// Re-embed all FAQs
\App\Models\Faq::chunk(50, fn ($faqs) => $faqs->each(fn ($f) => \App\Jobs\ReembedFaq::dispatchSync($f)));

// Simulate a successful Stripe payment for a booking
$booking = \App\Models\Booking::factory()->pending()->create();
app(\App\Services\Payment\PaymentService::class)->markSucceeded($booking->payment, 'pi_test_xxx');
```

## Common dev issues

| Symptom | Likely cause |
|---|---|
| `SQLSTATE[42P01]: relation does not exist` | Migrations not run. `php artisan migrate`. |
| `Class 'pgsql' not found` | PHP missing pgsql extension. `brew install php@8.3 && pecl install pgsql` or check `php -m`. |
| `RuntimeException: Pulse table does not exist` | `php artisan pulse:install && php artisan migrate`. |
| Stripe webhooks don't fire | Make sure `stripe listen` is running, secret matches `.env`. |
| `Could not find driver` for s3 | `composer require league/flysystem-aws-s3-v3`. |
| Livewire uploads 419 | CSRF token expired — `php artisan key:generate` then restart. |
| Filament 2FA setup fails | Time skew. Ensure system clock is synced. |

## Editor configuration

`.editorconfig` enforces 4-space indent for PHP, 2 for JS/JSON/YAML.

VS Code extensions to install:
- Intelephense
- Laravel Blade Snippets
- Laravel Pint
- Larastan
- Tailwind CSS IntelliSense
- ESLint
- DotENV

`.vscode/settings.json` checked in with shared formatter settings.

## Pre-commit hooks

Install once:

```bash
npx husky init
npm install --save-dev lint-staged
```

`.husky/pre-commit` runs Pint, ESLint, Gitleaks on staged files. See `STANDARDS/git-workflow.md`.

## Staging environment

Mirror of production with:
- Separate Supabase project (`sana-staging`)
- Stripe test mode
- Twilio sub-account or sandbox
- Sentry environment tag `staging`
- Same Hetzner-class host (smaller, e.g. CPX21)
- `.env.staging` with `APP_ENV=staging`, `APP_DEBUG=false`

Staging auto-deploys from `main` on every merge.

## Production access

- Production env vars live in Forge's secret manager.
- Database connection requires VPN or Supabase Dashboard direct connection.
- SSH access via Forge for emergencies only — most operations through the app or Forge UI.
- The team lead is the only one with production write access in v1.

## Cleaning up

```bash
# Reset DB completely
php artisan migrate:fresh --seed

# Clear all caches
php artisan optimize:clear

# Clear logs
> storage/logs/laravel.log
```
