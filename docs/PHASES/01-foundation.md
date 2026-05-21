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

### Task 4: Migrations

Implement all migrations from `ARCHITECTURE/database-schema.md`. Order matters (FKs).

Acceptance:
- [ ] All tables migrated with correct columns, types, defaults
- [ ] All foreign keys with correct `ON DELETE` clauses
- [ ] All indexes per the spec
- [ ] HNSW indexes on FAQ embeddings (run after data exists — separate migration)
- [ ] `php artisan migrate:fresh` runs clean on a blank Supabase project
- [ ] No raw SQL outside the appropriate vector index migration

### Task 5: Models

Acceptance:
- [ ] One model per table from the schema
- [ ] `$fillable` declared explicitly (no `$guarded = []`)
- [ ] All casts: dates, JSON, enums, encrypted columns
- [ ] Relationships typed: `BelongsTo`, `HasMany`, etc.
- [ ] No business logic — only accessors / mutators / relations
- [ ] Larastan passes on the Models layer

### Task 6: Enums + Value Objects

Implement all enums and value objects from `ARCHITECTURE/domain-model.md`.

Acceptance:
- [ ] Enums in `app/Enums/`
- [ ] Value objects in `app/Support/ValueObjects/`
- [ ] Each has unit tests for every public method
- [ ] `MoneyMad::formatted(Locale)` works for both locales
- [ ] `MoroccanPhoneNumber::fromInput` handles all common formats listed in the spec

### Task 7: Factories + seeders

Acceptance:
- [ ] Factory per model
- [ ] Seeders create 2 admin users (owner + assistant), 4 plans, default availability, ~5 sample bookings, ~30 sample FAQs
- [ ] `php artisan migrate:fresh --seed` produces a usable local DB

### Task 8: Tailwind + design tokens

Acceptance:
- [ ] Tailwind installed with `tailwindcss-rtl` plugin
- [ ] `tailwind.config.js` extends with brand tokens: colors (Ink, Parchment, Brass, Stone palette), fonts (Fraunces, Reem Kufi, Inter, IBM Plex Sans Arabic), spacing scale
- [ ] Fonts loaded via local `@font-face` (subsets per language, variable axis)
- [ ] CSS variables exported for use by Filament theme override
- [ ] A "Hello World" Blade page demonstrates: brand colors, both fonts (FR + AR), RTL flip

### Task 9: Layouts

Three layouts:

- `layouts/public.blade.php`
- `layouts/portal.blade.php`
- `layouts/error.blade.php` (404, 500, 503)

Filament has its own layout via theme override.

Acceptance:
- [ ] Public layout: header (nav + lang toggle + CTA), main slot, footer (practice ID, links)
- [ ] Portal layout: lighter header (greeting + logout)
- [ ] Both use the right fonts per locale
- [ ] Both RTL on `ar` paths
- [ ] Error pages styled and translated

### Task 10: i18n plumbing

Acceptance:
- [ ] `locale` middleware sets app locale, Carbon locale, HTML lang/dir
- [ ] `/` redirects to `/ar/` or `/fr/` based on detection (URL > cookie > Accept-Language > default `ar`)
- [ ] `LocaleSwitcher` component preserves current path
- [ ] `resources/lang/{ar,fr}/*.php` files created (minimum: common, nav, validation)
- [ ] CI script enforces key parity
- [ ] Routes wrapped in locale prefix group

### Task 11: Auth scaffolding

Acceptance:
- [ ] Fortify installed; admin login/password reset routes registered
- [ ] 2FA enabled via `pragmarx/google2fa-laravel`
- [ ] Admin lockout middleware (5 fails / 15 min)
- [ ] Magic-link routes + controller + form
- [ ] `MagicLinkService::send` and `MagicLinkController::verify` working
- [ ] Magic link 15-min expiry, single-use, hashed storage
- [ ] Client logout + admin logout work
- [ ] Session lifetimes (2h client / 30 min admin) enforced
- [ ] Tests cover happy paths + expired / consumed links

### Task 12: Filament shell

Acceptance:
- [ ] Filament 3 installed at `/admin`
- [ ] Custom theme: Brass primary, Inter font, brand-aligned sidebar
- [ ] Login uses Fortify (password + 2FA)
- [ ] Empty resources list (no resources yet — built in phase 5)
- [ ] Only one page: Dashboard (placeholder widget)
- [ ] Settings page with practice info form (ICE, IF, RC, Patente, phones, address, hours)
- [ ] Role-based access via `spatie/laravel-permission` (owner / assistant defined; assistant denied on hidden resources/pages)

### Task 13: Health endpoint + Sentry + Pulse

Acceptance:
- [ ] `GET /up` checks DB + Redis + storage, returns 200 or 503
- [ ] Sentry installed and reporting test exceptions (env-tagged)
- [ ] Sentry data scrubbing config in place
- [ ] Pulse installed and visible at `/admin/pulse` (owner only)
- [ ] Both work in staging

### Task 14: Logging + audit log

Acceptance:
- [ ] Structured JSON logging via Monolog
- [ ] `X-Request-ID` middleware adds correlation IDs
- [ ] `spatie/laravel-activitylog` installed and configured
- [ ] Audit log auto-records login / logout
- [ ] No PII in test log output (verified via grep on a sample run)

### Task 15: Storage (Supabase S3)

Acceptance:
- [ ] `supabase` disk configured in `config/filesystems.php`
- [ ] Buckets created on Supabase (documents, receipts, internal, public)
- [ ] Test: upload a file via `Storage::disk('supabase')->put(...)` succeeds
- [ ] Test: signed URL works and expires correctly

### Task 16: Queue + Horizon

Acceptance:
- [ ] Redis installed on staging Hetzner box
- [ ] Horizon installed, configured with `default` and `notifications` queues
- [ ] Supervisor running Horizon (Forge)
- [ ] `/admin/horizon` (owner only) accessible
- [ ] A test job dispatched + executed visible in Horizon

### Task 17: CI pipeline

Acceptance:
- [ ] GitHub Actions workflow per `OPERATIONS/deployment.md`
- [ ] PR opens → CI runs: lint (Pint), static analysis (Larastan level 8), tests (Pest 70% coverage min), security audit
- [ ] CI green on a fresh-cloned `main` branch
- [ ] Branch protection: required check is the CI job

### Task 18: Forge deployment to staging

Acceptance:
- [ ] Forge provisions staging box (CPX21)
- [ ] PHP 8.3, Nginx, Redis, PHP-FPM configured
- [ ] Deploy script per `OPERATIONS/deployment.md` runs successfully
- [ ] Staging accessible at `https://staging.sana-bouhamidi.ma` with TLS
- [ ] `/up` returns 200 from staging
- [ ] Auto-deploy on push to `main`

### Task 19: Maintenance page

Acceptance:
- [ ] `php artisan down --render="errors::503"` shows the branded maintenance page
- [ ] Maintenance page in both languages
- [ ] Includes phone + WhatsApp for urgent contact

### Task 20: Documentation parity

Acceptance:
- [ ] `OPERATIONS/environment-setup.md` matches what's actually in `.env.example`
- [ ] Any drift between docs and reality fixed in this phase

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
