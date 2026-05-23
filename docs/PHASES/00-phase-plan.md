# Phase Plan — Overview

## Strategy

Spec-driven, phase-by-phase. Each phase ships value while remaining safely composable with the next.

The plan optimizes for:
1. Foundation first — get the right primitives so subsequent phases are fast.
2. Public-facing surface early — visible progress, opportunity to revise content with Sana.
3. Transactional surface (booking + payment) before admin — admin built on top of bookings, not before.
4. Chatbot late — depends on FAQ content stabilizing.
5. Polish last — Lighthouse, security review, accessibility audit, native-speaker review.

## Phase summary

| # | Phase | Goals | Estimate | Doc |
|---|---|---|---|---|
| 1 | Foundation | Project skeleton, Supabase, Filament shell, design tokens, i18n, CI | 2 weeks | `01-foundation.md` |
| 2 | Public site | Marketing pages, FAQ, contact, legal — content in both languages | 2 weeks | `02-public-site.md` |
| 3 | Booking + payment | Booking flow, Stripe integration, receipts, notifications | 3 weeks | `03-booking-payment.md` |
| 4 | Client portal | Magic-link auth, portal pages, document management | 1.5 weeks | `04-client-portal.md` |
| 5 | Admin panel | Filament resources, KPI dashboard, refund flow, settings | 2 weeks | `05-admin-panel.md` |
| 6 | Chatbot | RAG, LLM integration, triage, escalation, admin review | 2 weeks | `06-chatbot.md` |
| 7 | Polish & launch | Accessibility audit, performance pass, security review, native speaker review, soft launch | 1.5 weeks | `07-polish-launch.md` |
| | **Total** | | **~14 weeks** | |

Estimates assume one developer with Ilyas's stack familiarity, working at the iterative pace established in `userPreferences`.

## Critical path

```
Foundation → Public site → Booking + payment → Polish & launch
                              │
                              └→ Client portal → Admin panel → Chatbot
```

Bookings + payment is the critical-path tentpole. Portal, admin, and chatbot can partially parallelize after booking is functional (in practice, one developer works sequentially).

## Sequencing rationale

### Why public site before booking

- Forces resolution of design tokens, layout, i18n plumbing.
- Sana reviews and approves visible copy before the higher-risk booking flow.
- Marketing pages have lower complexity — good warm-up.

### Why booking + payment together

- They're a single user flow. Building one without the other doesn't deliver value.
- Webhook / receipt complexity benefits from full-flow testing.

### Why portal before admin

- Portal is small and reuses booking-flow components.
- Admin builds on the data shapes confirmed during portal work (e.g. cancellation, reschedule).

### Why chatbot late

- FAQ corpus needs to exist before retrieval works well.
- Chatbot copy needs Sana's review — easier once she's been deep in the site for weeks.
- Cost containment: launching chatbot at the end keeps unnecessary API calls down during dev.

### Why polish last

- Many polish tasks (Lighthouse, axe, native review) require near-final content.
- Security review wants the full attack surface.
- Pre-launch checklist is its own phase to avoid skipping it.

## Phase entry & exit criteria

Each phase starts only when:
- Previous phase's exit criteria met
- Required external dependencies in place (Sana copy review, accounts created)
- Spec docs updated where the phase touched architecture

Each phase ends with:
- All acceptance criteria in the phase doc met
- All tests passing in CI
- Demo to Sana
- Sign-off on observable outputs
- Docs updated to reflect built reality

## Dependencies on Sana

Sana's involvement needed at specific points. Front-loaded to avoid blocking:

| Sana checkpoint | Phase | What's needed |
|---|---|---|
| Practice info (ICE, IF, RC, Patente, exact phones) | Foundation | Settings + receipt template |
| Copy review (home, about, services, FAQ, plans) | Public site | Content sign-off |
| Booking T&Cs and cancellation policy | Booking + payment | Legal copy |
| Receipt template validation (with accountant) | Booking + payment | Compliance |
| Notification copy (email, SMS, WhatsApp) | Booking + payment | Reviewed templates |
| Admin walkthrough (Filament training) | Admin panel | Onboarding |
| Chatbot system prompt review | Chatbot | Tone, scope, guardrails |
| Arabic translation review | Polish | Native speaker pass |
| Compliance pre-launch sign-off | Polish | Loi 09-08, notary rules, receipts |
| CNDP declaration filed | Polish | Pre-launch |

