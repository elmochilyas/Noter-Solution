# Git Workflow

## Branch model

Trunk-based. One long-lived branch (`main`). Short-lived feature branches.

```
main ──●──●──●──●──●──●──●──●─→ (always deployable)
        \         \         /
         feature   bug-fix
```

- `main` is always green and deployable. CI must pass.
- Production deploys from `main` after manual approval.
- Staging auto-deploys from `main` on every merge.

## Branch naming

`<type>/<scope>-<short-description>`

| Type | Use case |
|---|---|
| `feat` | New user-visible feature |
| `fix` | Bug fix |
| `chore` | Tooling, dependencies, build, docs (no user impact) |
| `refactor` | Code change with no behavior change |
| `perf` | Performance improvement |
| `test` | Tests-only changes |
| `docs` | Documentation-only changes |
| `ci` | CI / pipeline changes |

Examples:
- `feat/booking-calendar-view`
- `fix/payment-webhook-idempotency`
- `chore/upgrade-filament-3-1`
- `refactor/extract-availability-service`

Scope is one of: `booking`, `payment`, `chatbot`, `portal`, `admin`, `auth`, `i18n`, `notification`, `storage`, `infra`, `docs`.

## Commit messages — Conventional Commits

```
<type>(<scope>): <short summary in present tense, ≤72 chars>

<optional body explaining what and why>

<optional footer with breaking changes or issue refs>
```

Examples:

```
feat(booking): add slot hold mechanism

A slot is held for 10 minutes once a client opens the
payment step. Other clients see it as unavailable until
the hold expires or the booking is confirmed.

Refs #42
```

```
fix(payment): verify Stripe webhook signature before processing

Previously the webhook accepted any well-formed payload,
which would have allowed a forged charge confirmation to
mark a booking as paid without an actual payment.

BREAKING: webhook URL no longer accepts payloads without
the Stripe-Signature header.
```

## Commit hygiene

- Atomic commits: one logical change per commit.
- `git rebase -i` to clean up before opening a PR.
- No "WIP", "fix typo", "more fixes" commits in the final history.
- Sign commits with GPG/SSH if possible (optional in v1, required from v2).

## Pull request rules

### Size

- Target: ≤ 400 lines changed (excluding lockfiles and generated files).
- Hard ceiling: 800 lines. Anything bigger must be split or have an explicit reviewer-approved exception.

### Lifecycle

1. Branch from `main`.
2. Develop on the branch with atomic commits.
3. Push and open a draft PR early if you want feedback.
4. When ready: mark as ready for review.
5. CI must be green.
6. Self-review the diff before requesting human review.
7. Address review comments via new commits (do not force-push during review).
8. Once approved: squash-merge to `main`.
9. Delete the branch.

### PR template

```markdown
## Summary
<1–3 sentences: what changed and why>

## Changes
- <bullet list of key changes>

## Linked spec / phase
- <link to the phase or feature doc this implements>

## Screenshots / video
<for UI changes — before/after side by side>

## How to test
1. <step>
2. <step>
3. <expected>

## Risks and considerations
- <anything reviewers should pay attention to>

## Checklist
- [ ] Tests added / updated
- [ ] Docs updated (if behavior or system changed)
- [ ] No secrets in diff
- [ ] No `dd()` / `dump()` / `console.log()` left
- [ ] Lighthouse run on staging if public-facing UI changed
- [ ] Authorization tests for new endpoints
- [ ] Translations added for both `fr` and `ar` if user-facing text added
```

### Review checklist (for reviewers)

Reviewers verify:

**Correctness**
- [ ] Code does what the spec says
- [ ] Edge cases handled (empty, null, boundary, error)
- [ ] No off-by-one or timezone bugs

**Standards**
- [ ] Follows `STANDARDS/coding.md` conventions
- [ ] No business logic in controllers
- [ ] No raw SQL with interpolation
- [ ] Mass assignment safe
- [ ] Authorization on every protected action

**Security**
- [ ] Input validation present
- [ ] No PII in logs
- [ ] No secrets committed
- [ ] CSRF / rate-limit considerations addressed

**Tests**
- [ ] Tests are meaningful, not just for coverage
- [ ] Tests cover failure cases, not just happy path
- [ ] No flakiness introduced

**Performance**
- [ ] No obvious N+1
- [ ] Indexes added for new query patterns
- [ ] Heavy work moved to queue

**Accessibility / i18n**
- [ ] Translation keys used, both languages present
- [ ] Logical CSS properties for RTL
- [ ] Form labels associated
- [ ] Color contrast OK

### Merge strategy

- **Squash and merge** for feature branches → keeps `main` history clean.
- The squashed commit message uses Conventional Commits format and is reviewed before merge.
- Rebase is allowed for keeping a long-lived branch up to date with `main`; not for rewriting `main`.

## Tags and releases

- Production deploys are tagged: `v1.0.0`, `v1.1.0`, `v1.1.1`.
- Semver:
  - **Patch** (`1.1.0` → `1.1.1`) — bug fixes, no behavior change.
  - **Minor** (`1.1.x` → `1.2.0`) — new features, backward compatible.
  - **Major** (`1.x.x` → `2.0.0`) — breaking changes (rare for an app).
- Each tag has a release notes entry in `CHANGELOG.md`.

## Hotfix process

When a critical bug is in production:

1. Branch from the latest production tag: `git checkout -b hotfix/payment-webhook-crash v1.2.3`
2. Apply the minimum fix.
3. Open a PR labelled `hotfix`. Fast-track review.
4. Merge to `main`.
5. Tag as `v1.2.4`.
6. Deploy.

## Files always in `.gitignore`

```
.env
.env.*
!.env.example
/vendor
/node_modules
/storage/*.key
/storage/logs/*
/storage/framework/cache/*
/storage/framework/sessions/*
/storage/framework/views/*
/storage/app/livewire-tmp/*
/public/build/
/public/hot
.phpunit.result.cache
.phpunit.cache
.idea/
.vscode/
.DS_Store
Thumbs.db
auth.json
```

## Git hooks (via `husky` + `lint-staged`)

- **pre-commit:** Pint formatting, ESLint, secret scan via `gitleaks`.
- **pre-push:** Pest fast suite (`--exclude-group=slow`).
- **commit-msg:** Conventional Commits validation.

## Code ownership

- `docs/` and any compliance / legal content: requires Sana's confirmation in PR comments before merge.
- `app/Services/Payment/`, `config/cashier.php`, webhook handlers: requires lead developer review.
- Database migrations: requires lead developer review.
- Infrastructure (`infra/`, `forge/`, deploy scripts): requires lead developer review.

## Definition of Done

A PR is done when:

- [ ] Spec acceptance criteria met
- [ ] Tests added / updated and passing
- [ ] CI green (lint, static analysis, tests, security audit)
- [ ] Code reviewed and approved
- [ ] Documentation updated
- [ ] No regressions on Lighthouse, axe, or query budgets
- [ ] Translations present for both languages if text was added
- [ ] PR description complete with screenshots if UI
- [ ] Merged to `main` and branch deleted
- [ ] Deployed to staging and smoke-tested (or deferred to a documented release window)
