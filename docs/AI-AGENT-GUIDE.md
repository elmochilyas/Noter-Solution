# AI Coding Agent Guide

This document tells AI coding agents (Claude Code, Cursor, etc.) how to operate productively in this repository.

## Operating principles

1. **Read before you write.** Always load the relevant docs into context before editing code.
2. **One task at a time.** Don't chain unrelated changes in one PR.
3. **Ask when ambiguous, decide when clear.** If acceptance criteria are unclear, ask. If they're clear, just execute.
4. **Stay in scope.** Don't refactor unrelated code. Open a separate task for that.
5. **Update docs in the same change.** If your edit invalidates a doc statement, update the doc in the same commit.
6. **Match existing conventions.** Read 2–3 nearby files before writing a new one. Mimic structure, naming, and style.
7. **Run tests and linters before declaring done.** `composer test`, `php artisan test`, `npm run lint`.
8. **For any UI task, use `DESIGN/`.** Stitch HTML is reference, not source — translate to Blade + Tailwind + Livewire using the design system tokens, with logical CSS properties for RTL and translation keys for copy. Never copy Stitch HTML/CSS verbatim. If no Stitch output exists for a screen, design from `DESIGN/design-system.md`.

## Which docs to read for which task

| Task type | Docs to load |
|---|---|
| New feature | `PROJECT.md`, the relevant `FEATURES/*.md`, `STANDARDS/coding.md`, `STANDARDS/security.md`, `ARCHITECTURE/overview.md` + any architecture doc the feature touches |
| Bug fix | The relevant `FEATURES/*.md`, `STANDARDS/testing.md`, the architecture doc for the affected system |
| Refactor | `STANDARDS/coding.md`, `ARCHITECTURE/overview.md`, `ARCHITECTURE/domain-model.md` |
| Database change | `ARCHITECTURE/database-schema.md`, `ARCHITECTURE/domain-model.md`, `STANDARDS/security.md` |
| Auth / permission change | `ARCHITECTURE/auth.md`, `COMPLIANCE/loi-09-08.md`, `STANDARDS/security.md` |
| Payment change | `ARCHITECTURE/payments.md`, `FEATURES/payment.md`, `COMPLIANCE/receipts-invoicing.md`, `STANDARDS/security.md` |
| Chatbot change | `ARCHITECTURE/chatbot.md`, `FEATURES/chatbot.md` |
| UI work | `DESIGN/README.md`, `DESIGN/design-system.md`, `DESIGN/screens-index.md` (find your screen → load its Stitch HTML + Stitch prompt + Blade target path), the relevant `FEATURES/*.md`, `STANDARDS/accessibility-i18n.md` |
| Deployment / infra | `OPERATIONS/*.md`, `STANDARDS/security.md` |
| New phase | The relevant `PHASES/0X-*.md`, `PHASES/00-phase-plan.md` |

## Required output for every task

Every completed task produces:

1. **Working code** that passes tests and linters.
2. **Tests** that cover the new behavior (see `STANDARDS/testing.md`).
3. **Doc updates** if the change affects any documented behavior.
4. **A commit message** following Conventional Commits (see `STANDARDS/git-workflow.md`).
5. **A PR description** following the template in `STANDARDS/git-workflow.md`.

## How to ask vs. how to proceed

**Ask the human when:**
- A required env var or third-party credential is missing
- A business rule isn't in the docs and isn't obvious
- A choice affects compliance (Loi 09-08, notary rules) and isn't documented
- An external system would be modified (production DB, payment provider, DNS)

**Proceed without asking when:**
- The task spec is clear and complete
- The change is internal to the codebase
- The convention to follow is already documented or visible in nearby code

## Definition of Done

A task is done when **all** of the following are true:

- [ ] All acceptance criteria in the phase / feature spec are met
- [ ] Code passes `composer test` (Pest + static analysis)
- [ ] Code passes `npm run lint`
- [ ] New behavior is covered by tests (unit, feature, or Dusk as appropriate)
- [ ] No new linter / static-analysis warnings introduced
- [ ] Docs updated where applicable
- [ ] PR description includes: what changed, why, how to test, screenshots if UI
- [ ] No secrets, API keys, or PII in code or commits
- [ ] No `dd()`, `dump()`, `var_dump()`, `console.log()` left behind
- [ ] No commented-out code blocks left behind
- [ ] Feature flags or migration safety measures added if needed
- [ ] **For UI tasks:** translation keys used (no hardcoded strings), logical CSS properties used (no `ml-`/`mr-`/`text-left`/`text-right`), RTL render verified on `/ar/`, design tokens from `DESIGN/design-system.md` used (no ad-hoc hex colors or font names)

## Anti-patterns to avoid

- ❌ Editing migrations that have already run in any environment — create a new migration instead.
- ❌ Adding business logic to controllers — push it into services.
- ❌ Calling external APIs from inside Eloquent model methods.
- ❌ Hardcoded French or Arabic strings in PHP / Blade — always use translation keys.
- ❌ Inline styles in Blade — use Tailwind classes only.
- ❌ Disabling Larastan (`@phpstan-ignore-line`) without a comment explaining why.
- ❌ Importing the `Auth` facade in Filament resources — use Filament's helpers.
- ❌ Calling any LLM API or external service synchronously inside a HTTP request when it could be queued.
- ❌ Using `request()->all()` or unprotected mass assignment.
- ❌ Writing tests that touch the real Stripe / Cerebras / Twilio APIs.
- ❌ Copying Stitch HTML/CSS verbatim into the codebase — translate to Blade + Tailwind utilities mapped to design tokens.
- ❌ Using LTR-only Tailwind utilities (`ml-`, `mr-`, `pl-`, `pr-`, `text-left`, `text-right`, `left-`, `right-`) — use logical variants (`ms-`, `me-`, `ps-`, `pe-`, `text-start`, `text-end`, `start-`, `end-`).
- ❌ Inventing colors, fonts, or spacing values not in `DESIGN/design-system.md`.
- ❌ Shipping a UI screen without verifying it renders on `/ar/` (RTL) in the browser.

## Repository conventions reference

- Branch naming: `feat/<scope>-<short>`, `fix/<scope>-<short>`, `chore/<scope>-<short>`
- Commit messages: Conventional Commits (`feat(booking): …`, `fix(payment): …`)
- PHP version: 8.3+
- Laravel version: 13.x
- Filament version: 3.x (pinned to minor)
- Node version: 22 LTS
- Package manager: `composer` for PHP, `npm` for JS

## When the agent gets stuck

1. Re-read the feature spec and the architecture doc.
2. Read 3 similar existing files in the codebase for the convention.
3. Search git history for the last time a similar change was made.
4. If still stuck, stop and ask. Don't guess and ship.
