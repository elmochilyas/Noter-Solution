# Deployment

## Hosting

- **Server**: Hetzner Cloud CCX13 in Frankfurt (FSN1) — 4 vCPU, 16 GB RAM, 80 GB NVMe.
- **Provisioned via**: Laravel Forge.
- **OS**: Ubuntu 24.04 LTS.
- **Web server**: Nginx + PHP-FPM.
- **PHP**: 8.3 (matching prod requirements).
- **Node**: 22 LTS (for the build step).
- **Redis**: managed on the same server (queue + cache + session).
- **No DB on the box** — all data in Supabase.

## Environments

| Env | URL | Branch | DB | Stripe |
|---|---|---|---|---|
| local | http://sana.test | (working tree) | local pgsql or Supabase dev | test mode |
| staging | https://staging.sana-bouhamidi.ma | `main` (auto) | `sana-staging` Supabase project | test mode |
| production | https://sana-bouhamidi.ma | `main` (manual approve) | `sana-prod` Supabase project | live mode |

## CI pipeline (GitHub Actions)

`.github/workflows/ci.yml`:

```yaml
on:
  pull_request:
  push:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest
    services:
      postgres:
        image: pgvector/pgvector:pg16
        env:
          POSTGRES_PASSWORD: postgres
        ports: ['5432:5432']
        options: --health-cmd pg_isready --health-interval 10s
      redis:
        image: redis:7-alpine
        ports: ['6379:6379']
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: pgsql, redis
          coverage: pcov
      - uses: actions/setup-node@v4
        with: { node-version: 22 }
      - name: Install
        run: |
          composer install --no-interaction --prefer-dist
          npm ci
      - name: Build assets
        run: npm run build
      - name: Lint PHP
        run: vendor/bin/pint --test
      - name: Static analysis
        run: vendor/bin/phpstan analyse --memory-limit=2G
      - name: Lint JS
        run: npm run lint
      - name: Prepare test env
        run: |
          cp .env.testing .env
          php artisan key:generate
          php artisan migrate --force
      - name: Tests
        run: vendor/bin/pest --coverage --min=70
      - name: Composer audit
        run: composer audit
      - name: npm audit
        run: npm audit --production

  dusk:
    runs-on: ubuntu-latest
    needs: test
    if: github.event_name == 'push' && github.ref == 'refs/heads/main'
    steps:
      # ... same setup ...
      - name: Run Dusk
        run: php artisan dusk
```

Required-to-pass checks on PRs: `test`. `dusk` runs only on `main`.

## Deploy pipeline

Forge's deploy script triggered by:
- **Staging:** Forge webhook on push to `main`. Auto-deploys.
- **Production:** Manual deploy via Forge UI. Requires explicit approval.

### Deploy script

```bash
cd $FORGE_SITE_PATH

git pull origin main

# Maintenance mode with a friendly page
php artisan down --render="errors::503" --retry=10 --secret="$DEPLOY_SECRET"

# Install dependencies
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Migrate (idempotent, transactional where possible)
php artisan migrate --force

# Optimize
php artisan optimize:clear
php artisan optimize
php artisan view:cache
php artisan event:cache

# Build assets (only on staging; prod uses prebuilt)
npm ci
npm run build

# Restart workers gracefully
php artisan queue:restart
sudo -S service php8.3-fpm reload

# Bring back up
php artisan up

# Tag the release in Sentry
curl https://sentry.io/api/0/organizations/sana/releases/ \
  -H "Authorization: Bearer $SENTRY_AUTH_TOKEN" \
  -d "version=$(git rev-parse HEAD)" \
  -d "projects=sana-bouhamidi"
```

## Zero-downtime deploys

Forge's "Zero downtime deployment" toggle enabled. Mechanics:

- Each release goes into `releases/<timestamp>/`.
- Symlink `current` swaps atomically after migrations succeed.
- `php-fpm` reload (not restart) keeps in-flight requests alive.
- Workers gracefully shut down via `queue:restart`.

Maintenance mode (`php artisan down`) is only used when a destructive migration is incoming. The `--secret` allows the lead to test the new deploy before bringing the site back up.

## Secrets management

- Production secrets stored in **Forge's environment manager** (encrypted at rest).
- `.env` file generated from Forge's env on deploy.
- No developer machine has production secrets.
- Rotation schedule:
  - Stripe webhook secret: yearly
  - Anthropic / Voyage / Twilio API keys: 6-monthly
  - Resend API key: 6-monthly
  - Supabase service-role key: 6-monthly
  - `APP_KEY`: yearly with key-rotation playbook
- Rotation is a manual checklist in `OPERATIONS/runbooks/` (to be created).

## Database migrations

- Migrations are append-only. Never edit a migration that has run.
- Destructive migrations (column drops, table renames, index changes on hot tables) require:
  1. Announcement on staging deploy.
  2. Maintenance window for prod (low-traffic time).
  3. A rollback plan.
- Long-running migrations (>30 seconds) should be split:
  - Phase 1: add new column nullable.
  - Phase 2: backfill (queued job).
  - Phase 3: code uses new column.
  - Phase 4: drop old column.
- Migrations run in transactions where Postgres supports it; mixed-DDL migrations may need manual rollback steps.

## Asset builds

- Vite builds locally and on staging.
- Production deploys use **prebuilt** assets to keep deploys fast:
  - GitHub Action builds and uploads `public/build/` as an artifact.
  - Forge fetches the artifact in the deploy script and skips `npm run build`.
- Cache-busting via Vite's content-hashed filenames.

## Static asset caching

Nginx config sets `Cache-Control: public, max-age=31536000, immutable` on:
- `/build/*` (Vite output)
- `/images/*`
- `/fonts/*`

HTML responses get `Cache-Control: no-cache, must-revalidate`.

