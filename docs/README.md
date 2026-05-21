# Sana Bouhamidi — Project Documentation

This directory is the authoritative source of context for every contributor — human or AI — working on the Sana Bouhamidi notary website.

## How to use this documentation

**For AI coding agents:** read `AI-AGENT-GUIDE.md` first. It tells you exactly which docs to load for which task.

**For human contributors:** start with `PROJECT.md` to understand the product, then read the relevant `STANDARDS/` docs before writing code. Architecture docs are reference material; consult them when touching the system they describe.

## Document tree

```
docs/
├── README.md                          ← you are here
├── PROJECT.md                         vision, scope, personas, glossary
├── AI-AGENT-GUIDE.md                  how AI agents should work in this repo
│
├── STANDARDS/                         the rules of how we build
│   ├── coding.md
│   ├── security.md
│   ├── performance.md
│   ├── testing.md
│   ├── accessibility-i18n.md
│   └── git-workflow.md
│
├── ARCHITECTURE/                      the structure of what we build
│   ├── overview.md
│   ├── database-schema.md
│   ├── domain-model.md
│   ├── auth.md
│   ├── payments.md
│   ├── chatbot.md
│   ├── notifications.md
│   ├── storage.md
│   └── observability.md
│
├── FEATURES/                          feature specs with acceptance criteria
│   ├── public-site.md
│   ├── booking.md
│   ├── payment.md
│   ├── client-portal.md
│   ├── admin-panel.md
│   ├── chatbot.md
│   ├── notifications.md
│   ├── document-management.md
│   └── i18n.md
│
├── OPERATIONS/                        runbooks and procedures
│   ├── environment-setup.md
│   ├── deployment.md
│   └── backup-recovery.md
│
├── COMPLIANCE/                        legal and regulatory
│   ├── loi-09-08.md
│   ├── notary-rules.md
│   └── receipts-invoicing.md
│
├── DESIGN/                            design system + Stitch UI assets
│   ├── README.md                      how to use the design assets
│   ├── design-system.md               canonical tokens (colors, type, spacing)
│   ├── stitch-prompts.md              the 27 Stitch prompts (regeneration)
│   ├── screens-index.md               lookup: feature → Stitch HTML → Blade path
│   └── stitch-output/                 generated HTML mockups (drop your files here)
│       └── README.md                  manifest of expected files
│
└── PHASES/                            the build plan
    ├── 00-phase-plan.md
    ├── 01-foundation.md
    ├── 02-public-site.md
    ├── 03-booking-payment.md
    ├── 04-client-portal.md
    ├── 05-admin-panel.md
    ├── 06-chatbot.md
    └── 07-polish-launch.md
```

## Reading order for first-time contributors

1. `PROJECT.md`
2. `STANDARDS/coding.md` + `STANDARDS/security.md` + `STANDARDS/git-workflow.md`
3. `ARCHITECTURE/overview.md` + `ARCHITECTURE/database-schema.md`
4. `DESIGN/README.md` + `DESIGN/design-system.md` (if you'll touch any UI)
5. `OPERATIONS/environment-setup.md`
6. `PHASES/00-phase-plan.md` and the current active phase

## Source-of-truth policy

- Code is the only source of truth for runtime behaviour. Docs document intent, not implementation details that change weekly.
- When code and docs disagree, **the docs are stale, not the code** — fix them in the same PR that introduces the divergence.
- All docs are versioned with the code. There is no separate doc deployment.

## Maintenance

- Every PR that changes a system MUST update the docs in the same PR.
- Definition of Done explicitly includes documentation.
- Quarterly review: archive obsolete sections, fix drift, refresh examples.