## Dependencies on third parties

| Dependency | When needed | Lead time |
|---|---|---|
| Supabase project (prod + staging) | Foundation | <1 day |
| Hetzner + Forge setup | Foundation | <1 day |
| Domain registered + DNS | Foundation | 1–2 days |
| Stripe account activated for Morocco | Booking + payment | 1 week |
| Resend domain verified (SPF/DKIM/DMARC) | Booking + payment | 1 day |
| Twilio account + SMS sender ID approval | Booking + payment | 1–2 weeks |
| Twilio WhatsApp template approvals | Booking + payment / Notifications | 1–3 weeks |
| Cerebras API key (free tier — no credit card) | Chatbot | 1 day |
| Voyage AI key | Chatbot | 1 day |
| CNDP declaration submitted | Polish (pre-launch) | 2–4 weeks |
| CMI merchant account | Post-launch (v1.1) | 4–8 weeks |

Many third-party setups have long lead times — kick them off in Foundation, in parallel with engineering.

## Risk register

| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| Sana's copy review delays | High | Medium | Use placeholders; ship structure; integrate review into phase exit |
| Twilio WhatsApp template approval delay | Medium | Medium | Submit templates in Foundation; have email-only fallback |
| Stripe MA card acceptance issues | Medium | High | Test with real MA card early; plan CMI as v1.1 backup |
| Performance regressions discovered late | Medium | Medium | Lighthouse run after every phase, not just at end |
| RTL bugs surfacing late | Medium | Medium | RTL tested at every phase, not just polish |
| Compliance miss discovered post-launch | Low | High | Compliance docs reviewed early; legal sign-off before launch |
| Scope creep | High | Medium | Strict phase exit criteria; v2 list maintained, not folded in |
| Single-developer bus factor | Constant | High | Docs (this set) are the bus-factor mitigation |

## What's explicitly NOT in v1

Tracked in a v1.x / v2 backlog (not in this docs set):
- E-signature on documents
- Online consultation native (not Jitsi)
- Multi-office support
- Newsletter
- Blog
- Customer reviews (regulated profession — see `COMPLIANCE/notary-rules.md`)
- English locale
- Native mobile app
- Real-time WebSocket updates
- Customer-facing analytics ("My consultation history" beyond a list)
- Apple Pay / Google Pay (later flag flip)
- Voucher / discount codes
- Document templates / generation

## Definition of "launched"

Launch is a soft launch, not a marketing event:

- The site is live at `sana-bouhamidi.ma` with TLS.
- Sana and the assistant can log in and use the admin.
- A test client (Sana herself) has gone end-to-end through booking + payment + portal.
- Sentry and Pulse are reporting clean.
- Sana has approved the visible copy.
- All compliance items checked (CNDP filed, receipts validated by accountant, etc.).
- Phone numbers and email work and forward to the practice.

A "marketing launch" (announcing on Sana's professional channels) follows the soft launch by 1–2 weeks of observing real usage.

## How to use the per-phase docs

Each phase doc (`01-foundation.md` through `07-polish-launch.md`) contains:

- **Goal** — what success looks like
- **Prerequisites** — what must be true to start
- **Scope** — what's in vs. out
- **Task list** — concrete, sequenced, with acceptance criteria
- **Dependencies on others** — Sana, third parties
- **Exit criteria** — what must be true to finish
- **Demo script** — what to show Sana
- **Risks specific to the phase**

An AI agent executing on a task should load:
- The phase doc
- The relevant feature doc(s)
- The relevant architecture doc(s)
- The applicable standards docs

See `AI-AGENT-GUIDE.md`.

## Cadence

- Daily: working sessions, no formal stand-ups.
- Weekly: 30-min sync with Sana (status, copy review, demos).
- End of phase: demo + sign-off.
- Mid-phase course-correction allowed; major scope changes require updating the phase doc and re-approving.

## Tracking

- GitHub issues per task within a phase.
- Project board with columns: Backlog / In progress / Review / Done.
- Issues link to the phase doc section.
- PRs link to issues.

Light-weight; no Jira-style overhead.
