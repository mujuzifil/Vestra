# VESTRA — Go-Live Checklist

Work top to bottom. Do not proceed past a blocked item.

**Release:** ____________  **Date:** ____________  **Operator:** ____________

---

## Phase 1 — Security prerequisites 🔴 BLOCKING

Go-live is blocked until every item here is signed off. See
[Secret Rotation Checklist](SECRET_ROTATION_CHECKLIST.md).

- [ ] VPS root password rotated; SSH password authentication disabled
- [ ] `admin@vestra.com` password rotated away from `Admin@12345`
- [ ] AWS access key deactivated **and deleted**
- [ ] Git history purge performed, or accepted in writing as residual risk
- [ ] All collaborators re-cloned after any history rewrite
- [ ] `VPS.txt` and `New Text Document.txt` confirmed absent from the working tree
- [ ] No `.env` file is tracked: `git ls-files | grep -E '\.env$'` returns nothing

## Phase 2 — Infrastructure

- [ ] VPS provisioned: ≥ 2 vCPU, ≥ 4 GB RAM, ≥ 40 GB disk
- [ ] Docker Engine 24+ and Compose 2.20+ installed
- [ ] Firewall permits only 22, 80, 443
- [ ] DNS A records for `vestra.com`, `www.vestra.com`, `api.vestra.com` resolve to the VPS
- [ ] DNS propagation confirmed: `dig +short vestra.com`
- [ ] Repository cloned to `/opt/vestra`

## Phase 3 — Configuration

- [ ] `.env.production` created, `chmod 600`, root-owned
- [ ] `APP_KEY` generated fresh and **backed up somewhere durable**
- [ ] `APP_ENV=production`, `APP_DEBUG=false`, `DEBUGBAR_ENABLED=false`
- [ ] `APP_URL` and `FRONTEND_URL` use `https://`
- [ ] `CORS_ALLOWED_ORIGINS` lists every storefront origin, no trailing slash
- [ ] `SESSION_SECURE_COOKIE=true` (valid only because TLS terminates at nginx)
- [ ] `SANCTUM_STATEFUL_DOMAINS` covers all front-end hosts
- [ ] Database, Redis and MySQL root passwords are unique and strong
- [ ] `BOOTSTRAP_ADMIN_PASSWORD` set to a strong value
- [ ] Flutterwave **live** keys set (not test keys)
- [ ] `FLUTTERWAVE_WEBHOOK_SECRET` matches the dashboard secret hash exactly
- [ ] SMTP credentials set and verified
- [ ] `NEXT_PUBLIC_*` values point at production domains
- [ ] `docker compose … config --quiet` passes

## Phase 4 — TLS

- [ ] Certificates issued for both `vestra.com` and `api.vestra.com`
- [ ] `certbot/conf/live/<domain>/fullchain.pem` present for both
- [ ] `certbot` service running
- [ ] Renewal dry run succeeds: `docker compose … exec certbot certbot renew --dry-run`

## Phase 5 — Deploy

- [ ] Images built or pulled
- [ ] `docker compose … up -d` completed
- [ ] Migrations applied: `php artisan migrate:status` shows no pending
- [ ] Reference data seeded (**first deployment only**): `php artisan db:seed --force`
- [ ] `storage` symlink exists
- [ ] All eight services report `healthy`

## Phase 6 — Verification

Full detail in the
[Deployment Verification Checklist](DEPLOYMENT_VERIFICATION_CHECKLIST.md).

- [ ] `https://api.vestra.com/api/v1/health` → 200, all checks true
- [ ] `https://vestra.com/api/health` → 200
- [ ] `http://vestra.com` → 301 to HTTPS
- [ ] HSTS header present
- [ ] Storefront loads, products display **with images**
- [ ] Browser console shows no CORS errors and no calls to `localhost:8000`
- [ ] Full purchase journey completes end to end
- [ ] Payment webhook received and the order advances
- [ ] Admin login works at `/admin`
- [ ] Bootstrap administrator password changed from the `.env` value
- [ ] Queue drains a dispatched job
- [ ] `schedule:list` shows both cleanup commands

## Phase 7 — Operations

- [ ] Nightly backup cron installed
- [ ] First backup taken **and verified**: `./scripts/backup.sh /opt/vestra/backups`
- [ ] Off-site backup replication configured
- [ ] A restore has been rehearsed at least once on a non-production host
- [ ] Uptime monitoring points at `/api/v1/health` and alerts on **503**
- [ ] Disk, memory and certificate-expiry alerts configured
- [ ] `PREVIOUS_TAG` populated so rollback has a target
- [ ] Rollback rehearsed
- [ ] On-call contacts recorded in [Support Handover](SUPPORT_HANDOVER.md)

## Phase 8 — Sign-off

- [ ] [Known Issues](KNOWN_ISSUES.md) reviewed and accepted
- [ ] Stakeholders notified of the go-live window
- [ ] Rollback decision-maker identified and available

| Role | Name | Signature | Date |
|---|---|---|---|
| Engineering | | | |
| Operations | | | |
| Business owner | | | |

---

## First 24 hours

- [ ] T+15 min — health endpoints, error logs, a real purchase
- [ ] T+1 h — queue depth, failed jobs, response times
- [ ] T+4 h — disk and memory trend, payment reconciliation
- [ ] T+24 h — overnight backup ran, scheduler fired, error rate reviewed

## Abort criteria

Roll back immediately if: payments fail or double-charge, authentication is
broken, data loss or corruption is suspected, the 5xx rate exceeds 5%, or a
security control is found not to be in force.

```bash
./scripts/rollback.sh
```
