# Rotate Stripe Webhook Secret

## When to rotate
- Annually
- After any suspected secret exposure
- If webhook signature verification errors spike (may indicate mismatch)

## Procedure

```bash
# 1. Generate new secret
#   - Stripe Dashboard → Developers → Webhooks
#   - Select the endpoint → "Reveal" or "Rotate signing secret"
#   - Copy the new `whsec_...` value

# 2. Update staging
#   - Forge → Environment → STRIPE_WEBHOOK_SECRET → paste → Save
#   - Deploy

# 3. Verify staging
#   - Trigger a test webhook from Stripe Dashboard
#   - Check Laravel log for "webhook.stripe" entries

# 4. Update production
#   - Same as step 2 but on production Forge env
#   - Deploy

# 5. Verify production
#   - Complete a low-amount test payment end-to-end
#   - Verify webhook processed (check Sentry, logs)
```

## Impact
- Brief window where in-flight webhooks fail (seconds to minutes). Stripe retries with exponential backoff.
- No user-facing impact.

## Rollback
Restore previous `whsec_...` value in environment and redeploy.
