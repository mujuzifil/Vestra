# PHASE 15 — Production Readiness & Launch Certification

**Platform:** VESTRA E-Commerce
**Phase:** 15 — Production Readiness & Launch Certification
**Date:** 2026-07-22
**Preceding phases:** 9 (Security Remediation), 10 (Commerce Integrity)

---

## 1. Executive Summary

VESTRA is feature-complete. This phase introduced no business functionality and
no architectural redesign. Its objective was to certify that the platform can be
deployed and operated in production.

The decisive activity was **building and running the production stack end to
end**, which had never been done before. That distinction matters: the
production configuration differs from the development configuration in ways that
static review cannot detect — `--no-dev` dependency installs, `config:cache`,
a reverse proxy, an empty database, compiled front-end bundles.

Running it surfaced **eleven Critical defects**, of which the most severe were
invisible by inspection:

- The production backend image **could not be built at all**.
- `composer.lock` still shipped **Debugbar as a production dependency**.
- A stale development config cache **overrode production configuration**.
- `env()` returns null under `config:cache`, so production **trusted no
  proxies** — collapsing every user's rate limit into one shared bucket — and
  created the first administrator with the **shipped default password**.
- That, in turn, tripped a boot guard that **bricked the application entirely**
  on first deploy: every endpoint 500'd, including the admin panel needed to
  fix it.
- The frontend bundle called **`localhost:8000`** in production.
- There was **no queue worker and no scheduler**.

Every one of these would have caused a failed or unsafe launch. All are fixed
and verified against a running stack.

**Certification: 🟡 READY WITH MINOR OBSERVATIONS.**

The platform itself is production-ready: no Critical or High defects remain
open in the application, infrastructure or deployment pipeline. Certification is
held at amber for one reason outside the codebase — **credentials committed to
git history have not yet been rotated**. That is an operator action, and go-live
is blocked on it. Once §13 Part 1 is signed off, this becomes 🟢 PRODUCTION READY
with no further engineering work.

---

## 2. Infrastructure Readiness ✅

| Item | Before | After |
|---|---|---|
| Production image builds | ❌ **Impossible** (`ext-exif` missing) | ✅ Both build |
| Queue worker | ❌ Absent | ✅ Dedicated service |
| Scheduler | ❌ Absent | ✅ Dedicated service |
| Reverse proxy / TLS | ❌ Commented out; ports exposed | ✅ nginx sole ingress |
| `APP_KEY` | ❌ Not passed | ✅ Required, fails fast |
| CORS | ❌ Collapsed to API origin | ✅ Explicit, verified |
| Redis policy | ⚠️ Default LRU | ✅ `noeviction` + AOF |
| Log rotation | ❌ Unbounded | ✅ 10 MB × 3 per service |
| Resource limits | ❌ None | ✅ All services |
| Migrations / `storage:link` | ❌ Never ran | ✅ In entrypoint |

Eight services: nginx, frontend, backend, queue, scheduler, db, redis, certbot.
`backend`, `queue` and `scheduler` share one image; only `backend` carries
`RUN_MIGRATIONS=true`, so replicas cannot race the schema.

Redis uses `noeviction` deliberately — it backs the queue and sessions, not just
cache. An LRU policy would silently discard queued orders under memory pressure.

## 3. Deployment Readiness ✅

The pipeline previously **pushed images it never deployed**: CI published
commit-tagged images, but the compose file declared `build:` with no `image:`,
so `docker compose pull` was a no-op and the VPS rebuilt from local source.
Rollback by tag was impossible.

Now: images are tagged and pulled; `deploy.sh` validates configuration, backs
up, records `PREVIOUS_TAG`, migrates **before** cutover so a failing migration
aborts while the old containers still serve, then health-gates the switch.
`rollback.sh` restores the previous tag and warns explicitly that database
migrations are **not** reverted.

## 4. Frontend Readiness ✅

