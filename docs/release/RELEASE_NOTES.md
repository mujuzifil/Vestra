# VESTRA — Release Notes

## v1.0.0 — Production Launch Candidate

**Date:** 2026-07-22 · **Phase:** 15 — Production Readiness & Launch Certification

The platform is feature-complete. Phase 15 introduced **no new business
functionality**; it closed the gap between "works on the development stack" and
"runs correctly on a production VPS", and validated that claim against a real
production stack rather than by inspection.

---

## Why this release matters

The production stack had never actually been run before this phase. Building it
end to end surfaced a set of defects that no amount of code review would have
found, because each depended on production-only conditions: `--no-dev` installs,
`config:cache`, a real reverse proxy, an empty database.

Most consequentially, **the production backend image could not be built at
all**, and once that was fixed, several failures only appeared under
`config:cache` — the exact configuration every production deploy uses and no
test environment does.

---

## Critical fixes

### The image could not be built
`spatie/laravel-medialibrary` requires `ext-exif`, which was never installed.
`composer install` failed, so the production image had never been produced
successfully.

### Debugbar shipped to production
`composer.lock` still listed `barryvdh/laravel-debugbar` in the **production**
package set. Stage 9.1.2 moved it to `require-dev` in `composer.json` but never
regenerated the lock. A debugging tool was being installed into production
images, and its stale provider metadata additionally broke the build.

### Development configuration overrode production
A stale `bootstrap/cache/config.php` was copied into the image by `COPY . .`.
Laravel prefers the cached config over the environment, so containers would
have silently run on development settings. `.dockerignore` now excludes compiled
caches; the entrypoint builds them at runtime, once real configuration exists.

### `env()` returned null in production
`config:cache` — which every production deploy runs — makes `env()` return null
outside `config/*.php`. Three call sites depended on it:

- **`TrustProxies`** — production trusted **no proxies**. `X-Forwarded-For` was
  ignored, so every request appeared to originate from nginx: per-client rate
  limits collapsed into a single shared bucket (one abusive client could lock
  out every user), audit logs recorded the proxy address, and HTTPS detection
  failed.
- **`AdminUserSeeder`** — the first production seed created the administrator
  with the shipped default password regardless of configuration.
- **`DatabaseSeeder`** — the `DEMO_DATA` flag was inert.

All now read through `config()`. `ProductionConfigIntegrityTest` fails the build
if `env()` reappears in runtime code.

### First deployment bricked itself
The boot guard compared the administrator's password against
`BOOTSTRAP_ADMIN_PASSWORD` — the value the seeder had just used. Seeding
therefore tripped the guard immediately and returned 500 for **every** request,
including the admin panel needed to fix it and the health endpoints needed to
diagnose it. The guard now targets the shipped default (`Admin@12345`), which is
the credential that actually constitutes a risk; first-login rotation remains
enforced by `force_password_change_at`.

### A database blip took down the entire application
`PDOException extends RuntimeException`, so the guard's
`catch (RuntimeException) { throw; }` re-threw every transient database error
out of provider boot. Any brief database interruption produced application-wide
500s — including on the health endpoints. Failure to *verify* is no longer
treated as evidence of a violation.

### The frontend called `localhost` in production
`NEXT_PUBLIC_*` values are compiled into the client bundle at build time, but
were only supplied as runtime environment. The shipped bundle pointed at
`http://localhost:8000`. They are now build arguments, the build fails without
them, and CI asserts the injected origin appears in the output.

### No queue worker, no scheduler
`QUEUE_CONNECTION=redis` with no worker meant queued jobs never ran. Nothing ran
`schedule:run`, so `auth:cleanup-exchange-tokens` and `sanctum:cleanup-expired`
never fired and expired tokens accumulated indefinitely. Both now run as
dedicated services.

### Deployment pipeline never deployed what it built
CI pushed commit-tagged images; the compose file declared `build:` with no
`image:`, so `docker compose pull` was a no-op and the VPS rebuilt from local
source. Images are now tagged and pulled, with `PREVIOUS_TAG` recorded for
rollback.

---

## Also fixed

- `APP_KEY`, `CORS_ALLOWED_ORIGINS`, Flutterwave, mail and session settings were
  absent from the production stack — CORS collapsed to the API origin, blocking
  the storefront entirely
- Production entrypoint now waits for MySQL and Redis, migrates, creates the
  storage symlink and warms caches; it never seeds
- Container healthcheck targeted a static nginx route returning 200 regardless
  of application state; it now exercises the real endpoint
- Health endpoints returned 200 while unhealthy; they now return **503**, and
  readiness genuinely probes DB, cache and Redis. Liveness deliberately checks
  nothing external, so a database blip cannot trigger a restart loop
- Unauthenticated `/api/*` requests without an `Accept` header returned 500
  instead of 401
- Duplicate security headers from nginx and middleware both emitting
- Healthchecks probed `localhost`, which resolves to IPv6 first while the
  servers bind IPv4 only
- The scheduler inherited a web healthcheck it could never satisfy
- No TLS or reverse proxy; application ports were published directly
- CI never ran PHPUnit. A PHPStan step had been failing silently since it was
  added — PHPStan was never installed
- `package-lock.json` was out of sync with `package.json`

## Security

- **7 → 0** npm production vulnerabilities (`shadcn` moved to devDependencies,
  removing the MCP SDK and Hono server from the shipped image; `sharp` and
  `postcss` overridden; Next 15.5.21)
- **3 → 0** Composer advisories (Guzzle 7.15.1)
- Credentials removed from the working tree — **rotation still outstanding**,
  see [Secret Rotation Checklist](SECRET_ROTATION_CHECKLIST.md)
- Redis set to `noeviction` with AOF so queued jobs and sessions cannot be
  silently evicted
- Container logs capped, preventing disk exhaustion
- Resource limits on every service

## Infrastructure added

nginx reverse proxy with TLS termination and HTTP→HTTPS redirect · automated
certificate renewal · queue worker · scheduler · `.env.production.example` ·
`deploy.sh` and `rollback.sh` · backup verification (a truncated dump is now
rejected rather than trusted) · `global-error.tsx`

## Documentation

Twelve documents under `docs/release/`, covering deployment, operations,
configuration, backup and recovery, verification, rollback, go-live, known
issues, handover and this certification.

---

## Upgrade notes

**First deployment:** follow the
[Production Deployment Guide](PRODUCTION_DEPLOYMENT_GUIDE.md) in order. Stage
product images before seeding — `ProductSeeder` aborts without them.

**Existing deployments:** `.env.production` is now required and must define
`APP_KEY`, `CORS_ALLOWED_ORIGINS` and the `NEXT_PUBLIC_*` build args. The stack
refuses to start otherwise — deliberately, since each was silently broken before.

**Breaking:** health endpoints now return 503 when unhealthy. Monitoring that
treats any response as success must be updated to check the status code.

## Verification

117 backend tests passing before this phase; 3 added for the config-cache
hazard. Both production images build. The full stack was stood up end to end,
with database-failure, recovery, queue, scheduler and health behaviour verified
empirically. Details in
[PHASE_15_PRODUCTION_READINESS_CERTIFICATION.md](PHASE_15_PRODUCTION_READINESS_CERTIFICATION.md).
