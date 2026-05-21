# Feature: Client Portal

## Scope

A lightweight, magic-link-authenticated area for clients to:
- View their upcoming and past consultations
- Reschedule or cancel
- Upload or re-upload supporting documents
- View / download receipts
- Manage notification preferences
- Request account deletion

Not a full account / dashboard product. Just enough to reduce phone calls and respect the client's right to access their data.

## Design reference

Portal screens in `DESIGN/screens-index.md`: #16 (portal home), #17 (booking detail). Other portal routes (login, bookings list, receipts list, preferences, deletion modal) have no Stitch output — design from `DESIGN/design-system.md` using the conventions from screens #16–#17. Before writing views, read `DESIGN/README.md` and `DESIGN/design-system.md`.

## Auth flow

See `ARCHITECTURE/auth.md` → "Client authentication — magic link". Recap:

1. Visit `/portal/login`.
2. Enter email.
3. Magic link sent (15 min expiry, single use).
4. Click → logged in, session lives 2h idle / 24h hard cap.

## Routes

```php
Route::middleware(['locale'])->prefix('{locale}/portal')->group(function () {
    Route::middleware('guest:client')->group(function () {
        Route::get('/login', [MagicLinkController::class, 'show'])->name('portal.login');
        Route::post('/login', [MagicLinkController::class, 'request'])->name('portal.login.request');
        Route::get('/login/verify', [MagicLinkController::class, 'verify'])->name('portal.login.verify');
    });

    Route::middleware('auth:client')->group(function () {
        Route::get('/', PortalHomeController::class)->name('portal.home');
        Route::get('/bookings', BookingListController::class)->name('portal.bookings');
        Route::get('/bookings/{booking:reference}', BookingDetailController::class)->name('portal.bookings.show');
        Route::post('/bookings/{booking:reference}/cancel', BookingCancelController::class)->name('portal.bookings.cancel');
        Route::get('/bookings/{booking:reference}/reschedule', BookingRescheduleController::class)->name('portal.bookings.reschedule');
        Route::post('/bookings/{booking:reference}/documents', DocumentUploadController::class)->name('portal.documents.upload');
        Route::get('/bookings/{booking:reference}/documents/{document}', DocumentDownloadController::class)->name('portal.documents.download');
        Route::get('/receipts', ReceiptListController::class)->name('portal.receipts');
        Route::get('/receipts/{receipt:number}', ReceiptDownloadController::class)->name('portal.receipts.download');
        Route::get('/preferences', PreferencesController::class)->name('portal.preferences');
        Route::post('/preferences', PreferencesUpdateController::class)->name('portal.preferences.update');
        Route::post('/account/delete', DeleteAccountController::class)->name('portal.account.delete');
        Route::post('/logout', LogoutController::class)->name('portal.logout');
    });
});
```

All routes scoped to the `client` guard.

## Pages

### `/portal/login`

- Hero: "Espace client"
- Description: "Saisissez votre email pour recevoir un lien de connexion"
- Email field + submit
- Below: "Pour toute question, contactez-nous au [phone] / WhatsApp"
- Rate-limit indicator if exceeded ("Patientez quelques minutes avant de réessayer")

### `/portal` (logged-in home)

A simple dashboard:

```
┌────────────────────────────────────────────────────────┐
│ Bonjour, Karim                                         │
│                                                        │
│ Prochain rendez-vous                                   │
│ ┌────────────────────────────────────────────────────┐ │
│ │ Mardi 14 mars à 10:30                              │ │
│ │ Consultation standard en ligne · 30 min            │ │
│ │ Réf. SBA-ABC123                                    │ │
│ │ [Voir le détail] [Annuler] [Reporter]              │ │
│ └────────────────────────────────────────────────────┘ │
│                                                        │
│ Liens rapides                                          │
│ • Mes rendez-vous                                      │
│ • Mes reçus                                            │
│ • Mes préférences                                      │
│                                                        │
│ [Prendre un nouveau rendez-vous]                       │
└────────────────────────────────────────────────────────┘
```

If no upcoming booking: show "Vous n'avez pas de rendez-vous à venir" + CTA "Prendre rendez-vous".

### `/portal/bookings`

A list of all bookings, grouped by:
- À venir (upcoming, confirmed)
- En attente (pending payment)
- Passés (completed)
- Annulés (cancelled, no_show)

Each card: date, plan, format, reference, status badge, "Voir" action.

Paginated 20 per page (most clients will have far fewer).

### `/portal/bookings/{reference}`

Booking detail page:

- Booking summary (plan, date/time, format, reference, status)
- For online bookings: Jitsi join link (active 15 min before the slot)
- For in-office bookings: office address + map
- Documents section:
  - Already-uploaded documents (with download buttons)
  - Upload more documents (multi-file)
- Payment section:
  - Amount, method, status
  - Receipt download link (if paid card or marked-paid cash)
- Actions:
  - Reschedule (if eligible)
  - Cancel (if eligible)
  - Add documents
  - Contact us (WhatsApp + phone)
- Timeline / history (optional, soft-launch):
  - "Réservation créée — 12 mars 09:15"
  - "Confirmé — 12 mars 09:16"
  - "Rappel envoyé — 13 mars 10:30"

### Reschedule

`/portal/bookings/{reference}/reschedule`

