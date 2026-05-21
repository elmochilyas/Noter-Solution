# Authentication & Authorization Architecture

## Two distinct auth contexts

1. **Client auth** — magic-link only, low friction, for end users booking consultations.
2. **Admin auth** — email + password + mandatory 2FA, for Sana and her assistant.

These use **different guards** so they cannot impersonate each other accidentally.

```php
// config/auth.php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',         // admin users table
    ],
    'client' => [
        'driver' => 'session',
        'provider' => 'clients',       // clients table
    ],
],
'providers' => [
    'users' => ['driver' => 'eloquent', 'model' => User::class],
    'clients' => ['driver' => 'eloquent', 'model' => Client::class],
],
```

## Client authentication — magic link flow

### Flow

```
1. Client visits /portal/login
2. Enters email
3. POST /portal/login → MagicLinkController::request
4. Server:
   - Rate-limit check (3/hr per email, 10/hr per IP)
   - Find or create Client by email
   - Generate token (random 32 bytes, base64url-encoded)
   - Hash the token (sha256), store hash + expires_at (15 min) in magic_links
   - Email the token via signed URL: /portal/login/verify?t=<token>&id=<client_uuid>
5. Client clicks the link
6. GET /portal/login/verify → MagicLinkController::verify
7. Server:
   - Validate URL signature
   - Hash the token, find magic_link by hash
   - Reject if expired, consumed, or hash mismatch
   - Mark magic_link consumed_at
   - Auth::guard('client')->login($client)
   - Regenerate session ID
   - Log MagicLinkConsumed event with IP + UA
   - Redirect to /me
```

### Token format

- Generated via `random_bytes(32)`, base64-url-encoded.
- Length: ~43 characters.
- Stored hashed (sha256) — never plaintext.
- Single-use — invalidated on consumption.
- Expiry: 15 minutes.

### Why magic link, not OTP

- Email is required anyway for confirmations.
- Users hate copying 6-digit codes from email; clicking is friction-free.
- No SMS cost (OTP would need SMS for delivery).
- Single-use links can't be replayed (OTP can be intercepted).

### Edge cases

- **Email not yet linked to a Client:** the link still works (we create the Client). This is how new bookings flow into the portal without separate signup.
- **Expired link:** show "Lien expiré, demandez-en un nouveau."
- **Already consumed:** show "Ce lien a déjà été utilisé."
- **IP / device change between request and consume:** allowed (link is portable), but logged.

## Admin authentication

### Stack

- **Laravel Fortify** for password + 2FA scaffolding.
- **`pragmarx/google2fa-laravel`** for TOTP.
- **Filament login page** customized to use Fortify.

### Flow

```
1. Admin visits /admin/login
2. Enters email + password
3. POST → AdminLoginController (Fortify)
4. Server:
   - Rate-limit check (5/15min per email)
   - Verify password (bcrypt cost ≥ 12)
   - If 2FA not yet set up: redirect to /admin/2fa/setup
   - Otherwise: redirect to /admin/2fa/challenge
5. /admin/2fa/challenge:
   - Show TOTP code field
   - User enters code from authenticator app
   - Verify against user.two_factor_secret
   - If success: complete login, regenerate session ID, set Verified-Device cookie
   - If fail: increment counter, lock account after 5 failures
6. Redirect to /admin
```

### 2FA setup

On first login (or first login after admin reset):

1. Server generates a random 16-character base32 secret.
2. Show QR code (otpauth URI) + manual key.
3. User scans with Google Authenticator / Authy / 1Password.
4. User enters two consecutive valid codes to confirm.
5. Server generates 8 single-use recovery codes; display once, ask user to save.
6. Mark `two_factor_confirmed_at`.

### Recovery codes

- 8 codes, each 10 alphanumeric characters.
- Stored hashed (bcrypt) in `two_factor_recovery_codes` (JSON array of hashes).
- Each code single-use.
- Used at the 2FA challenge if the user lost their device.

### Verified-Device cookie

- Set after first successful 2FA login from a device.
- 30-day expiry.
- Contains a signed token bound to user_id + device fingerprint hash.
- New device (no valid cookie) triggers an email notification to the admin.

### Admin lockout

- 5 failed login attempts in 15 minutes → 30-minute lockout.
- Lockouts logged.
- Recovery: admin can reset another admin's lockout via Filament settings (audit-logged).

### Password policy

