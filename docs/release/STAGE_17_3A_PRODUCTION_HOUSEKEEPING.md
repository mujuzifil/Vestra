# Stage 17.3A — Production Housekeeping & Operational Readiness

**Status:** ✅ COMPLETE — ready for Stage 17.4 (DNS Cutover)
**Date:** 2026-07-22 (executed remotely over SSH)
**Server:** `deploy@srv1849339` (`187.77.84.119`) — Hostinger VPS, Ubuntu 24.04.4 LTS
**Scope guards honored:** no DNS changes, nginx/certbot untouched (still stopped),
no TLS, no application-code edits, no production containers removed, no secrets
rotated or exposed.

**Related:** [Stage 17.3 — Docker & Application Deployment](STAGE_17_3_DOCKER_AND_APPLICATION_DEPLOYMENT.md) ·
[Stage 17.2 — Server Hardening](STAGE_17_2_SERVER_PROVISIONING_AND_HARDENING.md) ·
[Backup & Restore Guide](BACKUP_AND_RESTORE_GUIDE.md)

---

## 1. Executive Summary

All pre-public housekeeping is done and evidence-backed. The highlights:

- **Swap:** 2 GB swapfile live, fstab-persistent, and **proven across a real
  reboot** — the whole stack (Docker + 6 containers) auto-recovered healthy
  with zero intervention.
- **Backups:** test backup taken (35 tables, storage, env, manifest), and a
  **restore was rehearsed** in a throwaway MySQL container (35/35 tables,
  6/6 products). Nightly cron installed.
- **CI failures root-caused with certainty** by replicating CI on the VPS —
  both are pipeline configuration issues, not application defects. The suite is
  **120/120 green** once the CI jobs stage product images.
- **Security:** Stage 17.2 validator re-run as root: **33/33 PASS**; Docker
  layer clean.
- **Docker maintenance:** 2.68 GB reclaimed; only production images/volumes remain.

**Verdict: READY for Stage 17.4 — DNS Configuration & Cutover.**

---

## 2. Task 1 — Swap Configuration Report

| Item | Before | After |
|---|---|---|
| RAM | 7.8 GB (1.4 GB used) | 7.8 GB (1.1 GB used) |
| Swap | **0 B** | **2.0 GB** (`/swapfile`, priority -2) |

```bash
sudo fallocate -l 2G /swapfile && sudo chmod 600 /swapfile
sudo mkswap /swapfile && sudo swapon /swapfile
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
```

- fstab entry (single, verified): `/swapfile none swap sw 0 0`
- Swappiness: `vm.swappiness = 10` (set in Stage 17.2; unchanged — correct for
  a database/Redis host: swap is OOM insurance, not working memory)
- **Reboot validation (21:24 UTC):** system rebooted; after boot —
  `swapon --show` → `/swapfile 2G`; all 6 containers `Up (healthy)` within
  2 minutes via `unless-stopped`; backend health endpoint returned
  `{"status":"healthy","checks":{"database":true,"storage":true,"cache":true}}`.

**Result: PASS** — swap persistent, auto-recovery of the full stack proven.

## 3. Task 2 — Backup Readiness Report: **PASS**

**Script audit** (`scripts/backup.sh`, `scripts/restore.sh`) — both sound:
single-transaction mysqldump; dump verified for completion marker, table count
and gzip integrity *before* being trusted; storage tarball; env backup mode
600; manifest with image tag + git commit; 30-day retention; restore verifies
the archive, snapshots current state first, stops writers, and health-gates
completion. Credentials read from the env file, never on the command line.

**Live test backup** — `/opt/vestra/backups/20260722_212804` (3.0 MB):
`database.sql.gz` (35 tables), `storage.tar.gz` (6 product images),
`env.production.bak` (600), `MANIFEST.txt`; tree mode 700.

**Restore validation (temporary location)** — throwaway `mysql:8.0` container:
dump restored → **35/35 tables**, **6/6 product rows**; container destroyed.
Storage tarball extracted to a temp dir → **6/6 images**; temp dir removed.
Production data untouched throughout.

**Scheduling** — nightly cron installed for `deploy` (per Deployment Guide §8):

