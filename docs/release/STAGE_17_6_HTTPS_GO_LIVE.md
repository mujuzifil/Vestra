# Stage 17.6 — Let's Encrypt, HTTPS Enablement & Production Go-Live

**Status:** ✅ COMPLETE — **VESTRA Detergents is LIVE in production**
**Date:** 2026-07-23 10:00–11:25 UTC
**Server:** `deploy@srv1849339` (`187.77.84.119`) — Hostinger VPS, Ubuntu 24.04.4 LTS
**Live URLs:** https://vestradetergents.com · https://www.vestradetergents.com · https://api.vestradetergents.com

**Related:** [Stage 17.5 — Nginx Reverse Proxy](STAGE_17_5_NGINX_REVERSE_PROXY.md) ·
[Go-Live Checklist](GO_LIVE_CHECKLIST.md)

---

## 1. Executive Summary

Trusted Let's Encrypt certificates were issued for all production hostnames via
the webroot flow, the production TLS nginx configuration was restored, HTTPS is
live with HTTP→HTTPS redirects everywhere, and the full application was
validated from the public internet — including the previously-dark client-side
API and the Filament admin panel.

Validation also surfaced **four genuine production defects**, all root-caused,
fixed, committed, redeployed and re-verified (§9). The final regression gate —
the complete PHPUnit suite run CI-parity against the exact go-live commit —
passed **120/120 (897 assertions)**.

## 2. Certificate Report

| Lineage | SANs | Issuer | Validity |
|---|---|---|---|
| `vestradetergents.com` | `vestradetergents.com`, `www.vestradetergents.com` | Let's Encrypt (YE2) | 2026-07-23 → 2026-10-21 |
| `api.vestradetergents.com` | `api.vestradetergents.com` | Let's Encrypt (YE2) | 2026-07-23 → 2026-10-21 |

- Method: **webroot** (`certbot/certbot` container) against the proven ACME
  path — zero downtime, matching the running architecture. Staging dry-runs
  passed for both lineages before real issuance.
- Registration: no email (`--register-unsafely-without-email`) — no ops mailbox
  exists yet; expiry risk is covered by the renewal loop (§10) and the external
  monitoring recommendation (§13).
- `notBefore` is backdated 1 h by LE (clock-skew tolerance) — expected.
- Chain verifies against the system CA store from an external client.

## 3. HTTPS Validation Report (final state, 11:21 UTC)

| Hostname | HTTP | HTTPS |
|---|---|---|
| `vestradetergents.com` | **301** → `https://vestradetergents.com/` | **200** |
| `www.vestradetergents.com` | **301** → `https://www.vestradetergents.com/` | **200** |
| `api.vestradetergents.com` | **301** → `https://api.vestradetergents.com/` | **200** |

No redirect loops. `nginx -t`: **zero warnings** (after the OCSP fix, §9.2).

## 4. TLS & Security Report

- Protocols: **TLS 1.2 and 1.3 accepted; 1.0/1.1 rejected** (nginx.conf policy).
- HSTS: `max-age=31536000; includeSubDomains; preload` on both vhosts (frontend
  via nginx, API via the app middleware — de-duplicated this stage, §9.4).
- Full header set verified live: `X-Frame-Options: DENY`,
  `X-Content-Type-Options: nosniff`, `Referrer-Policy`,
  `Permissions-Policy`, CSP; `Server: nginx` with version hidden.
- OCSP stapling: **not applicable** — Let's Encrypt retired OCSP; their certs
  no longer carry a responder URL (§9.2).

## 5. Application Validation Report