- Minimum 14 characters.
- Must contain letters and digits.
- Checked against the HaveIBeenPwned `range` API (k-anonymity).
- Rejected if previously used (last 5 passwords stored as hashes).
- No expiry (modern guidance — long, unique passwords don't need rotation, but breach response does).

## Session security

### Cookies

- `secure` (HTTPS only).
- `httponly` (no JS access).
- `samesite=lax`.
- Encrypted by default (Laravel).

### Lifetimes

| Guard | Idle timeout | Hard cap |
|---|---|---|
| `client` | 2 hours | 24 hours |
| `web` (admin) | 30 minutes | 8 hours |

Enforced via custom middleware checking `last_activity_at` on each request.

### Session fixation

- Session ID regenerated on:
  - Successful login (both guards)
  - 2FA success (admin)
  - Logout
  - Privilege change (e.g. role assigned)

## Authorization — RBAC

### Roles

Defined via `spatie/laravel-permission`.

| Role | Assigned to | Description |
|---|---|---|
| `owner` | Sana | Full access |
| `assistant` | Office assistant | Day-to-day operations, restricted on sensitive changes |
| `client` | All clients | Implicit role; not stored in permission tables |

### Permissions matrix

| Permission | owner | assistant | client |
|---|---|---|---|
| `bookings.view.all` | ✓ | ✓ | — |
| `bookings.view.own` | — | — | ✓ |
| `bookings.create` | ✓ | ✓ | ✓ (own) |
| `bookings.update` | ✓ | ✓ | — |
| `bookings.cancel` | ✓ | ✓ | ✓ (own, ≥2h before) |
| `bookings.reschedule` | ✓ | ✓ | ✓ (own, ≥2h before) |
| `bookings.complete` | ✓ | ✓ | — |
| `bookings.refund` | ✓ | request only | — |
| `payments.view` | ✓ | ✓ | own only |
| `payments.refund.approve` | ✓ | — | — |
| `clients.view.all` | ✓ | ✓ | — |
| `clients.edit` | ✓ | ✓ | — |
| `clients.delete` | ✓ | — | — |
| `content.edit` | ✓ | ✓ | — |
| `plans.edit` | ✓ | — | — |
| `availability.edit` | ✓ | ✓ | — |
| `settings.view` | ✓ | ✓ | — |
| `settings.edit` | ✓ | — | — |
| `users.invite` | ✓ | — | — |
| `users.disable` | ✓ | — | — |
| `chatbot.review` | ✓ | ✓ | — |
| `chatbot.faq.edit` | ✓ | ✓ | — |
| `documents.view.all` | ✓ | ✓ | — |
| `audit.view` | ✓ | — | — |

### Policies

Every model has a corresponding `app/Policies/<Model>Policy.php`. Policies use the permission strings above plus instance-level checks (own vs. all).

```php
class BookingPolicy
{
    public function viewAny(Client|User $actor): bool
    {
        return $actor instanceof User && $actor->can('bookings.view.all');
    }

    public function view(Client|User $actor, Booking $booking): bool
    {
        if ($actor instanceof User) {
            return $actor->can('bookings.view.all');
        }
        return $actor->id === $booking->client_id;
    }
}
```

### Filament authorization

Filament resources delegate to policies:

```php
class BookingResource extends Resource
{
    public static function canViewAny(): bool
    {
        return Auth::user()->can('bookings.view.all');
    }
    // ... canView, canCreate, etc.
}
```

### Authorization tests

Every protected route or action requires a positive **and** a negative test (see `STANDARDS/testing.md`). Tests run in CI.

## Logout

- `POST /logout` (CSRF-protected).
- Server invalidates session in the DB / cache, regenerates the CSRF token.
- Client clears cookies.
- Redirect to `/` (clients) or `/admin/login` (admin).

## "Login as another user" — NOT supported

We never impersonate users. If support is needed, the admin opens the relevant booking / client record in Filament directly.

## Account lifecycle

### Client account creation

- Implicit: created on first booking.
- Email is the unique identifier — duplicate emails merge into one Client.
- No password ever set.

### Client account deletion (GDPR-style right to erasure)

Triggered by the client emailing the practice or through a "Supprimer mon compte" link in the portal.

Process:
1. Verify identity (magic link to the registered email).
2. The Client record is anonymized:
   - `email` → `deleted-<uuid>@anonymized.local`
   - `full_name` → "(supprimé)"
   - `phone` → `+212000000000`
   - `national_id` cleared
3. Past bookings, payments, receipts are retained (legal obligation) but no longer linked to a contactable individual.
4. Documents older than 90 days post-appointment are purged (they already are by default).
5. Audit-log entry created.

Hard delete of the row would break referential integrity with retained legal records. Anonymization is the right balance.

### Admin account creation

- Only an existing `owner` can invite new admin users.
- Invitation: signed email link valid for 72 hours.
- Invitee sets password + 2FA in the same flow.
- New admin starts with `assistant` role.

### Admin account disable

- Owners can disable any admin (including themselves — at least one active owner must exist).
- Disabled users cannot log in but historical data remains intact.

## Account takeover protections

- New-device email on admin login (different IP / UA / time pattern).
- "Was this you?" email on every magic-link consumption with abnormal pattern (new IP, new UA).
- Concurrent session limit: max 3 active sessions per admin (oldest auto-logged-out).
- Password change requires re-authentication.
- 2FA reset requires recovery code OR another owner's approval.

## Security event logging

The following are logged to the activity log:

- Login success / failure
- Logout
- 2FA challenge success / failure
- 2FA setup completed
- Recovery code used
- New device detected
- Password changed
- Magic link requested / consumed
- Account disabled / re-enabled
- Role / permission change
