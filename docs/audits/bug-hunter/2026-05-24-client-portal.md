# Bug Hunt Session — Client Portal Surface (D)

**Date:** 2026-05-24
**Scope:** D — Client portal (magic link, bookings, documents, account deletion)

## Files scanned

- `app/Http/Controllers/Portal/BookingController.php`
- `app/Http/Controllers/Portal/CancelController.php`
- `app/Http/Controllers/Portal/RescheduleController.php`
- `app/Http/Controllers/Portal/PreferenceController.php`
- `app/Http/Controllers/Portal/DownloadController.php`
- `app/Http/Controllers/Portal/ReceiptController.php`
- `app/Http/Controllers/Portal/AccountDeletionController.php`
- `app/Http/Controllers/Auth/MagicLinkController.php`
- `app/Http/Middleware/ClientSessionLifetime.php`
- `app/Services/Auth/MagicLinkService.php`
- `app/Domain/Services/BookingService.php`
- `app/Domain/Services/AvailabilityService.php`
- `app/Domain/Services/DocumentService.php`
- `app/Domain/Services/NotificationService.php`
- `app/Domain/Services/PaymentService.php`
- `app/Models/Client.php`
- `app/Models/Booking.php`
- `app/Models/MagicLink.php`
- `app/Models/Document.php`
- `app/Models/Receipt.php`
- `app/Policies/BookingPolicy.php`
- `app/Policies/ClientPolicy.php`
- `app/Policies/DocumentPolicy.php`
- `app/Policies/ReceiptPolicy.php`
- `app/Listeners/IssueRefundIfApplicable.php`
- `app/Listeners/SendMagicLinkNotification.php`
- `app/Listeners/SendBookingCancelledNotifications.php`
- `app/Listeners/SendBookingRescheduledNotifications.php`
- `app/Listeners/RescheduleReminders.php`
- `app/Events/MagicLinkRequested.php`
- `app/Events/BookingCancelled.php`
- `app/Events/BookingRescheduled.php`
- `app/Providers/EventServiceProvider.php`
- `app/Observers/ClientObserver.php`
- `app/ValueObjects/MoroccanPhoneNumber.php`
- `app/ValueObjects/BookingData.php`
- `resources/views/portal/dashboard.blade.php`
- `resources/views/portal/bookings/show.blade.php`
- `resources/views/portal/bookings/cancel.blade.php`
- `resources/views/portal/account/delete.blade.php`
- `routes/web.php`
- `tests/Feature/Portal/*.php`
- `tests/Feature/Auth/MagicLinkTest.php`
- `tests/Feature/MiddlewareTest.php`
- `tests/Feature/RescheduleLimitTest.php`
- `tests/Feature/FreeOrientationRateLimitTest.php`
- `docs/FEATURES/client-portal.md`
- `docs/ARCHITECTURE/auth.md`

## Bugs Found

### BUG-022 — (P1) Reschedule crashes with 500 when client has no phone
- **Status: fixed**
- **Files:**
  - `app/Http/Controllers/Auth/MagicLinkController.php:56` — cause
  - `app/Domain/Services/BookingService.php:125` — crash site
  - `app/ValueObjects/MoroccanPhoneNumber.php:12-28` — validation rejects the value
- **What it does wrong:** `MagicLinkController::store()` creates new clients with `phone = '0000000000'`. When that client later attempts to reschedule, `BookingService::reschedule()` calls `MoroccanPhoneNumber::fromInput('0000000000')` which throws `InvalidArgumentException` (not caught — controller only catches `\RuntimeException`). Client gets a 500 error and the old booking is half-cancelled.
- **Fix:**
  1. Changed placeholder phone to `null` in MagicLinkController
  2. Added null/invalid phone guard in BookingService::reschedule() with a valid E.164 fallback (`+212600000000`)

### BUG-023 — (P2) DownloadController missing scan_status guard for pending documents
- **Status: fixed**
- **File:** `app/Http/Controllers/Portal/DownloadController.php:24-26`
- **What:** Only checked `infected` but not `pending`. Unscanned documents could be downloaded via direct URL before virus scan completes.
- **Fix:** Added `|| $document->scan_status === 'pending'` to the guard with appropriate message (`__('portal.scanning')`).

### BUG-024 — (P2) Cancel eligibility duplicated inline in BookingController::show()
- **Status: fixed**
- **File:** `app/Http/Controllers/Portal/BookingController.php:32-37`
- **What:** Cancel/reschedule eligibility logic duplicated from `BookingPolicy::cancel()`. Policy changes would drift from controller behavior, showing buttons that lead to 403.
- **Fix:** Replaced inline checks with `$client->can('cancel', $booking)`.

### BUG-025 — (P3) Hardcoded "Ouvrir" in dashboard view
- **Status: fixed**
- **File:** `resources/views/portal/dashboard.blade.php:102`
- **What:** Hardcoded French string instead of translation key. Arabic users saw "Ouvrir".
- **Fix:** Added `'open'` translation key to both `fr/portal.php` and `ar/portal.php`, replaced hardcoded string with `{{ __('portal.open') }}`.

### BUG-026 — (P3) IssueRefundIfApplicable uses queue-time for refund calculation
- **Status: fixed**
- **File:** `app/Listeners/IssueRefundIfApplicable.php:35`
- **What:** Refund percentage computed via `now()` at queue processing time, not at cancellation time. Delayed queue could produce wrong refund (e.g. 50% instead of 100%).
- **Fix:** Replaced `now()` with `$booking->cancelled_at` (already set by `BookingService::cancel()`).

## Commit

| SHA | Message |
|-----|---------|
| `7624cfb` | `fix(portal): prevent crash on reschedule when client has no phone` |
| `34e33a0` | `fix(portal): block download of unscanned documents` |
| `30602c5` | `fix(portal): delegate cancel eligibility to policy in booking detail` |
| `504d4e0` | `fix(portal): replace hardcoded French string with translation key` |
| `8fdcf48` | `fix(portal): use cancelled_at instead of now() for refund timing` |

## Tests added

- `test('new client created via magic link has no placeholder phone')` in `tests/Feature/Auth/MagicLinkTest.php`
- `test('reschedule does not crash when client has no phone')` in `tests/Feature/Portal/RescheduleTest.php`
- `test('client cannot download unscanned document')` in `tests/Feature/Portal/AuthorizationTest.php`
- `test('client can download clean scanned document')` in `tests/Feature/Portal/AuthorizationTest.php`
- `test('booking detail shows cancel button for eligible booking')` in `tests/Feature/Portal/BookingTest.php`
- `test('booking detail hides cancel button for ineligible booking')` in `tests/Feature/Portal/BookingTest.php`

## Test suite results

- `vendor/bin/pest` — **308/308 passed**, 651 assertions (was 299/299, 623 assertions)
- `vendor/bin/phpstan analyse` — not run (no PHP config changes made)

## Areas not fully covered this session

- No Dusk tests for portal flows
- No test for session lifetime middleware (2h idle / 24h hard cap)
- No test for magic link rate limiting

## Suggested next surface to audit

**Surface E — Admin Panel** (Filament resources, dashboard, settings) — untouched by previous sessions and has complex form interactions, actions, and authorization.