- Reuses the slot picker component from the booking flow.
- Constraints:
  - Only same plan (changing plan = cancel + new booking)
  - At least 2h before original slot
  - Max 2 reschedules per 30 days
- On submit: BookingService::reschedule(booking, newSlot)
- Old booking cancelled with reason `rescheduled`
- New booking created in `confirmed` (payment carries over)
- New reference returned, redirect to its detail page

### Cancel

`/portal/bookings/{reference}/cancel`

- Modal (or full page on mobile):
  - Refund preview: "Selon notre politique, vous serez remboursé de [X] MAD"
  - Optional cancellation reason (free text)
  - Confirmation checkbox: "Je confirme l'annulation"
  - Buttons: Annuler la réservation / Garder ma réservation
- On confirm: BookingService::cancel(booking, reason, $client)
- Refund flow triggered if applicable (see `FEATURES/payment.md`)
- Redirect to portal home with success flash

### Document upload

Reuses the upload component from the booking flow. Same constraints (PDF/JPG/PNG, 10 MB max, max 5 files added at a time).

After successful upload, page refreshes showing the new documents.

### `/portal/receipts`

List of all receipts:
- Number, date, amount, booking reference, download button

### `/portal/preferences`

Form:
- Preferred language (Arabic / French)
- Notification channels (checkboxes): Email (cannot be disabled for critical), SMS, WhatsApp
- Preferred contact channel (radio)
- Delete account button (with double confirmation)

### Delete account

Two-step:
1. Click "Supprimer mon compte"
2. Modal: "Cette action est irréversible. Vos données personnelles seront anonymisées. Les enregistrements légaux (réservations, paiements, reçus) seront conservés conformément à la loi mais ne seront plus liés à vous."
3. Confirm by typing "SUPPRIMER" in a text field
4. Click "Je confirme"
5. Server:
   - Send a final confirmation email with a magic link
   - On click, performs the anonymization (see `ARCHITECTURE/auth.md`)
   - Client is logged out
   - Email confirms completion

This double-step gates against accidental clicks.

## Layout

Different layout from public site (`resources/views/layouts/portal.blade.php`):

- Lighter header with "Espace client" title and the client's first name
- "Déconnexion" link in header
- Same footer
- Chatbot widget still available

## State and Livewire

Most portal pages are Livewire components for instant feedback on actions (cancel, upload, preferences).

## Authorization

Every page strictly checks: `Auth::guard('client')->user()->id === $resource->client_id`.

Tests for every controller:
- Logged-in client A can see their own booking
- Logged-in client A CANNOT see client B's booking (403)
- Anonymous user redirected to /portal/login

## Notifications integration

The portal links to actions; the actions trigger the notification flow described in `ARCHITECTURE/notifications.md`. The portal itself doesn't send notifications directly.

## Mobile UX

Most clients will access the portal from a mobile email link. Design priorities:
- Single column.
- Large tap targets (≥ 44 px).
- Avoid scroll-trapped modals.
- Fast load (< 2s on 4G).

## Data shown

We expose to the client only:
- Their own profile fields (name, email, phone, locale, notification prefs)
- Their bookings (all statuses)
- Their payments and receipts
- Their documents
- Sana's internal notes are NEVER shown.
- Other clients' data is never shown.

## i18n

Both languages supported. Switcher in the layout.

## Edge cases

| Case | Behavior |
|---|---|
| Magic link expired | Redirect to `/portal/login` with flash message |
| Magic link already consumed | Redirect with friendly message |
| Session expires during action | Save state if possible; redirect to login with `?next=` |
| Client tries to cancel a booking in `completed` status | 403 (policy denies) |
| Client tries to download another client's document | 403 |
| Document not yet scanned | Disabled download button + tooltip explaining |
| No bookings yet | Empty state with CTA to book |

## Notifications-related actions from portal

- "Renvoyer le lien Jitsi" — re-sends the meeting URL for online bookings
- "Renvoyer le reçu" — re-sends the receipt PDF

## Acceptance criteria

- [ ] Magic-link login works (request, click, log in, regenerate session)
- [ ] Expired link shows correct error
- [ ] Used link shows correct error
- [ ] Rate limiting on magic-link request enforced
- [ ] All authorization tests pass (own data only)
- [ ] Cancel works for eligible bookings, denied for ineligible
- [ ] Reschedule works for eligible bookings
- [ ] Reschedule limit enforced (2 / 30 days)
- [ ] Document upload works (multi-file, validation, signed URL access)
- [ ] Receipt download works via signed URL
- [ ] Preferences saved and applied to subsequent notifications
- [ ] Account deletion anonymizes Client properly
- [ ] Account deletion preserves legal records (bookings, payments, receipts)
- [ ] Logout works
- [ ] Idle session timeout enforced (2h)
- [ ] Hard session cap enforced (24h)
- [ ] Both AR and FR working
- [ ] Mobile-friendly (tested on iOS Safari, Android Chrome)
- [ ] Accessibility 100 on Lighthouse
- [ ] No PII in logs

## Out of scope (v1)

- Real-time updates (WebSocket / Echo)
- In-portal chat with Sana
- Document e-signing
- Multi-user accounts (e.g. for businesses with multiple authorized signatories)
- Booking on behalf of others
