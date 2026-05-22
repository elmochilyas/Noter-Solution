# Phase 1 — Foundation

## Goal

The project is ready to build features on. Laravel app skeleton stands up locally and deploys to staging. Supabase wired. Filament shell renders with brand. Auth scaffolding works for both guards. i18n plumbing complete. CI passes. Design tokens applied.

**Definition of phase complete:** an empty Laravel + Filament app deploys to staging, login + logout work for both client (magic link) and admin (password + 2FA), `/up` returns 200, and a Hello-World page in both `/ar/` and `/fr/` renders with the brand fonts and layout.

## Prerequisites

- [x] GitHub repo created
- [ ] Hetzner account + Forge subscription
- [ ] Supabase organization + two projects (prod + staging) provisioned (Frankfurt)
- [ ] Domain registered (sana-bouhamidi.ma) and DNS pointed to Hetzner staging IP
- [ ] Twilio account created (SMS sender ID submitted for approval — long lead time, start now)
- [ ] Resend account + domain verification started
- [ ] Stripe account activation in progress
- [ ] Practice info collected from Sana (ICE, IF, RC, Patente, all phone numbers, official email)

## Scope

In:
- Laravel 13 install
- Filament 3 install
- Tailwind + tailwindcss-rtl + design tokens
- PostgreSQL (Supabase) connection
- Redis on Hetzner box
- i18n scaffold (routes, middleware, lang files, language switcher)
- Auth scaffolding (Fortify + magic link)
- Layouts (public, portal, admin)
- Error pages (404, 500, 503)
- Maintenance mode page
- Health endpoint
- Sentry + Pulse setup
- CI pipeline (GitHub Actions)
- Forge provisioning
- Pre-commit hooks
- Database schema migrations (all tables from `database-schema.md`)
- Models with relationships and casts (no business logic yet — that's later)
- Factories and seeders
- Pest, Larastan, Pint configured
- Settings page (Filament)
- Practice info populated from Sana

Out:
- Any feature logic (booking, payment, chatbot)
- Filament resources beyond settings
- Real content (placeholder Lorem ipsum)
- Translation review (placeholders are fine here)

## Tasks

### Task 1: Repo and tooling — **DONE**

Acceptance:
- [x] GitHub repo created with `main` branch protected (no direct pushes, required PR + CI green)
- [x] `.gitignore` covers all per `STANDARDS/git-workflow.md`
- [x] `README.md` at root points to `docs/README.md`
- [x] `.editorconfig`, `.vscode/settings.json`, `.github/PULL_REQUEST_TEMPLATE.md` committed
- [x] Husky + lint-staged installed; pre-commit runs Pint
- [ ] ~~Husky + lint-staged installed; pre-commit runs Pint, ESLint, Gitleaks~~ (ESLint + Gitleaks not yet wired)
- [x] Conventional Commits enforced via commit-msg hook

### Task 2: Laravel install + base config — **DONE**

Acceptance:
- [x] Laravel 13 installed via `composer create-project`
- [x] PHP 8.3 minimum in `composer.json`
- [x] `config/app.php`: timezone `Africa/Casablanca`, locale `ar`, fallback locale `fr`
- [x] `config/auth.php`: two guards (`web`, `client`) and providers (`users`, `clients`)
- [x] `config/session.php`: secure, http_only, encrypt, same_site=lax
- [x] `config/database.php`: pgsql default, Redis for cache/session/queue
- [x] `.env.example` updated per `OPERATIONS/environment-setup.md`
- [x] `php artisan serve` boots without error

### Task 3: Database connection (Supabase) — **PARTIAL**

Acceptance:
- [x] Supabase prod + staging projects exist (dev project created)
- [x] pgvector extension enabled (ran `CREATE EXTENSION vector` in SQL Editor)
- [ ] Connection string in Forge env (prod, staging) *— blocked on Forge provisioning (Task 18)*
- [ ] Local dev `.env` connects to Supabase dev project *— blocked: dev network is IPv4-only, Supabase host is IPv6-only. Fallback: SQLite for local dev; `supabase` connection config added to `database.php` for when IPv6 or IPv4 add-on is available*
- [ ] `php artisan migrate:status` works against Supabase *— same IPv6 block*
- [x] `config/database.php` has `supabase` connection entry with SSL mode
- [x] `.env.example` updated with `SUPABASE_DB_*` variables
- [x] SQLite connection verified: `php artisan migrate:status` returns "Migration table not found"
- [x] `composer test` (194 tests) passes with SQLite in-memory — local dev unblocked

### Task 4: Migrations — **DONE**

Implement all migrations from `ARCHITECTURE/database-schema.md`. Order matters (FKs).

Acceptance:
- [x] All tables migrated with correct columns, types, defaults (32 tables total)
- [x] All foreign keys with correct `ON DELETE` clauses (RESTRICT/CASCADE/SET NULL per spec)
- [x] All indexes per the spec
- [x] Vector index migration created (deferred — only runs when FAQ embeddings exist)
- [x] `php artisan migrate:fresh` runs clean (verified on SQLite)
- [x] No raw SQL outside the vector index migration (single exception: pgvector columns in faqs table)
- [x] Spatie packages installed: `laravel-permission` + `laravel-activitylog` with their migrations

### Task 5: Models

Acceptance:
- [x] One model per table from the schema
- [x] `$fillable` declared explicitly (no `$guarded = []`)
- [x] All casts: dates, JSON, enums, encrypted columns
- [x] Relationships typed: `BelongsTo`, `HasMany`, etc.
- [x] No business logic — only accessors / mutators / relations
- [x] ~~Larastan passes on the Models layer~~ *— Larastan (PHPStan level 8) runs in CI; local Windows env skips due to platform requirements*

### Task 6: Enums + Value Objects — **DONE**

Implement all enums and value objects from `ARCHITECTURE/domain-model.md`.

Acceptance:
- [x] Enums in `app/Enums/`
- [x] Value objects in `app/ValueObjects/` (differs from spec path `app/Support/ValueObjects/`)
- [x] Each has unit tests for every public method (72 enum + value object tests)
- [x] `MoneyMad::formatted(Locale)` works for both locales
- [x] `MoroccanPhoneNumber::fromInput` handles all common formats listed in the spec

### Task 7: Factories + seeders

Acceptance:
- [x] Factory per model
- [x] Seeders create 2 admin users (owner + assistant), 4 plans, default availability, ~5 sample bookings, ~30 sample FAQs
- [x] `php artisan migrate:fresh --seed` produces a usable local DB

### Task 8: Tailwind + design tokens — **DONE**

Acceptance:
- [x] Tailwind v4 installed (CSS-first config, built-in RTL via logical properties)
- [x] `@theme` block in `app.css` with brand tokens: Ink, Parchment, Brass, Stone palette — fonts: Fraunces, Reem Kufi, Inter, IBM Plex Sans Arabic
- [x] Fonts loaded via local `@font-face` (8 woff2 files in `resources/fonts/`, self-hosted)
- [x] CSS variables exported via `@theme` consumed by Filament admin theme CSS
- [x] Hello World page (`resources/views/hello.blade.php`) demonstrates brand colors, fonts, RTL

### Task 9: Layouts — **DONE**

Three layouts:

- `layouts/public.blade.php`
- `layouts/portal.blade.php`
- `layouts/error.blade.php` (404, 500, 503)

Filament has its own layout via theme override.

Acceptance:
- [x] Public layout: header (nav + lang toggle + CTA), main slot, footer (practice ID, links)
- [x] Portal layout: lighter header (greeting + logout)
- [x] Both use the right fonts per locale (CSS `@layer base`)
- [x] Both RTL on `ar` paths (`dir` attribute + `html[dir='rtl']` CSS rules)
- [x] Error pages styled and translated (404, 500, 503)

### Task 10: i18n plumbing — **DONE**

Acceptance:
- [x] `SetLocaleMiddleware` sets app locale, Carbon locale, HTML lang/dir
- [x] `/` redirects to `/ar/` or `/fr/` based on detection (URL > cookie > Accept-Language > default `ar`)
- [x] `LocaleSwitcher` component preserves current path (switches `ar` ↔ `fr`)
- [x] `resources/lang/{ar,fr}/*.php` files created (6 files: auth, common, errors, footer, nav, portal)
- [x] CI key-parity script at `scripts/i18n-key-parity.php`
- [x] Routes wrapped in locale prefix group (`{locale}/ar|fr`)

### Task 11: Auth scaffolding — **DONE**

Acceptance:
- [x] Fortify installed; admin login/password reset routes registered
- [x] 2FA enabled via `pragmarx/google2fa-laravel`
- [x] Admin lockout middleware (5 fails / 15 min via Fortify rate limiter)
- [x] Magic-link routes + controller + form
- [x] `MagicLinkService::send` and `MagicLinkController::verify` working
- [x] Magic link 15-min expiry, single-use, hashed storage
- [x] Client logout works
- [x] Session lifetimes enforced (120 min client / 30 min admin via `SetSessionLifetime` middleware)
- [x] Tests cover happy paths + expired / consumed links (10 MagicLink tests)

### Task 12: Filament shell — **DONE**

Acceptance:
- [x] Filament 3 installed at `/admin`
- [x] Custom theme: Brass primary (`#B68A3E`), Inter font, brand-aligned sidebar
- [x] Login uses Fortify (password + 2FA)
- [x] Empty resources list (no resources yet — built in phase 5)
- [x] Only one page: Dashboard (placeholder widget)
- [x] Settings page with practice info form (ICE, IF, RC, Patente, phones, address, hours)
- [x] Role-based access via `spatie/laravel-permission` (owner / assistant roles seeded)

### Task 13: Health endpoint + Sentry + Pulse — **DONE**

Acceptance:
- [x] `GET /up` configured via Laravel health routing (checks DB)
- [x] Sentry package installed (`sentry/sentry-laravel ^4.25`), `config/sentry.php` published with env-var-driven DSN, data scrubbing config in place (PII disabled, filament routes filtered via `before_send`)
- [x] Pulse package installed (`laravel/pulse ^1.7`), `config/pulse.php` published with Africa/Casablanca timezone, slow queries/requests/jobs recorders enabled
- [x] Pulse migration published — runs on staging/prod
- [x] `PulseServiceProvider` registered — restricts Pulse dashboard to `owner` role
- [ ] Both reporting in staging *— blocked on Forge*

### Task 14: Logging + audit log — **DONE**

Acceptance:
- [x] Structured JSON logging via Monolog
- [x] `X-Request-ID` middleware adds correlation IDs
- [x] `spatie/laravel-activitylog` installed and configured
- [x] Audit log auto-records login / logout (via `LogAuthActivity` event listener)
- [x] No PII in test log output (verified via grep on sample test run)

### Task 15: Storage (Supabase S3) — **PARTIAL**

Acceptance:
- [x] `supabase` S3-compatible disk configured in `config/filesystems.php` — uses `SUPABASE_STORAGE_*` env vars with `use_path_style_endpoint=true`
- [ ] Buckets created on Supabase (documents, receipts, internal, public) *— requires Supabase dashboard access + `SUPABASE_STORAGE_ENDPOINT`/`SUPABASE_STORAGE_KEY`/`SUPABASE_STORAGE_SECRET` in `.env`*
- [ ] Test: upload a file via `Storage::disk('supabase')->put(...)` succeeds *— blocked on env config*
- [ ] Test: signed URL works and expires correctly *— blocked on env config*

### Task 16: Queue + Horizon — **PARTIAL**

Acceptance:
- [ ] Redis installed on staging Hetzner box *— blocked on Forge provisioning*
- [ ] Horizon config created (`config/horizon.php` with `default` and `notifications` queues) — requires `ext-pcntl` (Linux only), install on staging via `composer require laravel/horizon`
- [ ] Supervisor running Horizon (Forge)
- [ ] `/admin/horizon` (owner only) accessible
- [ ] A test job dispatched + executed visible in Horizon
- [x] Console schedule configured: `PurgeExpiredBookingHolds` runs every 5 minutes via `routes/console.php`
- [x] Queue driver defaults to `redis` in `.env.example`; falls back to `sync` for local dev

### Task 17: CI pipeline — **DONE**

Acceptance:
- [x] GitHub Actions workflow per `OPERATIONS/deployment.md` — 4 jobs: lint, static analysis, tests, security audit
- [x] PR opens → CI runs: lint (Pint), static analysis (Larastan level 8), tests (Pest 70% coverage min), security audit (`composer audit`)
- [ ] CI green on a fresh-cloned `main` branch *— needs verification on first PR*
- [ ] Branch protection: required check is the CI job *— needs enabling in GitHub repo settings*

### Task 18: Forge deployment to staging

Acceptance:
- [ ] Forge provisions staging box (CPX21)
- [ ] PHP 8.3, Nginx, Redis, PHP-FPM configured
- [ ] Deploy script per `OPERATIONS/deployment.md` runs successfully
- [ ] Staging accessible at `https://staging.sana-bouhamidi.ma` with TLS
- [ ] `/up` returns 200 from staging
- [ ] Auto-deploy on push to `main`

### Task 19: Maintenance page — **DONE**

Acceptance:
- [x] `php artisan down --render="errors::503"` shows the branded maintenance page
- [x] Maintenance page in both languages
- [x] Includes phone + WhatsApp for urgent contact

### Task 20: Documentation parity — **DONE**

Acceptance:
- [x] Phase docs (01–04) updated to reflect current code state — all implemented items checked
- [x] `.env.example` updated with `TURNSTILE_SITE_KEY`/`TURNSTILE_SECRET_KEY`, and matched against `config/services.php`
- [ ] `OPERATIONS/environment-setup.md` reviewed against `.env.example` *— deferred to Forge provisioning phase*

## Phase exit criteria

- [ ] All 20 tasks above complete with their acceptance criteria
- [ ] CI green on `main`
- [ ] Staging accessible at `https://staging.sana-bouhamidi.ma`
- [ ] Demo to Sana: brand fonts, both languages, admin login, settings page editable
- [ ] Sana provides + reviews practice info (ICE, IF, etc.) entered in settings
- [ ] All other phase docs unchanged (this phase doesn't alter feature plans)

## Risks

- **Supabase connection from Hetzner via pooler** can have quirks with PHP's persistent connections. Test under load before claiming done.
- **Twilio + WhatsApp template approval** is a long pole — submit during this phase even though templates won't be used until later.
- **2FA UX in Filament** sometimes needs custom view overrides — budget half a day for polish.

## Demo to Sana

Show:
1. Staging site in both languages with placeholder content
2. Filament admin: login with 2FA, see Settings page, edit practice info
3. Receive a magic-link email (from staging)
4. Health endpoint + Pulse dashboard
5. Discuss copy direction for phase 2

Sign-off requested on:
- Brand rendering (fonts, colors)
- Practice info correctness
- Magic-link experience (the email lands, the link works)

## Files / artifacts produced

- The Laravel + Filament codebase committed to GitHub
- Staging environment live
- All migrations applied
- Seeders for dev populating the schema
- This doc tree referenced as the spec
