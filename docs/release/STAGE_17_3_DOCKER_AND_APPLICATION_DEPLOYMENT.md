# Stage 17.3 — Docker Engine, Compose & Production Application Deployment

**Status:** ✅ COMPLETE — all validation checks passed
**Date:** 2026-07-22 (executed remotely from the ops workstation over SSH)
**Server:** `deploy@srv1849339` (`187.77.84.119`) — Hostinger VPS, Ubuntu 24.04.4 LTS, kernel 6.8.0-136-generic
**Domain:** `vestradetergents.com` (DNS cutover is Stage 17.4)

**Related:** [Stage 17.2 — Server Hardening](STAGE_17_2_SERVER_PROVISIONING_AND_HARDENING.md) ·
[Production Deployment Guide](PRODUCTION_DEPLOYMENT_GUIDE.md) ·
[Stage 16 — Deployment Preparation](STAGE_16_PRODUCTION_DEPLOYMENT_PREPARATION.md)

---

## 1. Executive Summary

Docker Engine and Compose were installed from the official Docker apt
repository, the production deployment structure was created at `/opt/vestra`,
the production environment was generated on-server with fresh secrets, both
application images were built deterministically at commit tag `cbfdc523efc4`,
and the full application stack was deployed and validated: **6/6 containers
healthy, zero restarts, persistence proven across container recreation, ~8% RAM
in use, and no hardening regressions.**

nginx and certbot were deliberately **not** started: DNS still resolves
`vestradetergents.com` to a non-VPS address (Stage 17.4), so certificates
cannot be issued yet and nginx would crash-loop without them. The stack is
exactly where this stage should end — healthy and ready for reverse-proxy/TLS
configuration.

### Scope corrections to the stage brief

- The brief's ASP.NET/Azure environment list (ASPNETCORE_ENVIRONMENT,
  ConnectionStrings, Azure OpenAI/Storage) does not apply — VESTRA is
  **Laravel + Next.js**. The real environment contract comes from
  `docker-compose.prod.yml` and `.env.production.example`.
- The brief's `/opt/vestra/{app,compose,env,…}` layout conflicts with the
  existing CI pipeline (`deploy.yml` runs `cd /opt/vestra` on the cloned repo).
  Kept the repo-clone layout per the Production Deployment Guide; added
  `backups/` and `certbot/{conf,www}/`.

## 2. Prerequisite Validation (before any change)

| Check | Result |
|---|---|
| Docker / Compose | Not installed — clean slate |
| OS / kernel | Ubuntu 24.04.4 LTS / 6.8.0-136-generic |
| Disk | 96 GB total, 95 GB free (2% used) |
| RAM | 7.8 GB (meets the 8 GB recommendation) — **no swap configured** (see §11) |
| CPU | 2 vCPU x86_64 |
| Connectivity | `download.docker.com` reachable (HTTP 302) |
| DNS | `vestradetergents.com` → `2.57.91.91` — **not** the VPS; expected pre-Stage-17.4 |
| Hardening | UFW active (22/80/443), Fail2Ban active |

## 3. Installation Summary

| Component | Version |
|---|---|
| Docker Engine (docker-ce, docker-ce-cli, containerd.io) | **29.6.2** (build dfc4efb) |
| Docker Compose plugin | **v5.3.1** |
| docker-buildx-plugin | installed with Engine |
| Storage driver | overlayfs (cgroup v2, systemd) |

Installed via new idempotent script `scripts/install-docker.sh` (official apt
repository, keyring at `/etc/apt/keyrings/docker.gpg`; skips components already
present). Docker service `active` + `enabled` (starts on boot); `deploy` added
to the `docker` group.

**Images built** (on-server, `docker compose build`, commit-tagged):

| Image | Tag | Size |
|---|---|---|
| `vestra/vestra-backend` | `cbfdc523efc4` | 1.36 GB |
| `vestra/vestra-frontend` | `cbfdc523efc4` | 357 MB |

## 4. Production Directory Structure

| Path | Owner | Mode | Purpose |
|---|---|---|---|
| `/opt/vestra` | `deploy:deploy` | 755 | Repository clone (deployment root, per CI pipeline) |
| `/opt/vestra/.env.production` | `deploy:deploy` | **600** | Production secrets — server only, never committed |
| `/opt/vestra/backups` | `deploy:deploy` | 775 | `scripts/backup.sh` target |
| `/opt/vestra/certbot/conf` | `deploy:deploy` | 775 | Let's Encrypt state (Stage 17.6) |
| `/opt/vestra/certbot/www` | `deploy:deploy` | 775 | ACME webroot (Stage 17.6) |

