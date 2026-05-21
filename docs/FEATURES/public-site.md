# Feature: Public Site

## Scope

All publicly-accessible pages that any visitor (logged in or not) can browse without authentication, excluding the chatbot (separate feature).

## Design reference

Stitch outputs and Blade paths for every screen in this feature: see the **Public pages** section of `DESIGN/screens-index.md` (screens #1–#14). Before writing any view, also read `DESIGN/README.md` and `DESIGN/design-system.md`.

## Pages

| Page | Path | Purpose |
|---|---|---|
| Home | `/{locale}/` | First impression, anchor trust, route to services or booking |
| About | `/{locale}/maitre-bouhamidi` | Biography, credentials, professional path |
| Services overview | `/{locale}/services` | Catalog of practice areas |
| Service detail × 4 | `/{locale}/services/{slug}` | Per-category page: family / real-estate / financial / contracts |
| Consultation plans | `/{locale}/consultation` | Plan comparison, gateway to booking |
| FAQ | `/{locale}/faq` | Searchable Q&A list |
| Office & contact | `/{locale}/cabinet` | Address, map, hours, contact form |
| Legal notice | `/{locale}/mentions-legales` | Required disclosures |
| Privacy notice | `/{locale}/politique-confidentialite` | Loi 09-08 compliance |
| Terms of use | `/{locale}/conditions-utilisation` | Site terms, booking T&Cs |

## Stack

- Blade views with Tailwind.
- Livewire islands where interactivity is needed (FAQ search, contact form, plan selector).
- No client-side router.
- `wire:navigate` for in-site link transitions (SPA-like, opt-in per `<a>`).

## Routing

```php
Route::middleware(['locale'])->prefix('{locale}')->group(function () {
    Route::get('/', HomeController::class)->name('home');
    Route::get('/maitre-bouhamidi', AboutController::class)->name('about');
    Route::get('/services', ServicesIndexController::class)->name('services.index');
    Route::get('/services/{slug}', ServiceDetailController::class)->name('services.show');
    Route::get('/consultation', ConsultationController::class)->name('consultation');
    Route::get('/faq', FaqController::class)->name('faq');
    Route::get('/cabinet', OfficeController::class)->name('office');
    Route::get('/mentions-legales', LegalController::class)->name('legal');
    Route::get('/politique-confidentialite', PrivacyController::class)->name('privacy');
    Route::get('/conditions-utilisation', TermsController::class)->name('terms');
});

Route::get('/', LocaleRedirectController::class);  // routes to /ar or /fr based on detection
```

`LocaleRedirectController` applies the language detection priority from `STANDARDS/accessibility-i18n.md`.

## Layout

A single layout (`resources/views/layouts/public.blade.php`):

- Header: language toggle, navigation, "Prendre rendez-vous" CTA
- Main content slot
- Footer: practice ID (name, title, address, ICE/IF/Patente), navigation, legal links, social (none in v1)
- Chatbot widget (loaded on every page)

Navigation items:
- Maître Bouhamidi
- Services (dropdown: 4 categories)
- Consultation
- FAQ
- Cabinet
- (CTA button) Prendre rendez-vous

## Home page sections

1. Hero: portrait + title + 2 primary CTAs (Consultation / Chatbot) + Arabic-French calligraphic accent
2. "Domaines d'intervention" — 4 service category cards
3. "Comment ça marche" — 4-step process
4. "Pourquoi consulter" — credibility / values (no superlatives — see `COMPLIANCE/notary-rules.md`)
5. "À propos" teaser — link to About page
6. FAQ teaser — 3 expandable items + link
7. Final CTA — "Prendre rendez-vous"

Hero contains the chatbot launcher (separate widget; see `FEATURES/chatbot.md`).

## Service detail page

Per category. Content stored in `services` table (`services_translations`):
- Title
- Intro paragraph
- Transactions covered (bulleted list)
- Required documents (bulleted list)
- Process / "what to expect"
- Pricing note (consultation fee scale; never act fees — see `COMPLIANCE/notary-rules.md`)
- CTA: "Prendre rendez-vous pour cette matière"

Content editable via Filament. Each section is a JSON field with FR/AR translations.

## Consultation plans page

Shows the 4 plans:

| Plan | Format | Duration | Price |
|---|---|---|---|
| Orientation gratuite | Online (chat / brief call) | 10 min | 0 MAD |
| Consultation standard | Online (video) | 30 min | 250 MAD |
| Consultation au cabinet | In-office | 60 min | 400 MAD |
| Consultation approfondie | In-office | 90 min | 800 MAD |

Each card:
- Plan name + tagline
- Price prominently displayed (with VAT note per `COMPLIANCE/receipts-invoicing.md`)
- Duration
- Format icon (online / in-person)
- "What's included" bullet list
- "Réserver" CTA → `/{locale}/book?plan=<slug>`
- Comparison view available below the cards

Recommended plan visually elevated (subtle brass border, "Recommandé" pill).

Plans are loaded from the DB. Sana can edit copy and toggle active/inactive via Filament. **Price changes require an audit-log entry and only the `owner` role can change them.**

## FAQ page

- All published FAQs grouped by category.
- Each FAQ is a Livewire-collapsible card (`<x-faq-item>`).
- Search box at the top — Livewire-debounced, filters in real-time.
- Search backed by Meilisearch index over `faqs.question_translations` + `faqs.answer_translations` (locale-aware).
- On expand: tracks a view (increments `faqs.view_count`) for usage analytics.

## Office & contact

- Address with embedded static map (OpenStreetMap tile, no live map JS — privacy + perf).
- Office hours table.
- Phone numbers (clickable `tel:` links).
- Email (clickable `mailto:`).
- WhatsApp button (deep link).
- Contact form (see "Contact form" below).
- Directions tips: "near the Tribunal de Première Instance"; bus / parking notes.
- One or two office photos (private practice taste — discuss with Sana).

### Contact form

Livewire component with fields:
- Full name (required)
- Email (required)
- Subject (select: family / real_estate / financial / contracts / other) (required)
- Message (textarea, 20–2000 chars) (required)
- "Je préfère être recontacté par..." (radio: téléphone / email / WhatsApp) (required)
- Honeypot field (hidden, must be empty)
- Cloudflare Turnstile (or hCaptcha) widget (one provider, configurable)

On submit:
- Server-side validation via `StoreContactMessageRequest`.
- Spam checks: honeypot, captcha, rate limit (5/hr per IP).
- Create `ContactMessage` row.
- Fire `ContactMessageReceived` event → emails Sana + assistant.
- Show inline success message.
- Reset the form.

## Legal pages

- Mentions légales: practice info, hosting info, contact info.
- Privacy: per `COMPLIANCE/loi-09-08.md`.
- Terms: site usage terms, booking T&Cs (cancellation policy, payment, fees).

All editable as Markdown stored in `services`-like tables (`legal_pages` or similar simple key/value store of markdown). Versioned; previous versions kept for legal traceability.

## SEO

### Per-page

- Unique `<title>`, `<meta description>` from `seo_meta_translations` JSON on each model.
- Canonical link.
- `hreflang` alternate links for both languages.
- Open Graph + Twitter tags (image: `/public/og/{locale}-default.png` overridable per page).

### Site-wide

- `sitemap.xml` generated automatically via `spatie/laravel-sitemap`, includes both locales.
- `robots.txt` allows all but `/portal/`, `/admin/`, `/webhooks/`, `/livewire/`.
- Structured data (JSON-LD) on the home page:
  - `LegalService` schema (name, image, address, telephone, openingHours, areaServed)
  - `LocalBusiness` schema
- Service detail pages: `Service` schema.

### Local SEO

- Practice listed (manually, by Sana) on Google My Business.
- Same NAP (Name, Address, Phone) everywhere.

## Acceptance criteria

- [ ] Every page exists at both `/ar/` and `/fr/` paths
- [ ] Language switcher preserves current page
- [ ] All copy editable via Filament (no hardcoded marketing text)
- [ ] Lighthouse Performance ≥ 90 mobile, ≥ 95 desktop
- [ ] Lighthouse Accessibility = 100
- [ ] Lighthouse SEO ≥ 95
- [ ] No console errors in Chrome / Safari / Firefox
- [ ] All forms validated client + server side
- [ ] Contact form captcha verified
- [ ] FAQ search returns results in <100ms locally
- [ ] hreflang and canonical correct on every page (spot-checked via Screaming Frog or similar)
- [ ] sitemap.xml accessible and valid
- [ ] Structured data validates on Google Rich Results Test
- [ ] All clickable contact methods work on mobile

## Out of scope (defer)

- Blog
- Newsletter / email capture
- Live status indicator (is Sana available now?)
- Social media share buttons
- Multi-office support

## Risks

- **Copy review** is the longest-tail blocker — Sana needs to sign off on every visible string and the chatbot system prompt. Start review early in the public-site phase.
- **Translation accuracy** — Arabic translation reviewed by native speaker before launch.
- **Performance budget** — keep the home page under 500 KB to maintain mobile Lighthouse score. Images optimized, fonts subset.
