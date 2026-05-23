# Phase 7 — Polish & Launch

## Goal

Cross all the t's and dot all the i's. Site is ready for soft launch in production: accessibility audited, performance tuned, security reviewed, native-speaker reviewed, compliance items filed, virus scanner real (not the v1 `null` driver), backups drilled, runbooks in place.

**Definition of phase complete:** the site is live at `https://sana-bouhamidi.ma` with TLS, Sana and the assistant are operating, all compliance items are filed or in flight, monitoring is green, and a real client has gone through the flow end-to-end (Sana herself first).

## Prerequisites

- [ ] Phase 6 complete and merged
- [ ] Sana ready for final review sessions (compliance + visual + copy)
- [ ] CNDP declaration drafted (per `COMPLIANCE/loi-09-08.md`) — Sana / her counsel ready to file
- [ ] Stripe production keys received (account fully activated for Morocco)
- [ ] Twilio production sender ID + WhatsApp templates approved
- [ ] Resend production domain warmed
- [x] Cerebras free tier provisioned (no credit card needed — 1M tokens/day)
- [ ] Backup destination configured (Backblaze B2 or Wasabi bucket for offsite dumps)

## Scope

In:
- Accessibility audit (axe + manual screen-reader + keyboard)
- Performance pass (Lighthouse all pages, query budgets, image audit)
- Security review (OWASP checklist, headers, CSP nonces, secret rotation)
- Native Arabic speaker review of all displayed copy
- Real virus scanner integration (replace `null` driver with ClamAV or VirusTotal)
- Backup drill (restore from PITR to a clean Supabase project)
- Compliance pre-launch checklist (Loi 09-08, notary rules, receipts)
- CNDP declaration filed
- Production environment provisioning (Hetzner CCX13 + Forge prod site)
- DNS cutover plan
- TLS + HSTS + security headers verified on prod
- Email deliverability checks (SPF, DKIM, DMARC, warmup, inbox tests)
- Soft-launch monitoring window (48h)
- Lessons-learned log started
- Cheat sheet PDFs for Sana and the assistant
- Public + portal + admin smoke checks
- Documentation drift review (every doc rechecked against built reality)

Out:
- Marketing announcement (post-launch, not part of this phase)
- v2 / v1.x features (CMI gateway, etc.)
- Anything requiring lead times that haven't been respected (no last-minute integrations)

## Tasks

### Task 1: Accessibility audit

Acceptance:
- [ ] axe-core run on every public + portal page on staging via Dusk — zero violations
- [ ] Manual keyboard-only walk of: booking flow, portal magic-link login + cancellation, admin login + booking management
- [ ] Manual screen-reader walk on home + booking flow (NVDA on Windows or VoiceOver on macOS)
- [ ] Color contrast spot-checked on every distinct UI surface
- [ ] All identified issues fixed; re-run shows zero violations
- [ ] Lighthouse Accessibility = 100 on every audited page

### Task 2: Performance pass

Acceptance:
- [ ] Lighthouse mobile on every key public page (home, services, plans, FAQ, contact, booking, portal home): Performance ≥ 90
- [ ] Lighthouse desktop ≥ 95 on the same pages
- [ ] LCP ≤ 2.5s on home, INP ≤ 200ms, CLS ≤ 0.1
- [ ] Initial JS bundle ≤ 100 KB compressed (verified via Vite output inspection)
- [ ] Total home page weight ≤ 500 KB initial load
- [ ] All images: correct format (WebP / AVIF), correct dimensions, `loading="lazy"` below fold
- [ ] Fonts subset and preloaded for current locale only
- [ ] Query count audited on top 5 routes via Pulse — under budget
- [ ] N+1 verified absent on booking calendar, booking detail, admin booking list, admin client detail
- [ ] Slow-query log empty for 24h on staging
- [ ] Redis configured with appropriate maxmemory + eviction policy

### Task 3: Security review

