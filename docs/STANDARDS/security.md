# Security Standards

This is a notary's website handling identity documents, financial transactions, and family-law matters. Security is non-negotiable.

## Threat model summary

| Asset | Threat | Mitigation |
|---|---|---|
| Client PII (name, phone, CIN) | Exposure via injection, broken access control | Parameterized queries, policy authorization, RLS-like Laravel policies |
| Uploaded documents (CIN scans, titles deeds) | Exfiltration, unauthorized access | Encrypted at rest, signed URLs only, expiry, owner-only access |
| Payment data | Card data theft | Never touch card data — Stripe.js handles it; we only see tokens |
| Admin account | Compromise leads to total system access | Strong password + mandatory 2FA, session hardening, IP allow-list optional |
| Site itself | Defacement, malware injection | HTTPS, CSP, file upload validation, dependency scanning |
| Privacy | Non-compliance with Loi 09-08 | See `COMPLIANCE/loi-09-08.md` |

## Authentication

### Client authentication — magic link

- Login is by email only.
- A signed URL (Laravel `URL::temporarySignedRoute`) is sent by email.
- Link expires in **15 minutes**.
- Link is single-use — invalidated on consumption.
- Sessions live for **2 hours** of inactivity, hard cap of **24 hours**.
- On every magic-link login, log the event with IP and User-Agent.

### Admin authentication — password + 2FA

- Email + password + TOTP via `pragmarx/google2fa-laravel`.
- Passwords required: minimum 14 characters, must include letters and digits, checked against HaveIBeenPwned via `password_pwned()` helper.
- Password hashing: bcrypt cost ≥ 12.
- 2FA setup is **mandatory** before first login completes. No bypass.
- Recovery codes (8 single-use) issued at 2FA setup.
- Failed login attempts: lockout after 5 failures in 15 minutes, with 30-minute cooldown.
- Admin sessions live **30 minutes** of inactivity, hard cap **8 hours**.
- Admin login requires `Verified-Device` cookie (set on first successful 2FA, 30-day expiry) — new devices trigger an email notification.

### Session security

```php
// config/session.php
'lifetime' => 30,                  // admin context, overridden per guard
'expire_on_close' => false,
'encrypt' => true,
'secure' => true,                  // HTTPS only
'http_only' => true,
'same_site' => 'lax',
'domain' => env('SESSION_DOMAIN'),
```

- Session ID regenerated on every login and privilege change.
- Logout invalidates session server-side, not just client cookie.

## Authorization

- Every controller action and Livewire method is gated by a Policy or Gate.
- Filament resources use `canViewAny`, `canView`, `canCreate`, `canUpdate`, `canDelete`.
- Default deny — explicitly allow.
- Tests assert authorization for every action (positive + negative).

### Role matrix

| Role | Bookings | Payments | Refunds | Content | Settings | Users |
|---|---|---|---|---|---|---|
| Guest | view own (none) | — | — | view public | — | — |
| Client | view own, cancel own | view own | — | view public | — | — |
| Assistant | view all, cancel | view all | propose only | edit FAQ + service pages | view only | view only |
| Owner (Sana) | full | full | approve | full | full | full |

Implemented via `spatie/laravel-permission`.

## Input validation

- **Every** HTTP input goes through a `FormRequest` with explicit rules.
- Validation rules must include:
  - Type rules (`string`, `integer`, `email`)
  - Size rules (`max`, `min`)
  - Format rules where applicable (`regex`, custom rules)
  - Existence rules where referring to DB records (`exists:bookings,id`)
- `prohibited`-style rules for fields a user must never submit (e.g. `status` on create).
- Validate `bail` early on dangerous fields.

```php
public function rules(): array
{
    return [
        'plan_id' => ['required', 'integer', 'exists:consultation_plans,id'],
        'slot' => ['required', 'date', 'after:now'],
        'phone' => ['required', new MoroccanPhone()],
        'documents.*' => ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
        'status' => ['prohibited'],
    ];
}
```

## Mass assignment

- `$fillable` declared explicitly on every model.
- **Never** `$guarded = []`.
- Never pass `$request->all()` to `Model::create()` or `Model::update()`. Use validated data: `$request->validated()`.

## Database security

- All queries through Eloquent or the query builder. **Zero raw SQL with interpolation.**
- Where raw SQL is unavoidable (PostGIS, vector search), use parameter bindings:
  ```php
  DB::select('SELECT * FROM faqs ORDER BY embedding <-> ? LIMIT 5', [$queryVector]);
  ```
- Sensitive columns encrypted via Laravel's encrypted casts:
  ```php
  protected $casts = [
      'national_id' => 'encrypted',
      'notes_internal' => 'encrypted',
  ];
  ```
- Hash columns that need to be searchable but should not be readable (e.g. national_id_hash for deduplication).

## XSS prevention

- All Blade output uses `{{ }}` (escaped). `{!! !!}` is forbidden except for content from a trusted source (e.g. Markdown rendered server-side via a known-safe library).
- Strict CSP header on all responses (see CSP section).
- Sanitize HTML inputs via `mews/purifier` if any rich-text fields are introduced (none in v1).

## CSRF

- Laravel's CSRF middleware enabled globally.
- Webhook endpoints (`/webhooks/stripe`, `/webhooks/twilio`) are excluded but secured by signature verification.

## CSRF webhook signature checks

- Stripe: `Stripe\Webhook::constructEvent()` with the webhook secret. Reject if signature is invalid or timestamp tolerance exceeded.
- Twilio: validate `X-Twilio-Signature` using the `twilio/sdk` validator.
- WhatsApp Business via Twilio: same validator.

## Rate limiting

