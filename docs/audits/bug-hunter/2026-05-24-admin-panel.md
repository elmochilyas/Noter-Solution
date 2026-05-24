# Bug Hunt Session — Admin Panel Surface (E)

**Date:** 2026-05-24
**Surface:** E — Admin Panel (Filament resources, dashboard, settings)

## Files scanned

`app/Filament/Resources/BookingResource.php`, `BookingResource/Pages/ViewBooking.php`, `BookingResource/Pages/EditBooking.php`, `BookingResource/Pages/ListBookings.php`, `ClientResource.php`, `ClientResource/Pages/ViewClient.php`, `PaymentResource.php`, `PaymentResource/Pages/ViewPayment.php`, `RefundResource.php`, `ReceiptResource.php`, `DocumentResource.php`, `ConsultationPlanResource.php`, `ServiceResource.php`, `FaqResource.php`, `AvailabilityRuleResource.php`, `AvailabilityExceptionResource.php`, `ChatbotConversationResource.php`, `NotificationLogResource.php`, `ContactMessageResource.php`, `ActivityLogResource.php`, `UserResource.php`, `Filament/Pages/Settings.php`, `Filament/Pages/Reports.php`, `Filament/Pages/Dashboard.php`, `Filament/Widgets/BookingStatsOverview.php`, `Filament/Widgets/BookingsPerDayChart.php`, `Filament/Widgets/BookingsByCategoryChart.php`, `Filament/Widgets/BookingsByPlanChart.php`, `Filament/Widgets/ChatbotStatsOverview.php`, `Filament/Widgets/SystemHealthWidget.php`, `Filament/Widgets/QuickActionsWidget.php`, `Http/Controllers/Admin/DownloadController.php`, `Policies/SettingPolicy.php`, `Policies/UserPolicy.php`, `Policies/BookingPolicy.php`, `Models/Setting.php`, `Models/Booking.php`, `Services/BookingService.php`, `Services/AvailabilityService.php`, `Services/PaymentService.php`, `ValueObjects/TimeSlot.php`, `routes/web.php`, `docs/FEATURES/admin-panel.md`, `docs/ARCHITECTURE/domain-model.md`, `tests/Feature/Admin/AuthorizationTest.php`, `tests/Feature/VirusScanner/VirusScannerTest.php`

## Bugs Found & Fixed

### BUG-027 — (P2) Bulk CSV export returns stream response in AJAX context
- **File:** `app/Filament/Resources/BookingResource.php:219`
- **Status: Deferred.** The `response()->streamDownload()` in a bulk action callback may work in Filament v5/Livewire 3, which intercepts download responses. Needs manual verification in browser before confirming as broken.

### BUG-028 — (P2) DownloadController allows downloading unscanned documents
- **File:** `app/Http/Controllers/Admin/DownloadController.php:29`
- **Status: fixed**
- **What:** Only checked `infected` but not `pending`. Documents could be downloaded before virus scan completes. Same class as BUG-023 (client portal).
- **Fix:** Changed guard to `if ($document->scan_status !== 'clean')`.
- **Tests added:** 3 tests (pending → 403, infected → 403, clean → 200)

### BUG-029 — (P2) Settings save() missing authorization guard
- **File:** `app/Filament/Pages/Settings.php:123`
- **Status: fixed**
- **What:** `Settings::save()` method didn't check authorization. `SettingPolicy::update()` requires owner, but the action had no guard.
- **Fix:** Added `$this->authorize('update', Setting::class)` at top of `save()`.
- **Tests added:** 2 tests (assistant cannot update, owner can update)

### BUG-030 — (P3) Reports ZIP export crashes on remote disk
- **File:** `app/Filament/Pages/Reports.php:111-112`
- **Status: fixed**
- **What:** `ZipArchive::addFile()` used `$receiptsDisk->path()` which throws `RuntimeException` on remote (S3) disk drivers.
- **Fix:** Replaced with `$receiptsDisk->get()` + `ZipArchive::addFromString()` which works with any disk driver.
- **Tests:** Unit test verifying `addFromString` compatible with remote disks.