The critical defect: `NEXT_PUBLIC_*` values are compiled into the client bundle
at build time, but were supplied only as runtime environment. The shipped bundle
called `http://localhost:8000`.

Verified by building with a distinctive origin and confirming inlining:

```
injected origin present in client bundle:  3 chunks
```

`localhost:8000` still appears as a dead `??` fallback literal that minification
preserves — provably unreachable, since the left operand is a non-null string
constant. The CI guard therefore asserts the injected origin is **present**
rather than that "localhost" is absent; the naive check would fail on every
correct build.

Also: `npm ci` replaces `npm install` (which exposed a lockfile out of sync with
`package.json`), `global-error.tsx` added, TypeScript clean.

## 5. Backend Readiness ✅

Beyond the infrastructure fixes, four latent application defects were found by
running the stack:

**`env()` under `config:cache`.** Every production deploy runs `config:cache`,
which makes `env()` return null outside `config/*.php`. Three call sites relied
on it. The worst was `TrustProxies`: production trusted **no proxies**, so
`X-Forwarded-For` was ignored and every request appeared to come from nginx —
per-client rate limits became one global bucket (a single abusive client could
lock out all users), audit logs recorded the proxy address, and HTTPS detection
failed. This passed all tests, because test environments never cache config.
`ProductionConfigIntegrityTest` now fails the build if `env()` reappears.

**First deploy bricked itself.** The boot guard compared the administrator
password against `BOOTSTRAP_ADMIN_PASSWORD` — the value the seeder had just
used. Seeding tripped it immediately and 500'd everything, including the admin
panel needed to fix it. It now targets the shipped default (`Admin@12345`), the
credential that is actually a risk; first-login rotation remains enforced by
`force_password_change_at`.

**A database blip took down everything.** `PDOException extends
RuntimeException`, so `catch (RuntimeException) { throw; }` re-threw every
transient database error out of provider boot.

**401s returned as 500s.** Unauthenticated `/api/*` requests without an
`Accept: application/json` header redirected to an undefined `login` route.
Tests passed because they always send the header.

## 6. Security Verification ✅

| Control | Result |
|---|---|
| Security headers | ✅ Present, duplicates removed |
| HSTS | ✅ `max-age=31536000; includeSubDomains; preload` |
| CORS — allowed origin | ✅ Echoes configured origin |
| CORS — foreign origin | ✅ Not echoed; browser blocks |
| Unauthenticated access | ✅ 401 with **and without** `Accept` header |
| `APP_DEBUG` / Debugbar | ✅ False; Debugbar removed from production packages |
| npm production vulnerabilities | ✅ **7 → 0** |
| Composer advisories | ✅ **3 → 0** (Guzzle 7.15.1) |
| Rate limiting integrity | ✅ Restored — was globally shared via `TrustProxies` |
| Tracked secrets | ⚠️ Removed from tree; **history purge and rotation outstanding** |

## 7. Commerce Verification ✅

Phase 10 commerce logic is untouched. Public catalogue, categories and settings
endpoints return 200; protected order endpoints correctly return 401. Queue
processing — which order confirmation and payment notification mail depend on —
was absent before this phase and is now verified working.

Full purchase-journey and webhook verification against live Flutterwave
credentials is a go-live activity; it cannot be performed with rehearsal keys.
It is enumerated in the Deployment Verification Checklist §7.

## 8. Operational Readiness ✅

Backups now **verify before reporting success** — a dump that is empty,
truncated (no completion marker), contains no tables, or fails `gzip -t` is
rejected rather than recorded. A silently truncated backup is worse than none,
because it is trusted during an incident. Restore verifies the archive before
touching the live database and snapshots current state first.

Health endpoints return 503 on failure; readiness probes DB, cache and Redis;
liveness deliberately checks nothing external, so a database blip cannot trigger
an orchestrator restart loop.

## 9. Performance Review ✅