| Endpoint | Limit |
|---|---|
| Magic-link request | 3 per hour per email + 10 per hour per IP |
| Admin login | 5 per 15 min per email + 20 per 15 min per IP |
| Booking submission | 10 per hour per IP |
| Chatbot messages | 30 per hour per session, 100 per day per IP |
| Contact form | 5 per hour per IP |
| Any unauthenticated POST | 60 per minute per IP (global) |

Implemented via Laravel rate-limit middleware with named limiters in `App\Providers\RouteServiceProvider`.

## File upload security

- Allowed MIME types: `pdf`, `jpg`, `jpeg`, `png` only.
- Max file size: 10 MB.
- Server-side MIME sniffing via `getMimeType()` — never trust the client header alone.
- Filenames sanitized: random UUID + sniffed extension. Original name stored in DB metadata, never in path.
- Files stored on Supabase Storage with `private` visibility.
- Access only via signed URLs (`Storage::temporaryUrl()`) with 5-minute expiry.
- No public listing endpoints.
- Virus scan hook in place via a queued job (`ScanDocumentForViruses`). Can integrate with ClamAV or VirusTotal API later. v1 marks files as "pending scan" and they're not downloadable until scanned.

## Secrets and environment

- All secrets in `.env`. **No secrets in code, no secrets in git history.**
- `.env` is in `.gitignore`. `.env.example` is committed with placeholder values.
- Production secrets stored in Forge env, not on the developer machines.
- Cerebras / Stripe / Twilio / Supabase keys rotated every 6 months minimum.
- Pre-commit hook scans for accidentally committed secrets via `gitleaks`.
- Pre-deploy CI step blocks deploys if `.env` files appear in the diff.

## Encryption

- HTTPS everywhere — HTTP redirects to HTTPS, HSTS header with `max-age=31536000; includeSubDomains; preload`.
- TLS 1.2 minimum; prefer 1.3.
- Encryption at rest:
  - Database: Supabase encrypts at rest by default.
  - File storage: Supabase Storage encrypts at rest by default.
  - Sensitive columns: additionally encrypted via Laravel's `encrypted` cast (defense in depth).
- `APP_KEY` rotated yearly with key rotation procedure (`php artisan key:rotate` — not built-in, see `OPERATIONS/`).

## Security headers

Set globally via middleware:

```php
'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains; preload',
'X-Frame-Options' => 'DENY',
'X-Content-Type-Options' => 'nosniff',
'Referrer-Policy' => 'strict-origin-when-cross-origin',
'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
'Content-Security-Policy' => "
    default-src 'self';
    script-src 'self' 'nonce-{NONCE}' https://js.stripe.com https://meet.jit.si;
    style-src 'self' 'unsafe-inline';
    img-src 'self' data: https:;
    font-src 'self';
    connect-src 'self' https://api.stripe.com https://*.supabase.co;
    frame-src https://js.stripe.com https://meet.jit.si;
    frame-ancestors 'none';
    base-uri 'self';
    form-action 'self';
",
```

Nonces are generated per request and added to inline scripts that need them.

## Dependency security

- `composer audit` runs in CI on every PR.
- `npm audit` runs in CI on every PR.
- Dependabot enabled for both ecosystems.
- Major-version dependency upgrades require explicit approval and a regression run.

## Logging — what NOT to log

Never log:
- Passwords (raw or hashed)
- Card numbers, CVCs, expiry dates
- Full national ID numbers (CIN) — log last 4 digits only if needed for debugging
- Magic-link tokens
- Session tokens
- 2FA secrets / recovery codes
- Document contents

What to log (with PII pseudonymized):
- Authentication events (user_id, IP, UA, success/failure)
- Authorization denials
- Payment events (without card data — Stripe charge ID is enough)
- Admin actions (audit log via `spatie/laravel-activitylog`)

## Audit log

- All admin write actions logged via `spatie/laravel-activitylog`.
- Retention: 2 years minimum.
- Includes: actor, action, subject, before/after state for changes.
- Logs themselves are append-only (no delete permission via the app — only Sana via DB).

## OWASP Top 10 mapping

| OWASP item | Where addressed |
|---|---|
| A01 Broken access control | Policies on every action, role matrix above |
| A02 Cryptographic failures | TLS, encryption at rest, encrypted casts, no plaintext PII |
| A03 Injection | Eloquent / bindings only, FormRequest validation, no eval/unserialize of user input |
| A04 Insecure design | Threat model in this doc, design review on architecture changes |
| A05 Security misconfiguration | Hardened headers, secrets in env, CSP, no debug in prod |
| A06 Vulnerable components | `composer audit`, `npm audit`, Dependabot |
| A07 Auth failures | Strong passwords + 2FA, rate limits, lockout, session hardening |
| A08 Software/data integrity | Signed webhooks, pinned dependencies, lock files committed |
| A09 Logging failures | Structured logging, Sentry, audit log, retention |
| A10 SSRF | No user-controlled URL fetching except via allow-list |

## Security review checkpoints

- **Per PR:** automated (Larastan rules, composer audit, secret scan).
- **Per phase:** manual checklist review by reviewer.
- **Pre-launch:** dedicated security review pass (see `PHASES/07-polish-launch.md`).
- **Post-launch:** quarterly review and dependency upgrade sprint.

## Incident response

Brief procedure (full version in `OPERATIONS/backup-recovery.md`):

1. Suspected breach → take affected system offline via maintenance mode (`php artisan down`).
2. Preserve logs (do not delete or restart anything that would clear in-memory state).
3. Notify Sana within 1 hour.
4. If PII confirmed exposed, notify CNDP within 72 hours (Loi 09-08 requirement).
5. Notify affected users within 72 hours of confirmation.
6. Rotate all credentials.
7. Document incident, root cause, remediation in `OPERATIONS/`.