```
0 2 * * * cd /opt/vestra && ./scripts/backup.sh /opt/vestra/backups >> /var/log/vestra-backup.log 2>&1
```

**Known gap (documented, not blocking):** backups live on the same VPS. A
server-loss event takes the backups with it. Off-site replication needs an
external target (object storage / second host) — owner decision; see §10.

| Check | Result |
|---|---|
| Database backup strategy | PASS (mysqldump, verified, nightly) |
| Application storage backup | PASS (tarball of `storage/app/public`) |
| Environment backup | PASS (mode 600, in backup tree) |
| Docker volumes | PASS (covered via dump + storage tar; raw volume copies unnecessary) |
| Retention policy | PASS (30 days, `BACKUP_RETENTION_DAYS`) |
| Restore documentation | PASS ([Backup & Restore Guide](BACKUP_AND_RESTORE_GUIDE.md) + DR checklist §9) |
| Restore rehearsed | PASS (this stage) |
| Off-site replication | **GAP — recommendation open** |

## 4. Task 3 — GitHub Deployment Readiness Report

Required secrets, derived from `.github/workflows/deploy.yml`:

| Secret | Required by | Value to set | Status |
|---|---|---|---|
| `SSH_HOST` | deploy + health-gate jobs | `187.77.84.119` | ☐ Operator to confirm |
| `SSH_USER` | deploy + health-gate jobs | `deploy` | ☐ Operator to confirm |
| `SSH_KEY` | deploy + health-gate jobs | private key of the pair whose public key is in `deploy`'s `authorized_keys` | ☐ Operator to confirm |
| `DOCKER_USERNAME` | build job (registry login + image namespace) | Docker Hub username | ☐ Operator to confirm |
| `DOCKER_PASSWORD` | build job | Docker Hub **access token** (not account password) | ☐ Operator to confirm |
| `NEXT_PUBLIC_API_URL` | frontend build arg | `https://api.vestradetergents.com/api/v1` | ☐ Operator to confirm |
| `NEXT_PUBLIC_SITE_URL` | frontend build arg | `https://vestradetergents.com` | ☐ Operator to confirm |
| `NEXT_PUBLIC_BACKEND_URL` | frontend build arg | `https://api.vestradetergents.com` | ☐ Operator to confirm |

Optional: none required beyond the above. (`BACKUP_RETENTION_DAYS` is read from
`.env.production`, not GitHub.)

**Limitation, stated plainly:** secret *presence* cannot be validated from this
session — GitHub's secrets API requires a repo-admin token and no `gh` CLI or
token exists here. The matrix above is the authoritative requirement list;
presence must be confirmed by the operator in repo **Settings → Secrets and
variables → Actions**. Until then the `Deploy to Production` workflow keeps
failing — expected, harmless.

**Pipeline readiness:** once the 8 secrets exist, the pipeline is end-to-end
operable — the server side it deploys to is fully live and healthy.

## 5. Task 4 — CI Audit Report