Acceptance:
- [ ] OWASP Top 10 checklist walked per `STANDARDS/security.md` — every item verified
- [x] CSP with per-request nonces on every page; `'unsafe-inline'` replaced with `'nonce-{NONCE}'` on `script-src` via `ContentSecurityPolicy` middleware
- [x] HSTS (`max-age=31536000; includeSubDomains; preload`), X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy set via middleware
- [ ] All security headers present (verified via securityheaders.com on production) *— needs production URL*
- [x] Rate limit tests written for Fortify login + webhook throttle (10 tests in `tests/Feature/RateLimitTest.php`)
- [ ] Rate limits tested manually on: contact form, booking submission, chatbot *— needs integration setup*
- [x] Mass assignment: spot-check 5 critical models (Booking, Payment, Document, Client, User) — all have explicit `$fillable`, no `$guarded = []`
- [ ] All input goes through FormRequests (spot-check controllers) *— noted: 9 controllers use raw Request; FormRequest directory does not exist; deferred to architectural improvement*
- [ ] Signed URLs on documents + receipts (5-min TTL) — verified by manually replaying an expired URL *— needs Supabase Storage*
- [ ] `composer audit` + `npm audit` clean *— ran in CI, no findings*
- [ ] Secret scan on git history clean (gitleaks full repo) *— needs gitleaks tooling*
- [x] No `dd`, `dump`, `console.log` in code (grep verified across app/, tests/, resources/views/)
- [ ] Logs scrubbed: 1-hour sample of staging logs reviewed for PII leakage *— needs staging logs*
- [x] Sentry data scrubbing config: `BeforeSendHandler` class created with PII-safe field redaction (password, CIN, phone, card data, etc.)

### Task 4: Native Arabic review

Acceptance:
- [ ] Native Arabic reviewer (Sana or designated) walks every public + portal page in `/ar/`
- [ ] All static copy reviewed (translation files)
- [ ] All CMS-stored copy reviewed (services, FAQ, plans, legal)
- [ ] All notification templates reviewed (email, SMS, WhatsApp in Arabic)
- [ ] Chatbot Arabic responses reviewed via a Sana-driven test set
- [ ] Receipt PDF Arabic content reviewed
- [ ] Issues captured as PRs and resolved
- [ ] Reviewer signs off on a checklist in `OPERATIONS/launch/arabic-review-<date>.md`

### Task 5: Real virus scanner

Replace the v1 `null` driver per the decision in `ARCHITECTURE/storage.md`.

Acceptance:
- [ ] Decision finalized: ClamAV (containerized) OR VirusTotal API
- [ ] If ClamAV: container running on the Hetzner box, daily signature update via cron, integration via TCP socket
- [ ] If VirusTotal: API key in env, calls budgeted, fallback to "pending review" if API down
- [ ] `ScanDocumentForViruses` job calls the real scanner
- [ ] EICAR test file rejected end-to-end (uploaded → flagged → deleted → admin emailed)
- [ ] Clean file passes through normally

### Task 6: Backup drill

Acceptance:
- [ ] Per `OPERATIONS/backup-recovery.md` quarterly drill procedure: create a PITR clone of the staging DB
- [ ] Connect a local Laravel instance to the clone, verify migrations + row counts
- [ ] Run a synthetic restore exercise documented in `OPERATIONS/drills/<date>.md`
- [ ] Delete the clone
- [ ] Document any surprises and update the runbook

### Task 7: Compliance pre-launch checklist

Acceptance (per `COMPLIANCE/loi-09-08.md` checklist):
- [ ] Privacy notice published in both languages, linked in footer
- [ ] Cookie banner present
- [ ] Account deletion flow implemented and tested (from phase 4)
- [ ] Data export action implemented (from phase 4)
- [ ] Audit log captures sensitive admin reads (from phase 5)
- [ ] Documents auto-purge configured and verified
- [ ] Conversations anonymized on client deletion
- [ ] Receipts retained 10 years (no purge job touches receipts)
- [ ] CNDP declarations drafted and filed (Sana / counsel files; engineering provides the system descriptions)
- [ ] DPAs collected from Supabase, Stripe, Twilio, Resend, Cerebras
- [ ] Quarterly review reminder set in Sana's calendar

