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
- [ ] Header: logo wordmark, nav (Maître Bouhamidi, Services dropdown, Consultation, FAQ, Cabinet), language toggle, "Prendre rendez-vous" CTA
- [ ] Sticky header behavior on scroll (subtle shadow appears)
- [ ] Mobile menu: hamburger → side drawer with all nav items + lang toggle
- [ ] Footer: practice ID block (name, title, address, ICE, IF, Patente, phones), nav links, legal links, last-updated date on legal pages
- [ ] Skip-to-content link present and works
- [ ] All elements RTL-correct on `/ar/`

### Task 2: Home page

Sections per `FEATURES/public-site.md`:

Acceptance:
- [ ] Hero: portrait + heading + 2 CTAs (Consultation, Chatbot) + decorative ornament
- [ ] "Domaines d'intervention": 4 cards (Family / Real estate / Financial / Contracts) linking to service pages
- [ ] "Comment ça marche": 4-step process (Question → Choisir un plan → Réserver → Consultation)
- [ ] "Pourquoi consulter": values without superlatives (see `COMPLIANCE/notary-rules.md`)
- [ ] "À propos" teaser linking to About page
- [ ] FAQ teaser: 3 expandable items + link to FAQ
- [ ] Final CTA strip
- [ ] LCP element identified and preloaded (likely the portrait)
- [ ] Hero portrait: WebP, multiple `srcset` sizes
- [ ] All copy editable via Filament `Service` or settings-style table (no hardcoded marketing text)

### Task 3: About page

Acceptance:
- [ ] Sana's biography (in both languages, content from Sana)
- [ ] Credentials list
- [ ] Languages of service
- [ ] Photo
- [ ] Office overview
- [ ] CTA to consultation

### Task 4: Services overview + 4 detail pages