Method: GitHub Actions API for job/step status; then **definitive root-cause by
replicating CI on the VPS** in a throwaway PHP 8.4 container (composer install
with dev deps, CI's exact step sequence). Scratch environment destroyed after.

| Job | Symptom | Root cause (proven) | Classification | Severity |
|---|---|---|---|---|
| Backend Tests (PHPUnit) | `Run PHPUnit` fails | Tests that run `DatabaseSeeder` die in `ProductSeeder` — "Product image missing" — because **the job never stages product images**. Reproduced: 63 failed/57 passed without images; **120/120 passed (897 assertions) with images staged** | **Pipeline issue** (missing step in `backend-tests`) | Medium — false-red hides real regressions |
| Backend Media Validation | `Migrate and seed` fails | Two chained pipeline defects, both reproduced: **(1)** `create_permission_tables` migration dies on `RedisException` — `.env.example` sets `CACHE_STORE=redis` with host default `redis`, unresolvable in CI; **(2)** with cache/session/queue forced to array/sync, migrate+seed succeed but the next artisan command (`storage:link`) aborts via the production bootstrap-password guard — the job inherits `APP_ENV=production` from `.env.example` and seeds the default-password admin | **Pipeline issue** ×2 (job env) | Medium |
| Docker Production Build | skipped | Consequence of the two failures above (`needs: [backend-tests, frontend]`) | Knock-on | — |
| Deploy to Production | fails | Missing repository secrets (Task 3) | **Infrastructure issue** — resolves when secrets are set | Low (expected pre-launch) |
| Frontend Build & Typecheck | passes | — | — | — |
| Backend Code Style | advisory pass | Pint backlog, `continue-on-error` by design ([Known Issues](KNOWN_ISSUES.md)) | Existing, accepted | Low |

**Recommendations (next engineering stage — no production containers touched):**

1. `backend-tests` job: add the image-staging step the media job already has
   (`mkdir -p storage/app/public/products && cp ../frontend/public/assets/images/products/*.png storage/app/public/products/`).
2. `backend-media` job: add env `APP_ENV: local`, `CACHE_STORE: array`,
   `SESSION_DRIVER: array`, `QUEUE_CONNECTION: sync`.
3. Set the 8 deploy secrets (Task 3).
4. Until 1–2 land, treat red CI on master as expected — do **not** treat it as
   an application regression; the production deployment is unaffected (images
   build and run green, suite is 120/120 in a correct environment).

## 6. Task 5 — Docker Maintenance Report

| Metric | Before | After |
|---|---|---|
| Disk used (`/`) | 9.8 GB | **7.5 GB** (8%) |
| Build cache | 4.19 GB (2.46 GB reclaimable) | 1.73 GB (0 reclaimable) |
| Volumes | 4 (incl. restore-test leftover) | **3 — production only** |

- `docker builder prune -f` → **2.459 GB reclaimed**
- `docker volume prune -f` → **215.8 MB** (only the throwaway restore-test
  volume; production volumes `vestra_db-data`, `vestra_redis-data`,
  `vestra_backend-storage` untouched and active)
- CI-audit scratch (`/tmp/ci-audit`, `vestra-ci-php` image) removed after Task 4.
- No production images or containers touched.

**Recommendation:** `docker builder prune -f` monthly, or after each on-server
build; the GitHub Actions pipeline already prunes images older than 72 h on
deploy.

## 7. Task 6 — Security Verification Report: **PASS**

Stage 17.2 validator re-run **as root** (required for ufw/fail2ban checks):
**33/33 PASS** — OS, UTC+NTP, deploy user/sudo/keys, all 7 effective sshd
hardening directives, UFW active with 22/80/443 + IPv6, Fail2Ban jail active,
unattended-upgrades, swappiness, nofile, journald, and the exposure check.

Validator improvement committed this stage: the port check now counts only
**public-interface** listeners (loopback-only services — systemd-resolved :53,
Hostinger `monarx-agent` :65529 — are not exposures) and accepts the post-17.6
state (80/443 published). Re-running it as non-root previously produced
misleading FAILs; usage now documents `sudo`.

Docker-layer checks:

| Check | Result |
|---|---|
| Docker socket | `srw-rw---- root:docker` — PASS |
| Public listening ports | 22 only — PASS |
| Container port bindings | none on host (3000/8080/3306/6379 internal `expose` only) — PASS |
| `APP_ENV` / `APP_DEBUG` | `production` / `false` — PASS |
| Debug endpoints | `/telescope` → 404, `/_debugbar` → 404 — PASS |
| `.env.production` | `-rw------- deploy:deploy` — PASS |
| Container users | frontend `nextjs`; php-fpm workers `www-data` — PASS |
| Fail2Ban | jail active, 0 bans (no attacks yet — pre-public) — PASS |

## 8. Task 7 — Monitoring Readiness Report

Available now (verified live):

| Primitive | State |
|---|---|
| Docker logs | json-file, 10 MB × 3 rotation on **all 6 services** |
| Laravel logs | `LOG_CHANNEL=stderr` → `docker logs vestra-backend` (verified) |
| Next.js logs | `docker logs vestra-frontend` (verified) |
| Health endpoints | `/api/v1/health` (db+storage+cache detail) and `/api/health` — both live |
| Restart policies | `unless-stopped` everywhere; **reboot-proven** |
| Backup log | `/var/log/vestra-backup.log` (cron output) |

Missing — recommendations (not blocking, most need the public endpoint first):

1. **External uptime monitor** on `https://api.vestradetergents.com/api/v1/health`
   alerting on non-200 — configure right after Stage 17.6 (Go-Live Phase 7).
2. **Host metrics alerts** in the Hostinger panel (CPU/disk/memory) — free,
   5 minutes.
3. **Certificate-expiry watch** — Let's Encrypt renews automatically; an
   external monitor that also checks cert days-remaining is cheap insurance.
4. Manual check commands documented in the Operations Runbook; a weekly
   `docker system df` glance catches disk creep early.

## 9. Task 8 — Disaster Recovery Checklist

Scenario: total server loss. Everything needed exists off-box **except** the
backup tree (§3 gap) — with off-site replication in place, RTO ≈ **1–2 h**.

| # | Step | Depends on | ~Time |
|---|---|---|---|
| 1 | Provision new VPS (Ubuntu 24.04), note new IP | Provider access | 15 min |
| 2 | Harden: run `scripts/provision-server.sh` (+ `HARDEN_SSH_CONFIRM=yes` after key check) | Operator SSH public key | 15 min |
| 3 | Install Docker: `sudo scripts/install-docker.sh` | Step 2 | 5 min |
| 4 | Clone repo to `/opt/vestra` (deploy-owned) | GitHub access | 5 min |
| 5 | Restore `.env.production` from the latest backup tree (`env.production.bak`) — **APP_KEY is the critical dependency**: without it, all settings encrypted at rest are unrecoverable | Backup tree (off-box copy) | 5 min |
| 6 | Build images (`docker compose build`) or pull by tag if using the registry pipeline | Steps 3–5 | 20–40 min |
| 7 | `up -d db redis backend queue scheduler frontend` | Step 6 | 5 min |
| 8 | Restore database: `FORCE_RESTORE=true ./scripts/restore.sh backups/<latest>` | Step 7 | 10 min |
| 9 | Verify media: `exec backend php artisan media:validate` | Step 8 | 2 min |
| 10 | DNS: point `@`, `www`, `api` to the new IP (TTL 300 makes this fast) | Registrar access | 5 min + propagation |
| 11 | TLS: standalone certbot issuance (Deployment Guide §5), start nginx+certbot | Step 10 | 15 min |
| 12 | Verify: health endpoints, purchase journey, webhook; reinstall backup cron | Step 11 | 15 min |

**Dependencies to keep off-box at all times:** GitHub repo (already off-box),
latest backup tree (off-site — pending §3 gap), `APP_KEY` (in the env backup —
treat as crown jewel), registrar + Hostinger credentials, Flutterwave dashboard
access.

## 10. Production Readiness Assessment

| Acceptance criterion | Status |
|---|---|
| Swap configured and persistent (reboot-proven) | ✅ |
| Backup strategy validated (backup + restore rehearsed, cron installed) | ✅ |
| GitHub deployment readiness documented (secrets matrix) | ✅ (presence = operator action) |
| CI issues documented (root causes proven) | ✅ |
| Docker maintenance completed (2.68 GB reclaimed) | ✅ |
| Security revalidated (33/33 + Docker layer) | ✅ |
| Monitoring reviewed (primitives verified, gaps recommended) | ✅ |
| Disaster recovery documented | ✅ |
| Final readiness assessment | ✅ |

### Remaining operational risks

1. **Backups are on-box only** — the one real gap. Off-site replication should
   be decided before or immediately after go-live (any S3-compatible target or
   a second host; `backup.sh` output is a self-contained directory, easy to sync).
2. **CI red on master** until the two pipeline fixes land — cosmetic for
   deployment, but it hides signal; fix early in the next engineering stage.
3. **Deploy secrets unset** — automated deploys can't run until Task 3 is done;
   manual deploy path (`scripts/deploy.sh --build`) works regardless.
4. Flutterwave live keys + SMTP still pending (owner action from Stage 16) —
   payments/mail activate when added to `.env.production`; not a Stage 17.4
   blocker.

### Recommended next actions (in order)

1. **Stage 17.4 — DNS cutover** (`@`, `www`, `api` → `187.77.84.119`, TTL 300).
2. Stage 17.5/17.6 — start nginx + certbot, issue certificates, public smoke tests.
3. Set the 8 GitHub secrets; confirm a green `Deploy to Production` run.
4. Owner: Flutterwave live keys + SMTP credentials into `.env.production`;
   first admin login + forced password change.
5. Schedule: off-site backup replication; the two CI pipeline fixes.

> **Final recommendation: the system is READY for Stage 17.4 — DNS
> Configuration & Cutover.** nginx and Let's Encrypt can proceed the moment DNS
> resolves to this host.

---

## Appendix — Execution Log (chronological, on the VPS unless noted)

```bash
# Task 1 — swap
free -h; swapon --show
sudo fallocate -l 2G /swapfile; sudo chmod 600 /swapfile
sudo mkswap /swapfile; sudo swapon /swapfile
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
sysctl vm.swappiness                      # 10 (from Stage 17.2)
sudo shutdown -r +0                        # persistence validation
# after reboot:
swapon --show; free -h; docker ps; curl -fsS http://127.0.0.1:8080/api/v1/health

# Task 2 — backups
cd /opt/vestra && ./scripts/backup.sh /opt/vestra/backups
docker run -d --name restore-test -e MYSQL_ROOT_PASSWORD=restoretest -e MYSQL_DATABASE=vestra_restore mysql:8.0
gunzip -c backups/20260722_212804/database.sql.gz | docker exec -i -e MYSQL_PWD=restoretest restore-test mysql -u root vestra_restore
docker exec -e MYSQL_PWD=restoretest restore-test mysql -u root -N -B \
  -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='vestra_restore';"   # 35
docker exec -e MYSQL_PWD=restoretest restore-test mysql -u root -N -B -e "SELECT COUNT(*) FROM vestra_restore.products;"  # 6
docker rm -f restore-test
tar -xzf backups/20260722_212804/storage.tar.gz -C /tmp/restore-check      # 6 files
echo '0 2 * * * cd /opt/vestra && ./scripts/backup.sh /opt/vestra/backups >> /var/log/vestra-backup.log 2>&1' | crontab -
crontab -l; sudo touch /var/log/vestra-backup.log && sudo chown deploy:deploy /var/log/vestra-backup.log

# Task 4 — CI audit (workstation API + VPS CI-parity replication)
curl -fsS "https://api.github.com/repos/mujuzifil/Vestra/actions/runs?per_page=10"   # job/step status
# throwaway php:8.4-cli image (pdo_mysql, pdo_sqlite, gd, zip, bcmath, intl, pcntl, opcache, exif, redis)
# replicate backend-tests:  php artisan test            → 63 failed / 57 passed (images missing)
# stage product images, rerun                          → 120 passed (897 assertions)
# replicate backend-media:  migrate                     → FAIL create_permission_tables (RedisException: host 'redis')
# with CACHE_STORE=array etc: migrate+seed OK, next artisan aborts (bootstrap-password guard, APP_ENV=production)
# scratch destroyed: sudo rm -rf /tmp/ci-audit; docker rmi vestra-ci-php

# Task 5 — docker maintenance
docker system df; docker builder prune -f              # 2.459 GB
docker volume prune -f                                 # 215.8 MB (restore-test volume only)

# Task 6 — security
sudo ./scripts/validate-server.sh                      # 33/33 PASS
stat -c '%A %U:%G' /var/run/docker.sock                # srw-rw---- root:docker
curl /telescope, /_debugbar → 404; docker ps (no host bindings); ss -tlnp; fail2ban-client status sshd

# Task 7 — monitoring
docker inspect --format '…LogConfig…' <all 6>          # json-file 10m×3 everywhere
docker logs vestra-backend --tail 2; docker logs vestra-frontend --tail 2

# Repository (workstation)
git update-index --chmod=+x scripts/backup.sh scripts/deploy.sh scripts/restore.sh scripts/rollback.sh
git commit -m "Set executable bit on operations scripts" && git push
git commit -m "validate-server.sh: root-required note, public-only port exposure check" && git push
```
