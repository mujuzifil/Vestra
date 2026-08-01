# Phase 11 — Known Issues

## Environment Limitations

- PHP is not on PATH and Docker is not running, so backend PHPUnit tests cannot be executed here.
- Real browser/device QA, Lighthouse, and cross-browser testing were not performed in this environment.

## Legacy Commerce Code

- Cart, checkout, wishlist, and saved-for-later API endpoints remain exposed.
- `carts` and `cart_items` database tables remain.
- These are documented in `backend/docs/COMMERCE_CLEANUP_PLAN.md` and will be removed in a dedicated cleanup phase.

## Frontend

- `frontend/package-lock.json` accumulates platform-specific optional `@next/swc-*` packages on Windows. This file should not be committed until it is regenerated in a clean CI environment.
- `frontend/Dockerfile` has local infrastructure changes that are intentionally excluded from this commit.

## Recommended Follow-Up

1. Execute full backend test suite in Docker/CI.
2. Run Playwright smoke tests against the production build.
3. Run Lighthouse and axe accessibility scans.
4. Complete backend commerce cleanup.