Acceptance:
- [ ] `/{locale}/services` index page lists 4 categories with icon, intro, link
- [ ] Each detail page: `/{locale}/services/{slug}` for `famille`, `immobilier`, `financier`, `contrats`
- [ ] Each detail page renders the fields from `services` table: title, intro, transactions, required documents, process, pricing note, CTA
- [ ] Filament `ServiceResource` allows full CMS editing in both languages with tabs
- [ ] Preview from Filament opens the live page in a new tab
- [ ] Default content seeded in both languages (placeholder pending Sana's polish)

### Task 5: Consultation plans page

Acceptance:
- [ ] `/{locale}/consultation` renders the 4 plans from `consultation_plans` table
- [ ] Cards show: name, tagline, price, duration, format, included features, CTA
- [ ] "Recommandé" badge on the standard plan (configurable)
- [ ] Comparison table below the cards
- [ ] VAT disclaimer per `COMPLIANCE/receipts-invoicing.md`
- [ ] Disclaimer distinguishing consultation fees vs. act fees per `COMPLIANCE/notary-rules.md`
- [ ] Filament `ConsultationPlanResource` lets Sana edit (only owner can change prices)

### Task 6: FAQ page

Acceptance:
- [ ] `/{locale}/faq` lists all published FAQs grouped by category
- [ ] Each item is a Livewire collapsible card; expand increments `view_count`
- [ ] Search bar with debounced live filtering (Meilisearch index over `faqs.question_translations` + `faqs.answer_translations` for current locale)
- [ ] Meilisearch installed on Hetzner box, indexed via Laravel Scout
- [ ] Filament `FaqResource` allows CRUD + publish toggle + bulk publish/unpublish (re-embed action is for phase 6)
- [ ] ~30 seed FAQs across 5 categories (Sana refines copy)

### Task 7: Office & contact page

Acceptance:
- [ ] Address with static OpenStreetMap tile (no live map JS)
- [ ] Office hours table from settings
- [ ] Phone numbers as `tel:` links
- [ ] Email as `mailto:`
- [ ] WhatsApp deep link
- [ ] Directions tips
- [ ] 1–2 office photos (from Sana)
- [ ] Contact form (next task)

### Task 8: Contact form

Acceptance:
- [ ] Livewire component with fields per `FEATURES/public-site.md`
- [ ] Server-side validation via `StoreContactMessageRequest`
- [ ] Honeypot field
- [ ] Cloudflare Turnstile widget (key in `.env`)
- [ ] Rate limit: 5/hr per IP
- [ ] On submit: `ContactMessage` created, `ContactMessageReceived` event fired
- [ ] Event listener emails Sana + assistant
- [ ] Success message displayed inline
- [ ] Form resets on success
- [ ] Email delivered via Resend (verified end-to-end)

### Task 9: Legal pages

Acceptance:
- [ ] `/{locale}/mentions-legales`, `/{locale}/politique-confidentialite`, `/{locale}/conditions-utilisation` rendered
- [ ] Content stored in a simple `legal_pages` table or as `Service`-like CMS entries (Markdown body translated)
- [ ] Filament resource to edit
- [ ] Privacy notice content matches `COMPLIANCE/loi-09-08.md` checklist
- [ ] Sana (or her counsel) reviewed each

### Task 10: SEO

Acceptance:
- [ ] Unique `<title>` and `<meta description>` per page (from `seo_meta` JSON on each CMS model, with defaults)
- [ ] Canonical link on every page
- [ ] hreflang tags pointing to both locales + `x-default`
- [ ] OG tags on every page with locale-appropriate default OG image
- [ ] `sitemap.xml` generated by `spatie/laravel-sitemap`, includes both locales
- [ ] `robots.txt` allows all but `/portal/`, `/admin/`, `/webhooks/`, `/livewire/`
- [ ] JSON-LD on home page: LegalService + LocalBusiness
- [ ] Service detail pages: Service schema
- [ ] Verified via Google Rich Results Test

### Task 11: Performance pass

Acceptance:
- [ ] All hero / above-fold images preloaded
- [ ] Lazy loading on below-fold images
- [ ] Fonts preloaded only for current locale's display weight
- [ ] Vite produces optimized JS bundle (initial ≤ 100 KB compressed)
- [ ] Total home page weight ≤ 500 KB on initial load
- [ ] Lighthouse mobile: Performance ≥ 90, Accessibility = 100, Best Practices ≥ 95, SEO ≥ 95 — verified on staging
- [ ] No render-blocking resources beyond critical CSS

### Task 12: Accessibility pass

Acceptance:
- [ ] axe-core runs in CI via Dusk on every public page — zero violations
- [ ] Keyboard nav: every interactive element reachable, visible focus ring
- [ ] Skip-to-content works
- [ ] Color contrast verified (tools + manual)
- [ ] Forms: labels, error association, autocomplete attributes, inputmode
- [ ] Images have alt text in the current locale
- [ ] Screen reader manual test on home + contact (NVDA or VoiceOver)
- [ ] `prefers-reduced-motion` respected

### Task 13: Cookie banner

Acceptance:
- [ ] Banner on first visit, dismissible
- [ ] Content per `COMPLIANCE/loi-09-08.md` cookie policy section
- [ ] Link to privacy notice
- [ ] Cookie set on dismiss; not re-shown
- [ ] No tracking cookies set anyway (Plausible cookie-free)

### Task 14: Booking CTA placeholders

Acceptance:
- [ ] Every "Prendre rendez-vous" link points to `/{locale}/book` (placeholder until phase 3)
- [ ] `/{locale}/book` shows a "Bientôt disponible" page with phone/WhatsApp fallback (no broken links)

### Task 15: Chatbot launcher placeholder

Acceptance:
- [ ] Floating button visible on every public page
- [ ] On click: opens a panel with "Bientôt disponible" message + phone/WhatsApp
- [ ] No actual Claude integration (that's phase 6)

### Task 16: Plausible Analytics

Acceptance:
- [ ] Script added to public + portal layouts (not admin)
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

- [ ] All 19 tasks complete
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