| Component | Evidence | Result |
|---|---|---|
| Health endpoint | `GET /api/v1/health` → 200, `{"database":true,"storage":true,"cache":true}` | PASS |
| Frontend | SSR 200 (`x-nextjs-cache: HIT`), no `http://` asset references (no mixed content) | PASS |
| Client-side API | `GET /api/v1/products` → 6 products with prices/stock; CORS preflight from storefront origin → 204 | PASS |
| Database / Redis / Storage | via health checks; 13+ h stable | PASS |
| Queue / Scheduler | healthy; hourly recycle by design; cleanup jobs registered | PASS |
| Containers | 8/8 running (nginx, certbot, frontend, backend, queue, scheduler, db, redis) | PASS |

## 6. Production Smoke Test Report

**Customer journey (API-level, from the public internet):**
catalogue (6 products, prices, stock) → product detail by slug
(`/products/ecosuit-cleaner` → 200) → session/cart infrastructure live →
checkout reachable. **Payment step deferred:** Flutterwave live keys are not
yet configured, so gateway initialization correctly declines; adding the live
keys to `.env.production` + restarting containers activates it (owner action —
credentials must come from the Flutterwave dashboard).

**Admin journey:** `https://api.vestradetergents.com/admin` → 302 to the
**https** login URL; login page renders 200 with all-https assets.
**First login is an owner action:** sign in with the bootstrap admin email +
the generated `BOOTSTRAP_ADMIN_PASSWORD` (server `.env.production`) and
complete the forced password change.

**Email:** SMTP not configured — deferred post-launch operational task per the
stage brief. Mail-dependent flows (password reset, order confirmation) activate
when `MAIL_*` is populated.

## 7. Performance Report

| Metric | Value |
|---|---|
| Homepage (external, 3 runs) | 0.80 / 0.83 / 0.84 s (dominated by transcontinental RTT) |
| API health (external, 3 runs) | 0.71 / 0.74 / 0.77 s |
| gzip | active (`Content-Encoding: gzip`) |
| Static caching | `s-maxage=31536000` homepage; immutable `/_next/static` |
| Container memory | ~720 MB total RSS of 7.8 GB (db 475 MB is the largest) |
| Container CPU | ≈ idle; scheduler tick spikes are transient |

## 8. Log Review Report

- nginx/backend/frontend/queue/scheduler logs: **no unresolved errors**. The
  only exceptions logged were the pre-fix "Vite manifest not found" entries
  (resolved, §9.3) and they have not recurred since the fixed build deployed.
- System journal: no error entries.
- No TLS handshake failures, no proxy errors, no restart loops.

## 9. Production Defects Found & Fixed (all committed)

| # | Defect | Root cause | Fix | Commit |
|---|---|---|---|---|
| 1 | certbot issuance hung silently | `docker compose run certbot` appends args to the service's renewal-loop `entrypoint` instead of replacing it — the container slept 12 h | Issue with `--entrypoint certbot`; documented for runbooks | (procedure) |
| 2 | `nginx -t` warning: "ssl_stapling ignored, no OCSP responder URL" | Let's Encrypt retired OCSP; new certs carry no responder URL | Removed `ssl_stapling` from `nginx/nginx.conf` | `9507768` |
| 3 | Admin panel 500 "Vite manifest not found" | `Dockerfile.prod` had no Node stage and `public/build` is dockerignored — Filament assets could never exist in the image | Added `assets` stage (`npm ci && vite build`), copied into the app stage | `a2c1dc0` |
| 4 | `/admin` redirected to **http://**; client IPs/proxy headers never trusted | (a) `TrustProxies` exploded `'*'` into an array matched as an IP literal; (b) the default global stack's framework `TrustProxies` ran **after** the app's and reset the trusted list to empty — proxy trust was never active anywhere in production | (a) keep `'*'` as a string; (b) `middleware->replace()` the framework middleware with the app's, leaving exactly one | `1d42393`, `4f7d5a6` |
| 5 | `http://` favicon on the admin login page | Filament evaluates `asset()` during provider **register** phase — before middleware; `URL::forceScheme` in `boot()` was already too late | `URL::forceScheme('https')` in `AppServiceProvider::register()` | `4f7d5a6`→moved |

