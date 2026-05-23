# Rotate Cerebras API Key

## When to rotate
- Annually (or per Cerebras's recommendation)
- After any suspected key exposure

## Procedure

```bash
# 1. Generate new key
#   - Cerebras Console → API Keys → Create Key
#   - Copy the key value
#   - Keep the old key temporarily (both active if allowed)

# 2. Update staging
#   - Forge → Environment → CEREBRAS_API_KEY → paste → Save
#   - Deploy

# 3. Verify staging
#   - Send a test chatbot message on staging
#   - Check logs for successful API call

# 4. Update production
#   - Same as step 2 but on production
#   - Deploy

# 5. Revoke old key
#   - Cerebras Console → API Keys → Delete old key
#   - Confirm no errors in Sentry/Pulse for 1 hour

# 6. Verify production
#   - Send a test chatbot message
#   - Confirm response received within normal latency
```

## Impact
- Brief window where in-flight requests fail (milliseconds)
- No user-facing impact if done during low traffic

## Rollback
Re-apply the old API key in environment and redeploy.
