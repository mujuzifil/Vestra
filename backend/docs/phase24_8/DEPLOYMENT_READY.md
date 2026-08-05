# Phase 24.8 — Deployment Readiness

## Implementation status

Admin Profile is implemented and locally validated.

- Route: `/profile` (`filament.admin.pages.profile`)
- Navbar Profile → `ProfilePage::getUrl()`
- `ProfilePageTest`: **7 passed**

## Production deploy blocker

The worktree `feature/phase24-8-admin-profile` also contains **uncommitted** work from Blog, Media, Staff RBAC, and Roles RBAC (phases 13.33–13.36).

Production deploy via `./scripts/deploy.sh --build` on `/opt/vestra` requires:

1. Explicit commit (and merge to `develop` → `master`) of the **intended release set**
2. Pull on the production host
3. `./scripts/deploy.sh --build`
4. Smoke: admin `/profile`, edit, password, sessions; HTTP 200 on admin/API/site

**Not executed yet:** no production deploy was run, because releasing the full uncommitted multi-phase tree without an explicit commit/merge decision risks shipping incomplete adjacent work.

## Deploy commands (when approved)

```bash
# On production host (/opt/vestra), after merge to master:
git pull
./scripts/deploy.sh --build
```

Then verify Admin domain `/profile` and write `docs/release/PHASE24_8_DEPLOYMENT_REPORT.md` with image tag + rollback target.