Docker named volumes (container persistence): `vestra_db-data`,
`vestra_redis-data`, `vestra_backend-storage`.

## 5. Environment Configuration

Created by new idempotent script `scripts/init-production-env.sh` — refuses to
overwrite an existing `.env.production` (verified: second run aborts).

- **Generated on-server (256-bit, `openssl rand`):** `APP_KEY` (base64, 32 B —
  permanent once live), `DB_PASSWORD`, `MYSQL_ROOT_PASSWORD`, `REDIS_PASSWORD`,
  `BOOTSTRAP_ADMIN_PASSWORD`. Never printed, never transmitted, never committed.
- **Production domain values:** `APP_URL=https://api.vestradetergents.com`,
  `FRONTEND_URL=https://vestradetergents.com`,
  `CORS_ALLOWED_ORIGINS=https://vestradetergents.com,https://www.vestradetergents.com`,
  `SESSION_DOMAIN=.vestradetergents.com`,
  `SANCTUM_STATEFUL_DOMAINS=vestradetergents.com,www.vestradetergents.com`,
  `NEXT_PUBLIC_API_URL=https://api.vestradetergents.com/api/v1`,
  `NEXT_PUBLIC_SITE_URL=https://vestradetergents.com`,
  `NEXT_PUBLIC_BACKEND_URL=https://api.vestradetergents.com`,
  `TRUSTED_PROXIES=*`, `IMAGE_TAG=cbfdc523efc4`.
- **Intentionally empty (by design):** `FLUTTERWAVE_*` (4 keys — awaiting live
  credentials), `MAIL_USERNAME`/`MAIL_PASSWORD` (awaiting SMTP selection),
  `NEXT_PUBLIC_CDN_HOST`, `PREVIOUS_TAG`. The stack boots and passes health
  checks without them; payments and mail activate when real credentials are
  added and containers restarted.
- `docker compose … config --quiet` → **VALID** (fail-fast `:?` guards all
  satisfied).

## 6. Compose Configuration Audit

`docker-compose.prod.yml` audited against the production checklist — **no
changes required**:

| Item | Finding |
|---|---|
| Restart policies | All 8 services `unless-stopped` |
| Healthchecks | 7 services (all except the certbot renewal loop) |
| Published host ports | Only nginx `80`/`443`; frontend 3000 and backend 8080 internal-only |
| Persistence | Named volumes for MySQL, Redis (AOF, `everysec`), backend storage |
| Network | Single internal bridge `vestra-network` |
| Logging | json-file, 10 MB × 3 rotation on every service |
| Resource limits | nginx 256M, frontend/backend 1G, queue/scheduler 512M, db 1.5G, redis 640M (~4.4 GB total vs 7.8 GB RAM) |
| Redis policy | `noeviction` + AOF — correct for queue/session backing |
| Migrations | `backend` alone carries `RUN_MIGRATIONS=true`; queue/scheduler cannot race it |
| Env guards | `:?` fail-fast on every required secret |

## 7. Deployment Report

Command: `docker compose -f docker-compose.prod.yml --env-file .env.production
up -d db redis backend queue scheduler frontend`

| Container | Image | Status | Health | Restarts | Ports |
|---|---|---|---|---|---|
| `vestra-db` | mysql:8.0 | Up | healthy | 0 | internal 3306 |
| `vestra-redis` | redis:7-alpine | Up | healthy | 0 | internal 6379 |
| `vestra-backend` | vestra/vestra-backend:cbfdc523efc4 | Up | healthy | 0 | internal 8080 |
| `vestra-queue` | vestra/vestra-backend:cbfdc523efc4 | Up | healthy | 0 | — |
| `vestra-scheduler` | vestra/vestra-backend:cbfdc523efc4 | Up | healthy | 0 | — |
| `vestra-frontend` | vestra/vestra-frontend:cbfdc523efc4 | Up | healthy | 0 | internal 3000 |
| `vestra-nginx` | nginx:1.27-alpine | **not started** | — | — | 80/443 (Stage 17.6) |
| `vestra-certbot` | certbot/certbot | **not started** | — | — | — (Stage 17.6) |

First-launch data (Deployment Guide §6): 6 product images staged into
`storage/app/public/products`, one-time `php artisan db:seed --force` (roles,
permissions, bootstrap admin, categories, products, settings),
`php artisan media:validate` → **"All product media validated successfully."**

> Pending owner action: first admin login at `/admin` (after Stage 17.6) forces
> a password change away from `BOOTSTRAP_ADMIN_PASSWORD`.

## 8. Health Validation

- `GET http://127.0.0.1:8080/api/v1/health` (in-network):
  `{"status":"healthy","checks":{"database":true,"storage":true,"cache":true}}`