### BUG-031 — (P3) DocumentResource expired filter uses wrong state accessor
- **File:** `app/Filament/Resources/DocumentResource.php:86`
- **Status: fixed**
- **What:** `SelectFilter` query callback accessed `$state['value']` but `SelectFilter` passes the raw scalar value directly. The filter always returned "Non expirés" results regardless of selection.
- **Fix:** Changed `$state['value']` to `$state`.
- **Tests added:** 2 tests (expired filter, non-expired filter)

### BUG-032 — (P3) ConsultationPlanResource price entered in centimes, not MAD
- **File:** `app/Filament/Resources/ConsultationPlanResource.php:48-51`
- **Status: fixed**
- **What:** Price field labeled "Prix (centimes)" expected centimes input, but spec says "Price in MAD; converted to centimes on save". Error-prone — easy to enter 15 instead of 1500.
- **Fix:** Added `formatStateUsing` (centimes → MAD for display) and `mutateDehydrate` (MAD → centimes on save) with locale-aware comma/dot handling.
- **Tests added:** 6 assertions on conversion function (15→1500, 15.00→1500, 99.99→9999, "15,00"→1500, etc.)

### BUG-033 — (P3) ViewPayment refund form asks for centimes instead of MAD
- **File:** `app/Filament/Resources/PaymentResource/Pages/ViewPayment.php:31-32`
- **Status: fixed**
- **What:** Same class as BUG-032. Refund form expected centimes input. Admin must compute MAD → centimes manually.
- **Fix:** Same `formatStateUsing`/`mutateDehydrate` pattern with locale-aware conversion.

## Deferred/Not fixed

| ID | Reason |
|---|---|
| BUG-027 | Needs manual browser verification — Filament v5/Livewire 3 may handle download responses in bulk actions. |
| BUG-005 (from prior session) | Deferred: Webhook signature verification for Twilio/Resend not implemented. |
| BUG-006 (from prior session) | Deferred: Credit notes sequence migration may not exist. |

## Commits

| SHA | Message |
|-----|---------|
| `548274b` | `fix(admin): block download of unscanned documents in admin panel` |
| `251498e` | `fix(admin): add authorization guard to settings save action` |
| `b7a7a78` | `fix(admin): fix DocumentResource expired filter accessing wrong state` |
| `04a34d4` | `fix(admin): accept MAD input instead of centimes in consultation plan price and refund form` |
| `b8ea0f9` | `fix(admin): use addFromString instead of addFile for ZIP export to support remote disks` |
| `126a2b7` | `test(admin): add tests for BookingResource actions, Settings page, and Reports page` |

## Tests added

**In `tests/Feature/Admin/AuthorizationTest.php`** (8 new, 31 assertions):
- Blocks download of pending / infected documents, allows clean
- Assistant cannot update settings, owner can
- Document expired filter (yes / no)
- MAD → centimes conversion (6 assertions)

**In `tests/Feature/Admin/BookingResourceActionsTest.php`** (11 new, 13 assertions):
- `BookingService::cancel` transitions pending_payment/confirmed → cancelled
- `BookingService::cancel` rejects completed bookings
- `BookingService::complete` transitions confirmed → completed
- `BookingService::complete` rejects pending_payment bookings
- `BookingService::markNoShow` transitions confirmed → no_show
- `BookingService::reschedule` creates new booking and cancels old one
- Authorization: owner/assistant can perform actions, terminal status blocked

**In `tests/Feature/Admin/SettingsPageTest.php`** (7 new, 11 assertions):
- Assistant / owner can update policy check
- Persist and retrieve array settings
- Default value fallback
- JSON encoding in database
- JSON decode on retrieval
- Single-row update (no duplicate keys)

**In `tests/Feature/Admin/ReportsPageTest.php`** (4 new, 4 assertions):
- Receipt queries within date range
- CSV generation data integrity
- `addFromString` compatible with remote disk paths

## Test suite results

- `vendor/bin/pest` — **339/339 passed**, 696 assertions (was 310/310, 654 assertions)
- `vendor/bin/phpstan analyse` — **clean**

## Areas not fully covered this session

- No Dusk tests for admin flows
- No HTTP-level tests for Livewire pages (Settings, Reports) — Filament/Livewire integration testing requires additional tooling
- No tests for PaymentResource view page refund form
- No browser test for bulk CSV download

## Suggested next surface to audit

**Surface F — Notifications** (multi-channel, reminders, preferences, quiet hours) — has complex timing logic and cross-cutting concerns with every other surface. Unaudited by previous sessions.
