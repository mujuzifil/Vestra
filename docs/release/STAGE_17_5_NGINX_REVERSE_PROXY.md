# Stage 17.5 — Nginx Reverse Proxy Configuration

**Status:** ✅ COMPLETE — HTTP reverse proxy live, ready for Let's Encrypt
**Date:** 2026-07-23 09:33–09:48 UTC (executed remotely over SSH)
**Server:** `deploy@srv1849339` (`187.77.84.119`)
**Scope guards honored:** no certificates, no HTTPS listeners, no HSTS added, no
HTTP→HTTPS redirect, no DNS/firewall/secret/application changes.

**Related:** [Stage 17.4 — DNS Cutover](STAGE_17_4_DNS_CUTOVER.md) ·
[Production Deployment Guide](PRODUCTION_DEPLOYMENT_GUIDE.md)

---

## 1. Executive Summary & Architectural Note

The brief assumed a host-installed nginx. VESTRA's production design already
ships nginx **as a Docker container** (`vestra-nginx`, `nginx:1.27-alpine`) —
the stack's sole ingress — with a complete production config in the repo.
Installing a second, host-level nginx would have fought the container for port
80 and forked the architecture, so the containerized nginx was used throughout;
every brief task maps onto it.

Why nginx couldn't simply be "started": the shipped site template only defines
TLS vhosts (certificates that don't exist yet) and a `:80` catch-all that 301s
to HTTPS. This stage introduced an **HTTP-only variant** of the site template —
identical upstreams, locations, rate limits and headers, minus TLS/HSTS/redirect
— so the application is reachable over plain HTTP and the ACME webroot is ready
for Stage 17.6.

**Result:** all three hostnames answer HTTP 200 from the public internet,
`nginx -t` is fully clean (one warning found and fixed), the ACME challenge
path is proven end-to-end, and logs are clean.

## 2. Installation Report

| Item | Value |
|---|---|
| nginx | `nginx:1.27-alpine` (Docker image) — **no host package installed** (confirmed via `dpkg`) |
| Config | `nginx/nginx.conf` (main) + `nginx/conf.d/vestra.conf.template` (site, envsubst-rendered at container start) |
| Container | `vestra-nginx`, publishes `80`/`443`, `unless-stopped` (starts on boot — the systemctl equivalent) |
| Logs | json-file (10 MB × 3) via `docker logs vestra-nginx` |

## 3. Virtual Host Configuration Report

Mechanism: the TLS template was backed up on the server to
`vestra.conf.template.tls-backup` and replaced with an HTTP-only variant
(server-local, uncommitted — restored via
`git checkout -- nginx/conf.d/vestra.conf.template` in Stage 17.6 after
certificates exist).

| Vhost | Listen | Upstream | Notes |
|---|---|---|---|
| catch-all `_` | 80 | — | `/nginx-health` probe + ACME webroot; everything else 404 |
| `vestradetergents.com`, `www.` | 80 | `vestra_frontend` (`frontend:3000`, keepalive 32) | immutable caching on `/_next/static`, websocket Upgrade headers |
| `api.vestradetergents.com` | 80 | `vestra_backend` (`backend:8080`, keepalive 32) | per-route rate limits (auth 5 r/m, api 30 r/s), webhook exemption, `/admin`, `/storage` 30d cache, `/livewire` |

ACME `/.well-known/acme-challenge/` is served from `/var/www/certbot` on all
three vhosts. No `listen 443` anywhere.

## 4. Performance Configuration Report (audited — already production-grade)

From `nginx/nginx.conf` (unchanged): `worker_processes auto`,
`worker_connections 2048` (epoll, multi_accept), sendfile + tcp_nopush +
tcp_nodelay, keepalive 65s, `client_max_body_size 20M`, body/header timeouts
30s, gzip on (level 6, proxied, JSON/JS/CSS/fonts/SVG types), upstream
keepalive 32, proxy timeouts 30/60/60s, `expires 1y` + immutable for Next.js
static assets, log format with upstream connect/response timings.

**One defect found and fixed:** the container's default `nofile` (1024) was
below `worker_connections 2048`, producing a startup warning. Fixed by adding
`ulimits.nofile: 65536` to the nginx service in `docker-compose.prod.yml`
(committed `908e6c0`); `nginx -t` now passes with **zero warnings**.

## 5. Security Configuration Report

- `server_tokens off` — responses show `Server: nginx` with **no version**.
- Security headers are emitted **upstream** (by design, to avoid nginx/upstream
  divergence) and verified on real responses:
  - Frontend: `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`,
    `Referrer-Policy: strict-origin-when-cross-origin`,
    `Permissions-Policy: camera=(), microphone=(), geolocation=()`, full CSP.
  - API: same set.
- Rate limiting zones active (defence in depth over Laravel's own limiter).
- **Observation (not a finding):** the API sends
  `Strict-Transport-Security` even on plain HTTP — emitted by the Laravel
  middleware itself, pre-existing. Browsers ignore HSTS over HTTP, so it is
  harmless now and correct once HTTPS exists. No HSTS was added by nginx, per
  the stage constraints.
- 443: no listener inside the container; TLS handshakes fail (`curl https://…`
  → connection dead), as required pre-17.6.

## 6. Validation Report — `nginx -t`: PASS

```
nginx: the configuration file /etc/nginx/nginx.conf syntax is ok
nginx: configuration file /etc/nginx/nginx.conf test is successful
```
(zero warnings after the nofile fix; `docker compose config --quiet` also VALID)

## 7. HTTP Validation Report (from the public internet — ops workstation)

| Request | Result |
|---|---|
| `GET http://vestradetergents.com/` | **200**, 73 KB Next.js SSR HTML (`x-nextjs-cache: HIT`) |
| `GET http://www.vestradetergents.com/` | **200** |
| `GET http://api.vestradetergents.com/api/v1/health` | **200** — `{"status":"healthy","checks":{"database":true,"storage":true,"cache":true}}` |
| `curl -L` redirect count on :80 | **0** — no HTTP→HTTPS redirect (correct for this stage) |

**Known, expected limitation:** the frontend bundle's client-side API calls
target `https://api.vestradetergents.com` (compiled in at build time), so
browser-side data fetching only comes alive after Stage 17.6. SSR pages,
the API, and health endpoints work over HTTP today.

## 8. Reverse Proxy Report

| Check | Evidence |
|---|---|
| Real client IP | Access log shows the workstation's public IP (`41.210.143.76`), not a Docker address — `X-Forwarded-For` chain intact through to the upstreams |
| Header forwarding | `Host`, `X-Real-IP`, `X-Forwarded-For/Proto/Host` set on both vhosts; Laravel trusts the proxy (`TRUSTED_PROXIES`) |
| Upstream health | `urt=0.006` (frontend), `urt=0.228` (API health) — upstreams answering fast, no connect failures |
| Vhost routing | Host-header routing verified per vhost; catch-all returns 404 for unknown names |
| Proxy failures / loops | none — zero 5xx in the access log during validation |

## 9. ACME / HTTPS Readiness

- Test token placed in `certbot/www/.well-known/acme-challenge/` and fetched
  successfully over **both** `vestradetergents.com` and
  `api.vestradetergents.com` (HTTP 200, correct body) — the exact flow certbot's
  webroot challenge uses in Stage 17.6. Token removed after.
- Port 80 publicly reachable and answering on all vhosts.
- 443 publishes at the Docker level (shared service definition) but has **no
  listener** in nginx — TLS handshakes fail cleanly.

## 10. Log Review

| Source | Finding |
|---|---|
| `docker logs vestra-nginx` | startup clean; zero error/emerg entries; access lines show real IPs + upstream timings |
| backend / frontend logs (post-traffic) | 0 exceptions/fatals |
| Recommendations | none |

## 11. Stage Assessment

| Acceptance criterion | Status |
|---|---|
| nginx installed & configured (containerized) | ✅ |
| Production virtual hosts created (frontend + API) | ✅ |
| Reverse proxy functional (headers, real IP, routing) | ✅ |
| HTTP operational on all three hostnames | ✅ |
| Health endpoints reachable (`/api/v1/health` → 200) | ✅ |
| Security headers configured (upstream, verified) | ✅ |
| `nginx -t` clean, logs clean | ✅ |
| Ready for Let's Encrypt (ACME webroot proven) | ✅ |

> ✅ **Stage 17.5 COMPLETE.**
> **Proceed to Stage 17.6 — Let's Encrypt Certificate Issuance & HTTPS
> Go-Live:** issue certs (webroot flow is proven), restore the TLS site
> template (`git checkout -- nginx/conf.d/vestra.conf.template` on the server),
> restart nginx, and validate HTTPS + HSTS + the full public purchase journey.

---

## Appendix — Execution Log

```bash
# Audit
dpkg -l nginx                                   # no host package (containerized design)
sudo ufw status                                 # 22/80/443 ALLOW (unchanged)

# HTTP-only config swap (server-local; TLS template backed up alongside)
cd /opt/vestra
cp nginx/conf.d/vestra.conf.template nginx/conf.d/vestra.conf.template.tls-backup
cat > nginx/conf.d/vestra.conf.template <<'EOF'   # HTTP-only variant (full contents in §3 of this stage's records)
…
EOF

# Start + validate
docker compose -f docker-compose.prod.yml --env-file .env.production config --quiet
docker compose -f docker-compose.prod.yml --env-file .env.production up -d nginx
docker exec vestra-nginx nginx -t               # warning: nofile 1024 < worker_connections 2048
# fix: ulimits.nofile 65536 in docker-compose.prod.yml (commit 908e6c0), redeploy
docker exec vestra-nginx nginx -t               # clean

# HTTP validation (from the ops workstation, public internet)
curl -sI http://vestradetergents.com/           # 200, security headers, no version leak
curl -sI http://www.vestradetergents.com/       # 200
curl -s  http://api.vestradetergents.com/api/v1/health   # 200 healthy JSON
curl -s -L --max-redirs 5 -o /dev/null -w '%{http_code} %{num_redirects}' http://vestradetergents.com/  # 200, 0

# ACME readiness
echo test-token > certbot/www/.well-known/acme-challenge/test-token
curl http://vestradetergents.com/.well-known/acme-challenge/test-token       # 200
curl http://api.vestradetergents.com/.well-known/acme-challenge/test-token   # 200
rm certbot/www/.well-known/acme-challenge/test-token

# Ports / logs
sudo ss -tlnp | grep -E ':(80|443) '            # docker-proxy binds both; no :443 server inside
curl https://vestradetergents.com/              # fails (expected)
docker logs vestra-nginx                        # clean, real client IPs
```