- `GET http://127.0.0.1:3000/api/health`: `{"status":"healthy","service":"vestra-frontend"}`
- Scheduler: `auth:cleanup-exchange-tokens` and `sanctum:cleanup-expired` both
  registered, hourly.
- Queue worker: running (`queue:work --tries=3 …`), caches warmed.
- Log scan (200 lines/service, backend/queue/scheduler/frontend): **no
  exceptions, errors, missing-env, or failed entries.**
- Restart loops: none — `RestartCount=0` on all six containers.

## 9. Persistence Validation

Canaries written, then `up -d --force-recreate backend queue scheduler frontend`:

| Canary | Survived recreation |
|---|---|
| File in `storage/app` (backend-storage volume) | ✅ |
| Row in `settings` table (db-data volume) | ✅ |
| Cache key in Redis (redis-data volume, AOF) | ✅ |

Canaries removed after the test. Data survives container recreation.

## 10. Performance Validation

`docker stats --no-stream` (idle stack):

| Container | CPU | Memory | % of limit |
|---|---|---|---|
| vestra-db | 1.1% | 426.8 MiB / 1.465 GiB | 28.5% |
| vestra-backend | 0.01% | 62.7 MiB / 1 GiB | 6.1% |
| vestra-queue | 0.0% | 54.2 MiB / 512 MiB | 10.6% |
| vestra-scheduler | 52.5%* | 54.4 MiB / 512 MiB | 10.6% |
| vestra-frontend | 0.0% | 38.6 MiB / 1 GiB | 3.8% |
| vestra-redis | 0.5% | 3.5 MiB / 640 MiB | 0.5% |

*transient sample during a `schedule:work` tick. Total RSS ≈ 640 MB of 7.8 GB.

`docker system df`: images 2.88 GB, volumes 219 MB, build cache 4.19 GB
(2.46 GB reclaimable — routine `docker builder prune` during maintenance).

## 11. Security Validation

