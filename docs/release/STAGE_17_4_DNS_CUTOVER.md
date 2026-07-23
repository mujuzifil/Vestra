# Stage 17.4 — DNS Configuration & Production Cutover

**Status:** ✅ COMPLETE — DNS cutover verified, production VPS live on the domain
**Dates:** 17.4A records updated 2026-07-22 (operator, hPanel) ·
17.4B verified 2026-07-23 09:13–09:25 UTC
**Server:** `deploy@srv1849339` (`187.77.84.119`) — Hostinger VPS

**Related:** [Stage 17.3A — Housekeeping](STAGE_17_3A_PRODUCTION_HOUSEKEEPING.md) ·
[Production Deployment Guide](PRODUCTION_DEPLOYMENT_GUIDE.md)

---

## 1. Executive Summary

The domain `vestradetergents.com` has been cut over from the previous host
(`2.57.91.91`) to the production VPS (`187.77.84.119`). All three production
hostnames resolve correctly on **21/21 resolver checks** — both authoritative
Hostinger nameservers, Google, Cloudflare, Quad9, OpenDNS, and the VPS's own
resolver — with TTL 300 as planned. The stack behind the domain re-validated
fully healthy post-cutover: 6/6 containers healthy, both health endpoints green,
logs clean, zero regressions.

nginx and certbot remain deliberately stopped — they start in Stage 17.5/17.6,
now unblocked.

## 2. DNS Before Report (17.4A audit, 2026-07-22)

| Record | Value before | TTL |
|---|---|---|
| `A @` | `2.57.91.91` (previous host) | ~300 |
| `AAAA` | none | — |
| `www` | `CNAME → vestradetergents.com` | 300 |
| `api` | **no record** | — |
| `MX` / `TXT` / SPF / DKIM / DMARC | none (no mail configured) | — |
| `NS` | `nova.dns-parking.com`, `cosmos.dns-parking.com` (Hostinger) | 86400 |
| SOA | serial 2026072102, `dns.hostinger.com` | 600 |

Registrar/DNS provider: **Hostinger** (zone managed in hPanel → Domains →
DNS Zone). No mail records existed, so nothing required preservation.

## 3. DNS Change Report (17.4A, applied by operator in hPanel)

| Hostname | Action | Type | Old value | New value | TTL |
|---|---|---|---|---|---|
| `@` | edited | A | `2.57.91.91` | `187.77.84.119` | 300 |
| `api` | created | A | — (did not exist) | `187.77.84.119` | 300 |
| `www` | unchanged | CNAME | `vestradetergents.com` | `vestradetergents.com` | 300 |

The `www` CNAME follows the root A record automatically. Changes detected on
the authoritative nameservers and confirmed by the operator's client-side
`nslookup` (17.4A) and the full resolver sweep below (17.4B).

## 4. Propagation Report (17.4B, 2026-07-23 09:13:36 UTC)

Every check queried with `dig +noall +answer`; all returned
`187.77.84.119` with TTL 300 (`www` via its CNAME chain).

| Resolver | `@` | `www` | `api` |
|---|---|---|---|
| nova.dns-parking.com (authoritative) | PASS | PASS | PASS |
| cosmos.dns-parking.com (authoritative) | PASS | PASS | PASS |
| Google (8.8.8.8) | PASS | PASS | PASS |
| Cloudflare (1.1.1.1) | PASS | PASS | PASS |
| Quad9 (9.9.9.9) | PASS | PASS | PASS |
| OpenDNS (208.67.222.222) | PASS | PASS | PASS |
| VPS local (127.0.0.53) | PASS | PASS | PASS |

**Propagation: 21/21 = 100%.** Full convergence (as expected with TTL 300 —
the cutover propagated within minutes of the hPanel edit).

## 5. Hostname Validation Report

| Check | Result |
|---|---|
| SSH via `vestradetergents.com` | **PASS** — session landed on `srv1849339` (the production VPS) |
| SSH via `api.vestradetergents.com` | **PASS** — same host |
| `curl http://<hostname>/` (80) | connection refused — **expected**: nginx intentionally stopped until Stage 17.5; not a failure |

The domain demonstrably routes end-to-end to the production VPS.

## 6. Container Health Report (2026-07-23 09:20 UTC)

| Container | Status | Health | Restarts |
|---|---|---|---|
| vestra-backend | Up 12 hours | healthy | 0 |
| vestra-db | Up 12 hours | healthy | 0 |
| vestra-frontend | Up 12 hours | healthy | 0 |
| vestra-queue | Up ~1 h cycles | healthy | 11* |
| vestra-redis | Up 12 hours | healthy | 0 |
| vestra-scheduler | Up 12 hours | healthy | 0 |

