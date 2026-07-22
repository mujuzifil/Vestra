# VESTRA — Known Issues

Open items at the close of Phase 15. Everything here is known, assessed and
either accepted or scheduled. Nothing Critical or High affecting the running
application remains open.

---

## 🔴 Critical — action required outside the codebase

### KI-001 — Credentials exposed in git history

**Status:** Removed from working tree; **history purge and rotation outstanding**

`VPS.txt` (VPS root password) and `New Text Document.txt` (`admin@vestra.com`
password, an AWS-labelled key) were committed and tracked. Both are deleted and
now git-ignored, but they remain in history — anyone with a clone, fork or
mirror retains them.

**This cannot be closed from inside the repository.** Rotate every affected
credential and, separately, purge history. Rotation is the part that actually
closes the exposure; the purge without rotation protects nothing.

→ [Secret Rotation Checklist](SECRET_ROTATION_CHECKLIST.md)

**Go-live is blocked on this.**

---

## 🟡 Medium

### KI-002 — Pint code-style backlog

**Status:** Accepted; CI advisory

`vendor/bin/pint --test` reports violations across roughly 40 files inherited
from earlier phases (`concat_space`, `ordered_imports`, `fully_qualified_strict_types`
and similar). All cosmetic; none affect behaviour.

Enforcing would mean a repo-wide reformat, which is out of scope for a
production-readiness phase and would bury functional diffs in noise. The CI job
runs with `continue-on-error: true` so the signal stays visible without
blocking.

**Recommendation:** run `vendor/bin/pint` as a standalone commit in a quiet
period, then drop `continue-on-error`.

### KI-003 — No static analysis

**Status:** Step removed from CI

`.github/workflows/ci.yml` previously ran `vendor/bin/phpstan analyse`, but
PHPStan **has never been installed** — it is absent from `composer.json` and
there is no `phpstan.neon`. The step failed on every run and was masked by
`continue-on-error: true`, so it produced no signal while appearing to.

The phantom step has been removed rather than left to imply coverage that does
not exist. Introducing PHPStan/Larastan means adopting a new tool and working
through its initial findings — real work, but not production-readiness work.

**Recommendation:** add `larastan/larastan` at level 5 in a follow-up.

### KI-004 — Frontend lint warnings

**Status:** Accepted; CI advisory

`npm run lint` reports `react-hooks/exhaustive-deps` warnings, notably
`lib/cart-context.tsx:97` (missing `guestCart`, `queryClient`). These are
warnings, not errors; the build succeeds.

Cart state is exercised by the commerce test suite and the rehearsal purchase
journey. Adding the dependencies naively risks a re-render loop, so this needs
deliberate review rather than a mechanical fix.

### KI-005 — Rehearsal was not performed on the target OS

**Status:** Inherent environmental limitation

The Phase 15 deployment rehearsal ran on Docker Desktop for Windows, not the
target Linux VPS. Container-level behaviour transfers; host-level concerns do
not and remain unverified until first deployment:

- systemd unit behaviour and boot ordering
- certbot HTTP-01 issuance against real DNS (self-signed certificates were used)
- Real TLS chain validation and HSTS behaviour in a browser
- `ufw` interaction with Docker's iptables rules
- Actual disk and memory headroom under production load

Each appears as a first-run verification item in the
[Go-Live Checklist](GO_LIVE_CHECKLIST.md).

---

## 🟢 Low

### KI-006 — `localhost:8000` string persists in the client bundle

**Status:** Verified harmless

`frontend/lib/api/client.ts` ends with `?? "http://localhost:8000/api/v1"`. That
literal survives minification even when `NEXT_PUBLIC_API_URL` is correctly
inlined, because the compiler keeps both operands of `??`.

Verified during Phase 15 by building with a distinctive origin: the injected
value is used and the fallback is provably unreachable (the left operand is a
non-null string constant).