| Check | Result |
|---|---|
| Public listening ports | **22 only**. Loopback-only: 53 (systemd-resolved), 65529 (`monarx-agent`, Hostinger's preinstalled malware agent — provider component, not a container exposure) |
| Container port exposure | 3000/8080/3306/6379 internal-only (compose `expose`, not `ports`) |
| `APP_ENV` / `APP_DEBUG` | `production` / `false` (verified via `config()` in the running container) |
| Secrets in images | None — `APP_KEY`/`PASSWORD`/`SECRET` absent from both images' `Config.Env` |
| Non-root containers | Frontend runs as `nextjs`; backend php-fpm master is root (required to spawn/drop privileges) with **workers as `www-data`** — the canonical php-fpm model |
| `.env.production` | `deploy:deploy`, mode `600`, server-only |
| Stage 17.2 hardening | UFW active (22/80/443), Fail2Ban active, SSH unchanged — **no regressions** |

## 12. Issues Encountered

| # | Issue | Severity | Resolution |
|---|---|---|---|
| 1 | `deploy` sudo required a password despite the stage brief claiming passwordless | Blocker | Operator added `/etc/sudoers.d/90-deploy` (NOPASSWD); verified with `sudo -n true` |
| 2 | Remote `master` was 2+ commits behind local — the server would have deployed pre-Phase-15 code | High | Committed Stage 16/17.2 artifacts and pushed; server cloned `63850fd`+ |
| 3 | Scripts lost their executable bit on clone (Windows `core.filemode`) | Low | `git update-index --chmod=+x` committed; also run via `bash <script>` |
| 4 | CI `Backend Media Validation` job fails at *Migrate and seed* on GitHub | Medium | CI does not stage product images before seeding; on-server deploy follows the guide order (images → seed) and succeeded. CI job ordering should be fixed in a later stage |
| 5 | CI `Backend Tests (PHPUnit)` failing on master | Medium | Pre-existing, unrelated to deployment (images build and run green). Flagged for the next engineering stage |
| 6 | `deploy.yml` GitHub Action fails on every push | Expected | `SSH_HOST`/`SSH_USER`/`SSH_KEY` secrets don't exist yet — they are created after this stage per the Deployment Guide §9 |
| 7 | Host has **no swap** | Observation | 7.8 GB RAM vs ~4.4 GB limits is comfortable; consider a small swapfile as OOM insurance in a maintenance window |
| 8 | `monarx-agent` listening on loopback 65529 | Observation | Hostinger-preinstalled security agent; loopback-only. Leave as-is unless policy dictates removal |

## 13. Production Validation Report

| # | Criterion | Result |
|---|---|---|
| 1 | Docker Engine installed (29.6.2, official repo) | **PASS** |
| 2 | Docker Compose installed (v5.3.1) | **PASS** |
| 3 | Docker service enabled + running; `deploy` in docker group | **PASS** |
| 4 | Production directory structure created (`/opt/vestra`, least-privilege) | **PASS** |
| 5 | Environment configured — secrets generated on-server, no placeholders deployed | **PASS** |
| 6 | Compose configuration audited (no defects found) | **PASS** |
| 7 | Images built — release config, commit-tagged, sizes reported | **PASS** |
| 8 | Containers deployed, startup ordering + dependencies respected | **PASS** |
| 9 | Health checks pass; no crash loops; no startup exceptions | **PASS** |
| 10 | Persistence verified across container recreation | **PASS** |
| 11 | Performance acceptable (~640 MB / 7.8 GB RSS) | **PASS** |
| 12 | Security validated; Stage 17.2 hardening intact | **PASS** |

## 14. Final Production Readiness Assessment

**Stage 17.3 is COMPLETE.** The application stack is deployed, healthy,
persistent, and secured on the production VPS. The server is ready for:

1. **Stage 17.4** — DNS cutover (`vestradetergents.com`, `www`, `api` →
   `187.77.84.119`)
2. **Stage 17.5/17.6** — nginx + certbot startup and TLS issuance (blocked on
   DNS), then public smoke tests
3. **Owner actions (parallel):** Flutterwave live keys + SMTP credentials into
   `.env.production`; GitHub Actions secrets (`SSH_HOST=187.77.84.119`,
   `SSH_USER=deploy`, `SSH_KEY`, `DOCKER_USERNAME`, `DOCKER_PASSWORD`,
   `NEXT_PUBLIC_*`) to activate CI/CD; first admin login + forced password
   change once public.

---

## Appendix — Execution Log (commands run on the VPS)

```bash
# Prerequisite validation (read-only scan — output in §2)
command -v docker; docker compose version; df -h /; free -h; nproc; uname -m
lsb_release -ds; curl -fsS -o /dev/null -w '%{http_code}' https://download.docker.com/linux/ubuntu
dig +short vestradetergents.com; sudo ufw status; systemctl is-active fail2ban

# Repository readiness (workstation)
git add docs/release/STAGE_16_*.md docs/release/STAGE_17_2_*.md scripts/provision-server.sh scripts/validate-server.sh
git commit -m "Stage 16 + Stage 17.2 — Production Deployment Preparation & Server Hardening" && git push origin master
git add scripts/install-docker.sh scripts/init-production-env.sh
git commit -m "Stage 17.3 — Docker installation & production environment initialisation scripts" && git push origin master
git update-index --chmod=+x scripts/*.sh && git commit -m "Set executable bit on server scripts" && git push origin master

# Docker installation (scripts/install-docker.sh — official apt repo)
sudo bash /tmp/install-docker.sh
systemctl is-active docker; systemctl is-enabled docker
docker version; docker compose version; docker info

# Directory structure
sudo mkdir -p /opt/vestra && sudo chown deploy:deploy /opt/vestra
git clone https://github.com/mujuzifil/Vestra.git /opt/vestra
cd /opt/vestra && mkdir -p backups certbot/conf certbot/www

# Environment (secrets generated on-server, never displayed)
bash scripts/init-production-env.sh
docker compose -f docker-compose.prod.yml --env-file .env.production config --quiet

# Build (commit-tagged)
git pull --ff-only
sed -i "s|^IMAGE_TAG=.*|IMAGE_TAG=$(git rev-parse --short=12 HEAD)|" .env.production
docker compose -f docker-compose.prod.yml --env-file .env.production build
docker images | grep vestra

# Deploy (nginx/certbot deferred to Stage 17.6)
docker compose -f docker-compose.prod.yml --env-file .env.production up -d db redis backend queue scheduler frontend

# First-launch data
docker compose … exec backend mkdir -p storage/app/public/products
for f in frontend/public/assets/images/products/*.png; do docker cp "$f" vestra-backend:/var/www/html/storage/app/public/products/; done
docker compose … exec backend chown -R www-data:www-data storage/app/public
docker compose … exec backend php artisan db:seed --force
docker compose … exec backend php artisan media:validate

# Health / persistence / performance / security (outputs in §8–§11)
docker compose … ps; docker inspect --format '{{.Name}} {{.RestartCount}}' …
docker compose … exec backend curl -fsS http://127.0.0.1:8080/api/v1/health
docker compose … exec frontend wget -qO- http://127.0.0.1:3000/api/health
docker compose … up -d --force-recreate backend queue scheduler frontend   # persistence test
docker stats --no-stream; docker system df
sudo ss -tlnp; docker top vestra-backend; sudo ufw status
```