Production-critical only, per scope. Opcache tuned with `validate_timestamps=0`;
config, route, view and event caches warmed at startup rather than baked at
build time; MySQL slow-query log enabled at 1 s; nginx gzip and immutable
caching for static assets; keepalive to both upstreams; queue worker bounded by
`--max-time` and `--max-jobs` to cap memory growth in long-lived PHP processes.

No speculative optimisation was undertaken. No N+1 problems were observed in the
endpoints exercised; a load-based review is more appropriate once real traffic
patterns exist.

## 10. Documentation Produced

Twelve documents in `docs/release/`: Production Deployment Guide · Operations
Runbook · Environment Configuration Guide · Backup & Restore Guide · Release
Notes · Known Issues · Go-Live Checklist · Rollback Checklist · Support Handover
· Deployment Verification Checklist · Secret Rotation Checklist · this
certification.

Each documents not only the procedure but the failure modes found in this phase
— the build-time/run-time distinction, the `env()`/`config:cache` hazard, the
staging of product images before seeding, and why rollback does not revert the
database.

## 11. Validation Results

| Validation | Result |
|---|---|
| Backend PHPUnit — baseline | ✅ 117 passed (891 assertions) |
| Backend PHPUnit — after changes | ✅ **120 passed, 0 failed** (897 assertions) |
| Backend production image build | ✅ Succeeds (previously impossible) |
| Frontend production image build | ✅ Succeeds |
| Frontend TypeScript | ✅ Clean |
| Compose config validation | ✅ Valid |
| Full stack startup | ✅ All 8 services healthy |
| Migrations on empty database | ✅ Applied by entrypoint |
| `storage:link` | ✅ Created |
| Health — normal | ✅ 200, all checks true |
| Health — **DB stopped** | ✅ **503**, `"database": false` |
| Readiness — DB stopped | ✅ **503** |
| Liveness — DB stopped | ✅ **200** (correctly unaffected) |
| Recovery after DB restart | ✅ Automatic, no intervention |
| Queue — worker stopped | ✅ Jobs accumulate |
| Queue — worker running | ✅ Dequeues, executes, records failures |
| Scheduler | ✅ Both cleanup commands registered hourly |
| Security headers | ✅ Present, no duplicates |
| CORS enforcement | ✅ Correct both directions |
| Unauthenticated API | ✅ 401 with and without `Accept` |
| npm audit (production) | ✅ 0 vulnerabilities |
| composer audit (production) | ✅ 0 advisories |
| Client bundle API origin | ✅ Injected origin confirmed present |

### 11.1 Test suite status

**Baseline: 117 passed. Final: 120 passed, 0 failed (897 assertions).**

Two existing tests were updated — not suppressed. Both drove the application
through `putenv()`, which meant they were asserting the **buggy** `env()`-based
mechanism and could never have caught the production failure:

- `AuthenticationSecurityTest::test_trust_proxies_respects_environment` →
  renamed `..._respects_configuration`, driven through `config()`.
- `AdminUserSeederTest::test_seeder_resets_password_when_reset_flag_is_true` →
  driven through `config()`.

That these tests passed while production trusted no proxies is precisely the
point: a test that exercises a code path production never takes provides false
assurance. No `putenv()` calls remain in the suite.

Three tests added — `ProductionConfigIntegrityTest` — which scan runtime code
for `env()` usage, assert the migrated settings resolve through `config()`, and
verify `TrustProxies` reads from configuration. These fail the build if the
class of bug recurs.

`ProductionBootstrapPasswordTest` passes **unchanged**, confirming the guard's
security intent survived the fix to the first-deploy brick.

## 12. Files Modified

**Infrastructure:** `docker-compose.prod.yml` (rewritten) · `docker-compose.dev.yml` ·
`backend/Dockerfile.prod` · `backend/.dockerignore` · `backend/docker/entrypoint.sh` ·
`backend/docker/nginx/default.conf` · `frontend/Dockerfile` · `frontend/.dockerignore` ·
`nginx/nginx.conf` (new) · `nginx/conf.d/vestra.conf.template` (new) ·
`.env.production.example` (new)