Acceptance (per `COMPLIANCE/notary-rules.md` checklist):
- [ ] No superlatives or comparative claims on the site (final copy review)
- [ ] No testimonials / reviews features present
- [ ] No promotion / discount features
- [ ] Fee pages distinguish consultation vs. act fees
- [ ] Chatbot prompt forbids prohibited claims and fee quotation
- [ ] Footer has full office identification
- [ ] No referral / affiliate features
- [ ] All client data is access-controlled and audit-logged
- [ ] Arabic is the default; legal-document language Arabic where applicable

Acceptance (per `COMPLIANCE/receipts-invoicing.md` checklist):
- [ ] Practice ICE, IF, RC, Patente confirmed and applied to the receipt template
- [ ] VAT treatment confirmed by accountant; receipt template updated accordingly
- [ ] Receipt template legal review by accountant — written sign-off
- [ ] Sequential numbering tested end-to-end (no gaps, no resets across years for the underlying sequence)
- [ ] Sample receipt generated, printed, reviewed
- [ ] Both `reçu` and `facture` flows tested
- [ ] Note-de-crédit flow tested
- [ ] 10-year retention (never auto-purge receipts) verified by reading the data-retention job code
- [ ] Offsite backup of receipts verified by inspecting B2/Wasabi bucket
- [ ] CSV export tested with accountant's preferred format
- [ ] Bilingual rendering of receipt verified for Arabic numerals, alignment, fonts

### Task 8: Production provisioning

Acceptance:
- [ ] Hetzner CCX13 in Frankfurt provisioned via Forge
- [ ] PHP 8.3, Nginx, Redis, PHP-FPM, Horizon Supervisor configured
- [ ] Production Supabase project promoted / created (EU-Central-1, Pro tier, pgvector enabled, PITR on)
- [ ] Supabase buckets created with correct privacy
- [ ] Production environment variables set in Forge (Stripe live, Twilio prod, Resend prod, Cerebras prod, Voyage prod, Sentry prod DSN)
- [ ] APP_KEY generated fresh for prod
- [ ] Cron entry for Laravel scheduler installed
- [ ] TLS via Let's Encrypt, HSTS enabled
- [ ] Deploy hook from `main` requires explicit manual approval

### Task 9: DNS cutover + email deliverability

Acceptance:
- [ ] DNS records set per `OPERATIONS/deployment.md`
- [ ] SPF, DKIM, DMARC records published and verified (no errors from mail-tester.com or similar)
- [ ] Resend domain showing fully verified
- [ ] Test emails sent to Gmail, Outlook, and a Moroccan ISP — all land in inbox, not spam
- [ ] Sender reputation steady (no spikes)
- [ ] Cloudflare in DNS-only mode (gray cloud) — proxied mode deferred to v1.2

### Task 10: Stripe production switch

Acceptance:
- [ ] Stripe keys swapped to live mode in prod env
- [ ] Webhook endpoint added in Stripe Dashboard pointing to prod URL; secret stored in Forge env
- [ ] A live test transaction (Sana, low amount) succeeds and produces a real receipt
- [ ] The test transaction refunded successfully
- [ ] Stripe Radar reviewed; default rules deemed appropriate for v1

### Task 11: Soft launch monitoring window

Acceptance:
- [ ] Site live at `https://sana-bouhamidi.ma`
- [ ] 48-hour soak: Sentry monitored continuously, no Sev-1/2 issues
- [ ] UptimeRobot configured, baseline uptime green
- [ ] Pulse showing healthy traffic
- [ ] At least 2 real bookings completed during soak (Sana + a trusted contact)
- [ ] At least 5 chatbot conversations observed
- [ ] At least 1 reschedule + 1 cancellation tested in production with real refund
- [ ] Issues found logged into a launch log; resolved or scheduled

### Task 12: Sana + assistant onboarding