## TLS

- Let's Encrypt via Forge.
- Auto-renewal verified weekly by Forge.
- HSTS enabled with 1-year max-age and `includeSubDomains; preload`.

## DNS

- Provider: Cloudflare (free tier).
- Mode: DNS-only (gray cloud) for now to keep things simple. Move to proxied (orange cloud) at v1.2 for DDoS protection + edge caching.
- Records:
  - `A sana-bouhamidi.ma → <Hetzner IP>`
  - `A www.sana-bouhamidi.ma → <Hetzner IP>` (redirects to apex via Nginx)
  - `A staging.sana-bouhamidi.ma → <Staging Hetzner IP>`
  - `MX → 10 sana.example-email-host.com` (set once email is configured)
  - `TXT @ → "v=spf1 include:resend.com -all"` (Resend SPF)
  - `TXT @ → "v=DMARC1; p=quarantine; rua=mailto:dmarc@sana-bouhamidi.ma"`
  - DKIM record from Resend dashboard.

## Email deliverability

- SPF, DKIM, DMARC configured before sending any production email.
- Resend domain verified.
- Bounce / complaint webhooks set up (see `ARCHITECTURE/notifications.md`).
- Warm-up period for new domain: ramp up volume gradually over 2 weeks before launch.

## Queue workers (Horizon)

- `php artisan horizon` runs as a Supervisor process on the box.
- Configuration in `config/horizon.php`:
  - `default` queue: 2–6 processes, balance=auto
  - `notifications` queue: 1–4 processes, higher priority
- Worker auto-restart on file deploy via `queue:restart`.
- Failed-job retention: 7 days.

## Scheduler

Forge cron entry:

```
* * * * * cd /home/forge/sana-bouhamidi.ma && php artisan schedule:run >> /dev/null 2>&1
```

Scheduled jobs (defined in `app/Console/Kernel.php`):

| Job | Frequency | Purpose |
|---|---|---|
| `PurgeExpiredBookingHolds` | every 5 min | Clean up abandoned holds |
| `PurgeExpiredDocuments` | daily at 03:00 | Apply retention |
| `PurgeExpiredMagicLinks` | daily at 03:15 | Clean up consumed/expired links |
| `RunDataRetention` | daily at 03:30 | All other retention rules |
| `BackfillEmbeddingsIfNeeded` | hourly | Catch any FAQ without embedding |
| `SendFailedJobsDigest` | daily at 09:00 | Email admin if anything failed |
| `RemindAdminOfPendingApprovals` | weekly | Refund approvals etc. |
| `RotateRequestIDs` | every hour | Internal correlation |

## Monitoring during deploy

- Forge sends a Slack-style notification on deploy success/failure (if configured).
- Sentry releases automatically created from deploy hook.
- Manual smoke test checklist after every prod deploy:
  - [ ] `/up` returns 200
  - [ ] Homepage loads in both `/ar/` and `/fr/`
  - [ ] Booking calendar loads
  - [ ] Admin login works
  - [ ] Sentry shows the new release

## Rollback procedure

If a deploy breaks production:

1. In Forge, click "Rollback" on the previous release.
2. Forge swaps the `current` symlink back; PHP-FPM reloads.
3. If the broken release ran destructive migrations:
   - Stop. Don't auto-rollback.
   - Restore the DB from Supabase PITR to a point before the deploy.
   - Replay non-destructive writes if any (typically there won't be many in the rollback window).
   - Run a fresh deploy from a known-good commit.
4. Post-incident: write up what happened in `OPERATIONS/backup-recovery.md` or a similar incident log.

## Hotfix process

For critical prod bugs:

1. Branch from the prod tag: `git checkout -b hotfix/<scope>-<short> v1.2.3`.
2. Fix, test locally and in staging.
3. PR fast-track review.
4. Merge to `main`, tag `v1.2.4`.
5. Deploy to prod.

See `STANDARDS/git-workflow.md` for full procedure.

## Capacity planning

Current CCX13 ceiling (rough numbers):

- ~5k req/min on the public site
- ~1k concurrent Livewire connections
- ~50 simultaneous chatbot streams
- Queue throughput: ~500 jobs/min

Above these, the upgrade path:

1. Scale up to CCX23 (8 vCPU, 32 GB) — vertical, ~5 min downtime
2. Add a second box for Horizon and Pulse — horizontal
3. Move Redis to a managed service
4. Add Cloudflare proxy + caching

## Disaster recovery overview

See `OPERATIONS/backup-recovery.md` for the full procedure. Headline:

- **RPO (Recovery Point Objective):** ≤ 1 hour (PITR with 1-min snapshot frequency on Supabase Pro)
- **RTO (Recovery Time Objective):** ≤ 4 hours

## Cost ledger

| Item | $/mo |
|---|---|
| Hetzner CCX13 | ~17 |
| Hetzner CPX21 (staging) | ~7 |
| Forge subscription | 15 |
| Supabase Pro (prod) | 25 |
| Supabase Pro (staging — optional) | 25 |
| Resend | 0 (free tier) |
| Twilio (SMS + WhatsApp at expected volume) | ~25 |
| Sentry Team | 26 |
| Plausible Analytics | 9 |
| Domain | ~1 |
| Anthropic API (estimated) | ~20 |
| Voyage AI | ~3 |
| **Total** | **~150** |

Reviewed monthly.

## Compliance touch-points

- Supabase region: EU-Central-1 (data in EU — required for Loi 09-08 PII).
- Sentry region: EU.
- Resend: EU region available.
- Twilio: data center in Ireland.
- Anthropic: US — but no client PII sent (see `ARCHITECTURE/chatbot.md`).
- Stripe: EU (Ireland).

Data flows audited annually; see `COMPLIANCE/loi-09-08.md`.