**Application:** `backend/app/Providers/AppServiceProvider.php` ·
`backend/app/Http/Middleware/TrustProxies.php` ·
`backend/app/Http/Controllers/Api/V1/HealthController.php` ·
`backend/bootstrap/app.php` · `backend/config/app.php` ·
`backend/database/seeders/AdminUserSeeder.php` ·
`backend/database/seeders/DatabaseSeeder.php` ·
`backend/composer.lock` · `frontend/app/global-error.tsx` (new) ·
`frontend/package.json` · `frontend/package-lock.json`

**Tests:** `backend/tests/Feature/ProductionConfigIntegrityTest.php` (new) ·
`backend/tests/Feature/AuthenticationSecurityTest.php`

**CI/CD & scripts:** `.github/workflows/ci.yml` · `.github/workflows/deploy.yml` ·
`scripts/deploy.sh` (new) · `scripts/rollback.sh` (new) · `scripts/backup.sh` ·
`scripts/restore.sh` · `.gitignore`

**Deleted:** `VPS.txt` · `New Text Document.txt`

**Untouched:** all controllers, services, models, policies and migrations from
Phases 9 and 10; `frontend/next.config.ts`; all React components.

## 13. Remaining Risks

### 🔴 Blocking go-live

**Credentials in git history.** `VPS.txt` (VPS root password) and
`New Text Document.txt` (`admin@vestra.com` password, an AWS-labelled key) were
committed and remain in history. They are deleted from the working tree and
git-ignored, but anyone with a clone, fork or mirror retains them.

This cannot be closed from inside the repository. Rotate every affected
credential — rotation, not the history purge, is what closes the exposure.
→ [Secret Rotation Checklist](SECRET_ROTATION_CHECKLIST.md)

### 🟡 Accepted

Rehearsal ran on Docker Desktop for Windows, not the target Linux VPS.
Container behaviour transfers; host concerns (systemd, real certbot issuance,
TLS chain validation, `ufw`/Docker iptables interaction, true resource headroom)
are first-run verification items. Also: Pint backlog, no static analysis,
single-instance deployment, Redis as a single point of failure, no centralised
logging. All detailed in [Known Issues](KNOWN_ISSUES.md).

## 14. Go-Live Checklist

→ [GO_LIVE_CHECKLIST.md](GO_LIVE_CHECKLIST.md) — eight phases from security
prerequisites through sign-off, plus first-24-hour monitoring and abort criteria.

## 15. Rollback Checklist

→ [ROLLBACK_CHECKLIST.md](ROLLBACK_CHECKLIST.md) — including the distinction
between rollback and restore, and why a destructive migration makes rollback
alone insufficient.

## 16. Recommendation

## 🟡 READY WITH MINOR OBSERVATIONS

The engineering work is complete. Eleven Critical and several High-severity
defects were found and fixed, each verified against a running production stack
rather than by inspection. No Critical or High issue remains open in the
application, infrastructure, or deployment pipeline.

Certification is held at amber solely because **credentials exposed in git
history have not been rotated**. That is an operator action with no engineering
dependency.

**On completion of Part 1 of the Secret Rotation Checklist, this certification
becomes 🟢 PRODUCTION READY** with no further engineering work required.

A closing observation worth carrying forward: every Critical defect in this
phase was invisible to code review and to the test suite, because each depended
on a production-only condition. The suite passed at 117/117 throughout — while
the production image could not be built, shipped a debug toolbar, ignored its
proxy configuration, and bricked itself on first deploy. Rehearsing the real
deployment is what found them. That rehearsal is now scripted and should be
repeated whenever the deployment configuration changes.

---

| Role | Name | Signature | Date |
|---|---|---|---|
| Coordinator | | | |
| Engineering | | | |
| Operations | | | |
| Business owner | | | |