Acceptance:
- [ ] Sana's prod admin account created with strong password + 2FA enrolled
- [ ] Assistant account created (if assistant available) with same
- [ ] Sana 1-pager cheat sheet refreshed for prod URLs and final UI
- [ ] Assistant 1-pager cheat sheet
- [ ] Screencast of admin walkthrough recorded for re-watching
- [ ] Sana confirms she can: log in, see a booking, cancel a booking with refund, edit a FAQ, edit a service page, change availability

### Task 13: Documentation drift review

Acceptance:
- [ ] Every doc in this set re-read against the built reality
- [ ] Any drift fixed in a final docs-only PR
- [ ] `README.md` updated with prod URLs and post-launch quick links
- [ ] `OPERATIONS/runbooks/` populated with: rotate-app-key, rotate-stripe-webhook-secret, rotate-cerebras-api-key, prod-deploy, hotfix
- [ ] Lessons-learned log started at `OPERATIONS/lessons-learned.md`

### Task 14: Launch communication

Acceptance:
- [ ] Soft-launch announcement drafted (Sana's choice of channel — likely word of mouth + WhatsApp updates to her network)
- [ ] No public marketing launch yet — that's a separate decision after soak

### Task 15: Post-launch checklist

Acceptance (verified day +7 after launch):
- [ ] Bookings flow being used by real clients
- [ ] No critical bugs in 7 days
- [ ] Notifications delivering reliably
- [ ] Sentry budget green
- [ ] Sana comfortable operating the admin solo
- [ ] Quarterly review reminders scheduled in Sana's calendar

## Phase exit criteria

- [ ] All 15 tasks complete
- [ ] All compliance items filed or in flight with documented expected dates
- [ ] No open Sev-1 or Sev-2 issues
- [ ] Sana signs off on launch readiness
- [ ] 48-hour staging-equivalent soak in production clean
- [ ] Backup drill complete with documented outcome
- [ ] Native-speaker review signed off

## Risks

- **CNDP filing timing.** Mitigation: prepare the filing in phase 5, submit during phase 7. If filing slips, the site can launch without it for very short period (Loi 09-08 enforcement on private practices is moderate), but Sana's risk appetite governs — when in doubt, hold launch until filed.
- **WhatsApp template approval timing.** Mitigation: if templates still pending, ship email + SMS at launch and add WhatsApp on approval (no code change needed).
- **Stripe activation rejected or delayed.** Mitigation: if delayed, launch with cash-at-office + free-orientation only; add card on activation.
- **Performance regressions discovered late.** Mitigation: this phase explicitly re-Lighthouses everything; any regression is a phase blocker.
- **Compliance miss.** Mitigation: every compliance doc has an explicit checklist; this phase walks each.

## Demo to Sana

90-min session (the "go / no-go" review):
1. Walk the public site in production in both languages
2. Complete a real (low-amount) booking + payment
3. Receive all notifications on Sana's phone
4. Cancel that booking and process the refund — see the credit note
5. Open admin, walk the day-to-day actions Sana will perform
6. Confirm the cheat sheet matches the live UI
7. Discuss the soft-launch communication plan
8. Receive Sana's go-decision

Sign-off requested on:
- All compliance items
- Production readiness
- Operational comfort
- Soft-launch timing

## Files / artifacts produced

- Live production site at `https://sana-bouhamidi.ma`
- Real virus scanner integrated
- CNDP declaration filed
- DPAs collected
- All accountant + legal sign-offs in writing
- Production runbooks
- Lessons-learned log started
- Native-speaker review sign-off document

## Post-launch (v1.x roadmap pointers — not part of this phase)

Tracked separately:
- CMI gateway (v1.1)
- Cloudflare proxied mode for caching + DDoS
- Marketing launch
- Apple Pay / Google Pay
- Public review of analytics-driven content improvements
- Quarterly compliance review (first one ~3 months post-launch)
- Quarterly backup drill (first one ~3 months post-launch)
- Quarterly dependency upgrade sprint
