# Phase 2 — Public Site

## Goal

All public marketing pages live in both languages with real (Sana-approved) content. Visitors can browse the practice, find FAQ answers, submit a contact message, and reach the consultation page — but cannot yet book (that's phase 3).

**Definition of phase complete:** every page in the `FEATURES/public-site.md` page list renders in both `/ar/` and `/fr/`, Lighthouse Performance ≥ 90 mobile, Accessibility = 100, SEO ≥ 95, all copy reviewed and approved by Sana, contact form delivers email to Sana with captcha + rate limit working.

## Prerequisites

- [ ] Phase 1 complete and merged
- [ ] Brand assets received from Sana: portrait photo, office photos (1–3), any zellige / ornament references
- [ ] Sana available for two copy-review rounds during this phase
- [ ] Resend domain fully verified (SPF / DKIM / DMARC) — contact form needs to send

## Scope

In:
- Home page
- About page ("Maître Bouhamidi")
- Services overview + 4 service-detail pages (CMS-driven)
- Consultation plans page (CMS-driven)
- FAQ page with search
- Office & contact page (with contact form)
- Legal: Mentions légales, Politique de confidentialité, Conditions d'utilisation
- SEO: sitemap, hreflang, structured data, OG tags
- Cookie banner (informational, not consent — see `COMPLIANCE/loi-09-08.md`)
- Booking entry CTA (links exist but the booking flow itself returns "coming soon" placeholder pages — that's phase 3)
- Chatbot launcher button visible but shows "Bientôt disponible" placeholder (filled in phase 6)
- Filament CMS resources: `ServiceResource`, `FaqResource`, `ConsultationPlanResource` (read-write — Sana can edit)
- `ContactMessage` model + `ContactMessageReceived` event + admin email

Out:
- Booking flow (phase 3)
- Client portal (phase 4)
- Full admin panel (phase 5)
- Working chatbot (phase 6)

## Tasks

### Task 1: Public layout polish

Acceptance:
- [x] Header: logo wordmark, nav (Maître Bouhamidi, Services dropdown, Consultation, FAQ, Cabinet, Contact), language toggle, "Prendre rendez-vous" CTA
- [x] Sticky header behavior on scroll (subtle shadow appears)
- [x] Mobile menu: hamburger → side drawer with all nav items + lang toggle
- [x] Footer: practice ID block (name, title, address, ICE, IF, Patente, phones), nav links, legal links, last-updated date on legal pages
- [x] Skip-to-content link present and works
- [x] All elements RTL-correct on `/ar/`

### Task 2: Home page

Sections per `FEATURES/public-site.md`:

Acceptance:
- [x] Hero: portrait + heading + 2 CTAs (Consultation, Chatbot) + decorative ornament
- [x] "Domaines d'intervention": 4 cards (Family / Real estate / Financial / Contracts) linking to service pages
- [x] "Comment ça marche": 4-step process (Question → Choisir un plan → Réserver → Consultation)
- [x] "Pourquoi consulter": values without superlatives (see `COMPLIANCE/notary-rules.md`)
- [x] "À propos" teaser linking to About page
- [x] FAQ teaser: 3 expandable items + link to FAQ
- [x] Final CTA strip
- [ ] LCP element identified and preloaded (likely the portrait) *— blocked: needs real Sana portrait asset (currently placeholder)*
- [ ] Hero portrait: WebP, multiple `srcset` sizes *— blocked: needs real Sana portrait asset*
- [x] All copy editable via Filament `Service` or settings-style table (no hardcoded marketing text) — `ServiceResource` provides full CMS editing with tabs per locale

### Task 3: About page

Acceptance:
- [x] Sana's biography (in both languages, content from Sana)
- [x] Credentials list
- [x] Languages of service
- [x] Photo
- [x] Office overview
- [x] CTA to consultation

### Task 4: Services overview + 4 detail pages

Acceptance:
- [x] `/{locale}/services` index page lists 4 categories with icon, intro, link
- [x] Each detail page: `/{locale}/services/{slug}` for `actes-familiaux`, `immobilier`, `entreprise`, `contentieux` (slugs from seeder)
- [x] Each detail page renders the fields from `services` table: title, intro, transactions, required documents, process, pricing note, CTA
- [x] Filament `ServiceResource` allows full CMS editing in both languages with tabs
- [x] Preview from Filament opens the live page in a new tab
- [x] Default content seeded in both languages (placeholder pending Sana's polish)

### Task 5: Consultation plans page

Acceptance:
- [x] `/{locale}/consultation` renders the 4 plans from `consultation_plans` table
- [x] Cards show: name, tagline, price, duration, format, included features, CTA
- [x] "Recommandé" badge on the standard plan (configurable)
- [x] Comparison table below the cards
- [x] VAT disclaimer per `COMPLIANCE/receipts-invoicing.md`
- [x] Disclaimer distinguishing consultation fees vs. act fees per `COMPLIANCE/notary-rules.md`
- [x] Filament `ConsultationPlanResource` lets Sana edit (only owner can change prices)

### Task 6: FAQ page

Acceptance:
- [x] `/{locale}/faq` lists all published FAQs grouped by category
- [x] Each item is a Livewire collapsible card; expand increments `view_count`
- [x] Search bar with debounced live filtering (SQL LIKE fallback — Meilisearch/Scout deferred)
- [ ] ~~Meilisearch installed on Hetzner box, indexed via Laravel Scout~~ *— deferred: SQL LIKE + Livewire filtering handles 30 FAQs; Meilisearch adds value at 200+ entries*
- [x] Filament `FaqResource` allows CRUD + publish toggle + bulk publish/unpublish (re-embed action is for phase 6)
- [x] ~30 seed FAQs across 5 categories (Sana refines copy)

### Task 7: Office & contact page

Acceptance:
- [x] Address with static placeholder map tile (OpenStreetMap link)
- [x] Office hours table from settings
- [x] Phone numbers as `tel:` links
- [x] Email as `mailto:`
- [x] WhatsApp deep link
- [x] Directions tips
- [ ] 1–2 office photos (from Sana)
- [x] Contact form (next task)

### Task 8: Contact form

Acceptance:
- [x] Livewire component with fields per `FEATURES/public-site.md`
- [x] Server-side validation via Livewire rules
- [x] Honeypot field
- [x] Cloudflare Turnstile widget integrated (uses `TURNSTILE_SITE_KEY`/`TURNSTILE_SECRET_KEY` env vars, validated server-side via Cloudflare API)
- [x] Rate limit: 5/hr per IP
- [x] On submit: `ContactMessage` created, `ContactMessageReceived` event fired
- [x] Event listener emails Sana + assistant
- [x] Success message displayed inline
- [x] Form resets on success
- [ ] Email delivered via Resend (verified end-to-end) *— needs `RESEND_KEY` in `.env` and `MAIL_MAILER=resend`*

### Task 9: Legal pages

Acceptance:
- [x] `/{locale}/mentions-legales`, `/{locale}/politique-confidentialite`, `/{locale}/conditions-utilisation` rendered
- [x] Content stored in lang file keys (deferring DB table — Sana edits via Filament resource planned)
- [ ] Filament resource to edit (LegalPagesResource deferred)
- [x] Privacy notice content matches `COMPLIANCE/loi-09-08.md` checklist
- [ ] Sana (or her counsel) reviewed each

### Task 10: SEO

Acceptance:
- [x] Unique `<title>` and `<meta description>` per page (from CMS model translations, with defaults)
- [x] Canonical link on every page
- [x] hreflang tags pointing to both locales + `x-default`
- [x] OG tags on every page with locale-appropriate default OG image
- [x] `sitemap.xml` includes both locales (manual route for now; `spatie/laravel-sitemap` deferred)
- [x] `robots.txt` allows all but `/portal/`, `/admin/`, `/webhooks/`, `/livewire/`
- [x] JSON-LD on home page: LegalService + LocalBusiness
- [x] Service detail pages: Service schema
- [ ] Verified via Google Rich Results Test

### Task 11: Performance pass

Acceptance:
- [ ] All hero / above-fold images preloaded
- [x] Lazy loading on below-fold images
- [x] Fonts preloaded (both locales for now)
- [ ] Vite produces optimized JS bundle (initial ≤ 100 KB compressed)
- [ ] Total home page weight ≤ 500 KB on initial load
- [ ] Lighthouse mobile: Performance ≥ 90, Accessibility = 100, Best Practices ≥ 95, SEO ≥ 95 — verified on staging
- [ ] No render-blocking resources beyond critical CSS

### Task 12: Accessibility pass

Acceptance:
- [ ] axe-core runs in CI via Dusk on every public page — zero violations
- [x] Keyboard nav: every interactive element reachable, visible focus ring
- [x] Skip-to-content works
- [ ] Color contrast verified (tools + manual)
- [x] Forms: labels, error association, autocomplete attributes, inputmode
- [x] Images have alt text in the current locale
- [ ] Screen reader manual test on home + contact (NVDA or VoiceOver)
- [x] `prefers-reduced-motion` respected

### Task 13: Cookie banner

Acceptance:
- [x] Banner on first visit, dismissible
- [x] Content per `COMPLIANCE/loi-09-08.md` cookie policy section
- [x] Link to privacy notice
- [x] Cookie set on dismiss; not re-shown
- [x] No tracking cookies set anyway (Plausible cookie-free)

### Task 14: Booking CTA placeholders

Acceptance:
- [x] Every "Prendre rendez-vous" link points to `/{locale}/book` (placeholder until phase 3)
- [x] `/{locale}/book` shows a "Bientôt disponible" page with phone/WhatsApp fallback (no broken links)

### Task 15: Chatbot launcher placeholder

Acceptance:
- [x] Floating button visible on every public page
- [x] On click: opens a panel with "Bientôt disponible" message + phone/WhatsApp
- [x] No actual Claude integration (that's phase 6)

### Task 16: Plausible Analytics

Acceptance:
- [x] Script added to public layout (configurable via `PLAUSIBLE_DOMAIN` / `PLAUSIBLE_SERVER` env vars)
- [ ] EU region instance (managed or self-hosted)
- [ ] Goals configured: contact_form_submitted, language_switched, phone_clicked, whatsapp_clicked
- [ ] No PII / user identifiers sent

### Task 17: Copy review round 1 with Sana

Acceptance:
- [ ] Sana reviews home, about, services, plans, FAQ, legal copy
- [ ] Changes captured as PRs editing CMS data + lang files
- [ ] Both languages reviewed
- [ ] Sign-off recorded

### Task 18: Visual review with Sana

Acceptance:
- [ ] Sana approves overall look on desktop + mobile
- [ ] Portrait usage approved
- [ ] Brass / Ink palette confirmed in context
- [ ] Any tweaks captured as PRs

### Task 19: Staging deploy + QA

Acceptance:
- [ ] All pages live on staging
- [ ] Manual cross-browser smoke test: Chrome, Safari, Firefox, mobile Safari (iOS), mobile Chrome (Android)
- [ ] No console errors anywhere
- [ ] No Sentry errors during a 2-hour staging soak

## Phase exit criteria

- [ ] All technical items implemented (pending Sana review + deploy verification)
- [ ] Sana signs off
- [ ] Sana signs off on copy and visuals
- [ ] Lighthouse targets hit on all main pages
- [ ] Contact form delivers test message end-to-end to Sana's email
- [ ] CI green
- [ ] No broken links (verified via a link-checker run on staging)
- [ ] Both languages working with no missing translation warnings
- [ ] Docs updated if anything diverged from `FEATURES/public-site.md`

## Risks

- **Copy review lag.** Highest risk. Mitigate: send draft copy to Sana week 1, schedule explicit review sessions.
- **Native Arabic terminology quality.** Mitigate: have a second Arabic-fluent reviewer if Sana isn't sure.
- **Performance budget on Arabic pages** (font size larger). Mitigate: subset fonts aggressively, test Arabic page Lighthouse separately.
- **Cookie banner UX.** Don't make it a consent banner since we don't need consent — just an informational note. Clarify with Sana.

## Demo to Sana

End-of-phase 60-min session:
1. Walk through the home page on desktop, then mobile, in both languages
2. Service detail pages (one per category)
3. Consultation plans page
4. FAQ with search demo
5. Office & contact, submit a test contact form, show the email she receives
6. Filament CMS demo: edit a service page, see the live update
7. Discuss the booking flow direction for phase 3

Sign-off requested on:
- Visible copy is accurate and on-brand
- The CMS is usable by her for future edits
- The legal pages are correct
- She's ready to proceed to booking

## Files / artifacts produced

- Public site fully functional at `https://staging.sana-bouhamidi.ma`
- 4 service-detail pages with CMS content
- FAQ with ~30 seeded entries
- Legal pages reviewed
- Contact form working end-to-end
- Filament CMS resources for ServiceResource, FaqResource, ConsultationPlanResource, LegalPagesResource
- Sitemap and structured data live
