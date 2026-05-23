# Hotfix Process

## When to use
A hotfix is for critical bugs in production:
- Site is down or degraded
- Payment flow is broken
- Data integrity issue
- Security vulnerability actively being exploited

## Procedure

### 1. Branch from production tag
```bash
git checkout -b hotfix/description v1.2.3
```

### 2. Apply the minimum fix
- Only the lines needed to resolve the issue
- No refactoring, no scope expansion
- Add a test that reproduces the bug

### 3. Open a PR
- Label: `hotfix`
- Title: `fix(scope): brief description`
- Body: what broke, why, how fixed, how to verify
- Fast-track: assign to lead developer for immediate review

### 4. Review and merge
- PR must pass CI (at minimum the relevant test suite + lint)
- One approving review required (can be self-merge if lead dev)
- Merge to `main` (squash merge)

### 5. Deploy to production
- Follow `prod-deploy.md`
- Do NOT merge other changes between hotfix merge and deploy

### 6. Post-hotfix
- Document the incident in `docs/OPERATIONS/lessons-learned.md`
- Create a follow-up issue for root cause analysis if the fix was a surface-level patch
- Schedule the proper fix in the next regular sprint

## Communication
- Notify Sana within 30 minutes of confirmed production issue
- Send all-clear message once deployed and verified
- Include: what happened, what was fixed, expected impact