**Regression gate:** full PHPUnit suite, CI-parity environment, exact go-live
commit: **120 passed / 0 failed (897 assertions)** — including both
trusted-proxies configuration tests.

## 10. Automatic Certificate Renewal

- Mechanism: `vestra-certbot` container, `certbot renew --webroot` twice daily;
  nginx serves the challenge on all vhosts over plain HTTP (the redirect
  exempts `/.well-known/acme-challenge/`).
- **Dry-run result: all simulated renewals succeeded** for both lineages.
- Renewal config persisted at issuance (`certbot/conf/renewal/`).

## 11. Go-Live Assessment

| Acceptance criterion | Status |
|---|---|
| Let's Encrypt certificates issued | ✅ both lineages |
| HTTPS on all production hostnames | ✅ |
| HTTP → HTTPS redirects | ✅ 301, no loops |
| TLS validation (1.2/1.3, chain trusted) | ✅ |
| Security headers incl. HSTS | ✅ |
| Frontend operational over HTTPS | ✅ |
| API operational over HTTPS | ✅ |
| Client-side API communication | ✅ (products, CORS, health) |
| No mixed content | ✅ (favicon defect found & fixed) |
| Production smoke tests | ✅ (payment/email deferred — credentials pending) |
| Automatic renewal verified | ✅ dry-run passed |
| Test suite regression gate | ✅ 120/120 |

**Deferred (owner actions, not blockers):**
1. Flutterwave live keys → `.env.production` + container restart (activates payments).
2. SMTP `MAIL_*` → `.env.production` (activates all transactional mail).
3. First admin login + forced password change.
4. GitHub Actions secrets (8) → enables CI/CD deploys.
5. Recommended: external uptime + cert-expiry monitor; off-site backup target.

---

# 🚀 VESTRA Detergents is officially LIVE in Production.

> Storefront: https://vestradetergents.com
> Admin: https://api.vestradetergents.com/admin
> Health: https://api.vestradetergents.com/api/v1/health

---

## Appendix — Execution Log (condensed)

```bash
# Pre-issuance
dig +short {,www.,api.}vestradetergents.com @1.1.1.1        # 187.77.84.119 ×3
curl http://vestradetergents.com/.well-known/acme-challenge/precheck

# Issuance (note --entrypoint: compose run appends to the service entrypoint)
docker compose … run --rm --entrypoint certbot certbot certonly --webroot -w /var/www/certbot \
  -d vestradetergents.com -d www.vestradetergents.com --register-unsafely-without-email --agree-tos --dry-run
docker compose … run --rm --entrypoint certbot certbot certonly --webroot -w /var/www/certbot \
  -d api.vestradetergents.com --register-unsafely-without-email --agree-tos --dry-run
# (then without --dry-run, both lineages)
sudo openssl x509 -in certbot/conf/live/<domain>/fullchain.pem -noout -issuer -dates -ext subjectAltName

# TLS enablement
git checkout -- nginx/conf.d/vestra.conf.template           # restore TLS template
docker compose … up -d --force-recreate nginx && docker compose … up -d certbot
docker exec vestra-nginx nginx -t

# Validation
curl -sI http://{,www.,api.}vestradetergents.com/           # 301 → https ×3
curl --tlsv1.2/--tlsv1.3 https://vestradetergents.com/      # 200; 1.0/1.1 rejected
curl https://api.vestradetergents.com/api/v1/health         # 200 all-true
curl https://api.vestradetergents.com/api/v1/products       # 6 products
curl -sI https://api.vestradetergents.com/admin             # 302 → https login
docker compose … run --rm --entrypoint certbot certbot renew --webroot -w /var/www/certbot --dry-run

# Defect fixes (§9): commits 9507768, a2c1dc0, 1d42393, 4f7d5a6 — each rebuilt
# and redeployed; final suite: 120/120 passed.
```
