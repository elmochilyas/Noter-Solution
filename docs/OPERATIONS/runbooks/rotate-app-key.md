# Rotate Application Key

## When to rotate
- Annually (minimum)
- After any suspected key compromise
- After any developer with access leaves the project

## Procedure

```bash
# 1. Generate new key locally (old key stays in .env until all workers restart)
php artisan key:generate --show
# Copy the output

# 2. Update staging
#   - Forge → Environment → APP_KEY → paste new value → Save
#   - Deploy or restart PHP-FPM

# 3. Verify staging
#   - Visit staging, confirm no encryption/decryption errors
#   - Check Sentry for `DecryptException` errors

# 4. Update production
#   - Forge → Environment → APP_KEY → paste new value → Save
#   - Deploy or restart PHP-FPM

# 5. Verify production
#   - Visit production, confirm no errors
#   - Check Sentry for 15 min after switch
```

## Impact
- All existing sessions are invalidated (users must re-login)
- All encrypted DB columns (CIN, internal notes) remain readable (decrypts with new key automatically)
- Cookie-stored data is re-encrypted on next request

## Rollback
Re-apply the previous APP_KEY value and restart PHP-FPM. Sessions still invalidated.