\* **By design, not a crash loop:** the queue worker runs with
`--max-time=3600`, so it exits and is recycled by `unless-stopped` once per
hour (12 h uptime ≈ 11 recycles). Queue logs are clean; container currently
healthy mid-cycle. Docker daemon: `active`.

## 7. Application Validation Report

| Component | Check | Result |
|---|---|---|
| Health endpoint | `GET /api/v1/health` → HTTP 200, `{"database":true,"storage":true,"cache":true}` | PASS |
| Frontend health | `GET /api/health` → `{"status":"healthy"}` | PASS |
| Database | health check + containers Up 12 h | PASS |
| Redis | health check (`cache:true`) | PASS |
| Storage | health check (`storage:true`); media validated in 17.3 | PASS |
| Scheduler | `auth:cleanup-exchange-tokens` + `sanctum:cleanup-expired` registered, hourly | PASS |
| Queue worker | running, `queue:work` active, no failed jobs | PASS |

## 8. Log Review Report

| Source | Findings |
|---|---|
| App logs (backend/frontend/queue/scheduler, last 300 lines each) | No exceptions, fatals, or repeated errors |
| db/redis logs | No errors |
| System journal (`journalctl -p err -b`) | **No entries** |
| Nightly backup | **Cron fired unattended at 02:00** — `backups/20260723_020001` created (3.0 MB), retention pruning working, 2 backups retained |

Warnings: none. Recommendations: none.

## 9. Rollback Summary

Rollback remains valid and trivially safe — no production traffic depends on
the domain yet (site not public) and the previous host was left untouched.

- **Previous values:** `@` A → `2.57.91.91`; `api` A → did not exist;
  `www` CNAME unchanged throughout.
- **Procedure (hPanel → DNS Zone):** set `@` A back to `2.57.91.91`; delete the
  `api` A record. No other records were modified, so nothing else to revert.
- **Estimated rollback propagation:** ~5–15 minutes (TTL 300).
- **Triggers:** domain mis-pointed, erroneous change, need for the old host to
  resume serving.
- **Maximum acceptable downtime:** N/A pre-launch — the cutover carried zero
  user-facing risk.

## 10. Stage 17.4 Completion Assessment

| Acceptance criterion | Status |
|---|---|
| `@`, `www`, `api` resolve to `187.77.84.119` | ✅ (21/21 resolvers) |
| TTL confirmed (300) | ✅ |
| Production VPS receiving traffic on the domain | ✅ (SSH-by-hostname lands on `srv1849339`) |
| Docker stack healthy (6/6, no crash loops) | ✅ |
| Health endpoints operational | ✅ |
| No critical errors in logs | ✅ |
| Rollback documented | ✅ |

**Remaining operational risks:** none new. Public HTTP(S) is intentionally
dark until nginx starts; Let's Encrypt issuance is now unblocked because all
three hostnames resolve to this host.

> ✅ **Stage 17.4 COMPLETE.**
> **Proceed to Stage 17.5 — Nginx Reverse Proxy Configuration**, then
> Stage 17.6 — Let's Encrypt issuance (certbot's HTTP-01 challenge will now
> reach this server for all three domains).

---

## Appendix — Execution Log

```bash
# Task 1 — DNS verification (from the VPS, 2026-07-23 09:13:36 UTC)
for srv in nova.dns-parking.com cosmos.dns-parking.com 8.8.8.8 1.1.1.1 9.9.9.9 208.67.222.222 127.0.0.53; do
  for h in vestradetergents.com www.vestradetergents.com api.vestradetergents.com; do
    dig +noall +answer "$h" @"$srv"
  done
done                                          # 21/21 → 187.77.84.119, TTL 300

# Task 2 — hostname reachability (from the ops workstation)
ssh deploy@vestradetergents.com 'hostname'    # srv1849339
ssh deploy@api.vestradetergents.com 'hostname' # srv1849339
curl -m 5 http://vestradetergents.com/        # refused — expected pre-17.5

# Task 3 — stack
systemctl is-active docker                    # active
docker compose -f docker-compose.prod.yml --env-file .env.production ps
docker inspect --format '{{.Name}} {{.RestartCount}} {{.State.Health.Status}}' <6 containers>

# Task 4 — application
docker compose … exec backend curl -fsS http://127.0.0.1:8080/api/v1/health   # 200, all checks true
docker compose … exec frontend wget -qO- http://127.0.0.1:3000/api/health     # healthy
docker compose … exec scheduler php artisan schedule:list

# Task 5 — logs
docker compose … logs --tail=300 backend frontend queue scheduler   # clean
docker compose … logs --tail=100 db redis                           # clean
journalctl -p err -b --no-pager                                     # no entries
tail /var/log/vestra-backup.log                                     # nightly cron fired 02:00
```
