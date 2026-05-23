# Production Deploy

## Prerequisites
- PR merged to `main` on GitHub
- CI green on `main` (lint, static analysis, tests, security audit)
- Staging deployed and smoke-tested

## Manual approval gate
Production deploys require manual approval in Forge:
1. Forge → Sites → sana-bouhamidi → Deployments
2. Verify the commit SHA matches what was merged to `main`
3. Click "Deploy"

## Deploy script (managed by Forge)

```bash
cd /home/forge/sana-bouhamidi.ma
git pull origin main
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
npm ci && npm run build

php artisan down --render="errors::503"

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

php artisan up

php artisan horizon:terminate
# Horizon supervisor restarts automatically via Forge
```

## Post-deploy verification

```bash
# 1. Health check
curl -s https://sana-bouhamidi.ma/up | jq .

# 2. Smoke test public page
curl -s -o /dev/null -w "%{http_code}" https://sana-bouhamidi.ma/ar/

# 3. Check Pulse
open https://sana-bouhamidi.ma/admin/pulse

# 4. Check Sentry for new errors
open https://sana-bouhamidi.sentry.io
```

## Rollback
1. Forge → Deployment → Previous deploy → Rollback
2. Verify `GET /up` returns 200
3. Monitor Sentry for 15 min