Worth knowing because a naive `grep localhost .next/static` looks alarming and
is not evidence of a fault. The CI guard therefore asserts the injected origin
is **present** rather than that "localhost" is absent.

### KI-007 — Single-instance deployment

**Status:** Accepted for launch scale

One container per service; no horizontal scaling or multi-node redundancy.
`restart: unless-stopped` covers process failure, but VPS loss means downtime
bounded by the disaster-recovery procedure (RTO ≈ 2 h).

Appropriate for launch volume. Revisit if sustained traffic warrants it.

### KI-008 — Redis is a single point of failure

**Status:** Accepted; mitigated

One Redis instance backs cache, queue **and** sessions. If it fails, sessions
drop and queued work stalls.

Mitigated by `--maxmemory-policy noeviction` (the queue is never silently
evicted) and AOF persistence with `appendfsync everysec` (at most ~1 s of queue
loss on hard failure). Separating cache from queue/session Redis would be the
next step if this proves limiting.

### KI-009 — No centralised log aggregation

**Status:** Accepted

Logs live in Docker's json-file driver, capped at 10 MB × 3 per service.
Adequate for single-host operation and bounded against disk exhaustion, but
there is no search across services and history is shallow under high volume.

**Recommendation:** ship to Loki, or a hosted service, when operational load
justifies it.

---

## Resolved in Phase 15

Recorded because several were latent and would have surfaced only in
production.

| ID | Issue | Severity |
|---|---|---|
| — | Production backend image could not build — `ext-exif` missing, required by `spatie/laravel-medialibrary` | Critical |
| — | `composer.lock` listed Debugbar as a **production** dependency; stale provider metadata also broke the image build | Critical |
| — | Stale dev `bootstrap/cache/config.php` copied into the image, overriding production configuration at run time | Critical |
| — | `PDOException extends RuntimeException`, so the bootstrap-password guard re-threw **every** database error out of provider boot, 500-ing all requests including health probes | Critical |
| — | `env()` called from runtime code returns null under `config:cache`. In production this meant **no proxies were trusted** (rate limits collapsed to one shared bucket, audit logs recorded nginx's IP, HTTPS detection failed) and the first seed created the admin with the shipped default password | Critical |
| — | The boot guard compared against `BOOTSTRAP_ADMIN_PASSWORD` rather than the shipped default, so a correct first deployment bricked itself — every endpoint 500'd, including the admin panel needed to fix it | Critical |
| — | Unauthenticated `/api/*` requests without an `Accept: application/json` header returned 500 (redirect to an undefined `login` route) instead of 401 | High |
| — | Frontend shipped without `NEXT_PUBLIC_API_URL` inlined; client bundle called `localhost:8000` | Critical |
| — | No queue worker in production — queued jobs never ran | Critical |
| — | No scheduler — expired auth tokens never pruned | Critical |
| — | `APP_KEY` absent from production compose | Critical |
| — | CORS collapsed to the API origin, blocking the storefront | Critical |
| — | Deploy pipeline pushed images the compose file never used | Critical |
| — | No migrations or `storage:link` in the production entrypoint | High |
| — | Container healthcheck hit a static nginx route that returned 200 regardless of application state | High |
| — | Health endpoints returned 200 while unhealthy | High |
| — | No TLS, no reverse proxy, backend and frontend ports published directly | High |
| — | CI never ran PHPUnit | High |
| — | `package-lock.json` out of sync with `package.json` | High |
| — | 7 npm production vulnerabilities → 0 | High |
| — | 3 Composer advisories (Guzzle < 7.15.1) → 0 | Medium |
| — | Healthchecks probed `localhost`, which resolves to IPv6 first; servers bind IPv4 only | Medium |
| — | Scheduler inherited a web healthcheck it could never satisfy | Medium |
| — | Unbounded container logs | Medium |
| — | Redis eviction policy could silently discard queued jobs and sessions | Medium |
