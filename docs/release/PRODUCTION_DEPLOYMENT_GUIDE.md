# VESTRA — Production Deployment Guide

Complete procedure for deploying VESTRA to a production VPS from a clean server.

**Related:** [Environment Configuration](ENVIRONMENT_CONFIGURATION_GUIDE.md) ·
[Go-Live Checklist](GO_LIVE_CHECKLIST.md) ·
[Operations Runbook](OPERATIONS_RUNBOOK.md) ·
[Rollback Checklist](ROLLBACK_CHECKLIST.md)

---

## 1. Architecture

```
                    Internet
                       │
                  ┌────▼────┐
                  │  nginx  │  :80 → :443 redirect, TLS termination
                  │  :443   │  the only publicly exposed service
                  └────┬────┘
           ┌───────────┴───────────┐
   vestra.com                api.vestra.com
           │                       │
    ┌──────▼──────┐        ┌───────▼────────┐
    │  frontend   │        │    backend     │
    │  Next.js    │───────▶│ PHP-FPM+nginx  │  SSR calls backend:8080
    │  :3000      │  (SSR) │  :8080         │  over the internal network
    └─────────────┘        └───────┬────────┘
                                   │
                    ┌──────────────┼──────────────┐
              ┌─────▼─────┐  ┌─────▼─────┐  ┌─────▼──────┐
              │   queue   │  │ scheduler │  │  db/redis  │
              │queue:work │  │schedule:  │  │  MySQL 8   │
              │           │  │  work     │  │  Redis 7   │
              └───────────┘  └───────────┘  └────────────┘
```

`backend`, `queue` and `scheduler` all run the **same image** with the same
environment. Only the command differs. `backend` alone carries
`RUN_MIGRATIONS=true` — the replicas must not race it.

Ports 3000 and 8080 are **not** published to the host. All ingress is via nginx.

---

## 2. Prerequisites

| Requirement | Minimum |
|---|---|
| OS | Ubuntu 22.04 LTS or Debian 12 |
| CPU | 2 vCPU |
| RAM | 4 GB (the stack reserves ~4.4 GB of limits; 8 GB recommended) |
| Disk | 40 GB SSD |
| Docker Engine | 24.0+ |
| Docker Compose | 2.20+ |

DNS records, both pointing at the VPS, propagated **before** requesting
certificates:

| Record | Type | Value |
|---|---|---|
| `vestra.com` | A | VPS IP |
| `www.vestra.com` | A | VPS IP |
| `api.vestra.com` | A | VPS IP |

---

## 3. Server preparation

```bash
# Packages
sudo apt update && sudo apt upgrade -y

# Docker
curl -fsSL https://get.docker.com | sudo sh
sudo usermod -aG docker "$USER"   # log out and back in

# Firewall — only SSH and HTTP(S)
sudo ufw allow OpenSSH
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable

# Harden SSH: key-only authentication
sudo sed -i 's/^#*PasswordAuthentication.*/PasswordAuthentication no/' /etc/ssh/sshd_config
sudo sed -i 's/^#*PermitRootLogin.*/PermitRootLogin prohibit-password/' /etc/ssh/sshd_config
sudo systemctl restart sshd
```

Verify: `docker --version && docker compose version`

---

## 4. Deploy the application

```bash
sudo mkdir -p /opt/vestra && sudo chown "$USER":"$USER" /opt/vestra
git clone <repository-url> /opt/vestra
cd /opt/vestra

cp .env.production.example .env.production
chmod 600 .env.production
```

Now populate `.env.production`. Every value is described in the
[Environment Configuration Guide](ENVIRONMENT_CONFIGURATION_GUIDE.md); generate
secrets per the [Secret Rotation Checklist](SECRET_ROTATION_CHECKLIST.md).

Generate `APP_KEY` before the first boot:

```bash
docker run --rm php:8.4-cli php -r \
  'echo "base64:" . base64_encode(random_bytes(32)) . PHP_EOL;'
```

> **`APP_KEY` is permanent.** Stage 9.1.2 encrypts sensitive settings at rest.
> Changing this key after go-live makes every encrypted setting undecryptable.

The compose file fails fast on missing required values — `APP_KEY`, `APP_URL`,
`FRONTEND_URL`, `CORS_ALLOWED_ORIGINS`, `DB_PASSWORD`, `MYSQL_ROOT_PASSWORD`,
`REDIS_PASSWORD` and the three `NEXT_PUBLIC_*` build args. Validate before
starting anything:

```bash
docker compose -f docker-compose.prod.yml --env-file .env.production config --quiet
```

---

## 5. TLS certificates

nginx will not start without certificates, and certbot's HTTP-01 challenge
needs a running web server. Break the cycle with a temporary standalone issuance:

```bash
mkdir -p certbot/conf certbot/www

docker run --rm -p 80:80 \
  -v /opt/vestra/certbot/conf:/etc/letsencrypt \
  -v /opt/vestra/certbot/www:/var/www/certbot \
  certbot/certbot certonly --standalone \
    -d vestra.com -d www.vestra.com \
    --email ops@vestra.com --agree-tos --no-eff-email

docker run --rm -p 80:80 \
  -v /opt/vestra/certbot/conf:/etc/letsencrypt \
  -v /opt/vestra/certbot/www:/var/www/certbot \
  certbot/certbot certonly --standalone \
    -d api.vestra.com \
    --email ops@vestra.com --agree-tos --no-eff-email
```

Confirm both exist:

```bash
ls certbot/conf/live/vestra.com/     # fullchain.pem privkey.pem chain.pem
ls certbot/conf/live/api.vestra.com/
```

Renewal is automatic from here — the `certbot` service retries twice daily
using the webroot challenge, which nginx serves at
`/.well-known/acme-challenge/` over plain HTTP.

> **Rate limits.** Let's Encrypt allows 5 duplicate certificates per week. Add
> `--dry-run` while testing.

---

## 6. First launch

```bash
cd /opt/vestra
docker compose -f docker-compose.prod.yml --env-file .env.production up -d --build
docker compose -f docker-compose.prod.yml --env-file .env.production ps
```

On first start the backend entrypoint waits for MySQL and Redis, runs
`migrate --force`, creates the `storage` symlink, and warms the config, route,
view and event caches. Allow up to 90 seconds.

Watch it:

```bash
docker compose -f docker-compose.prod.yml --env-file .env.production logs -f backend
```

### Stage product images before seeding

`ProductSeeder` aborts if a product's image file is missing, and uploaded media
is intentionally **not** baked into the image — it is runtime data on a volume.
Copy the images in first, or the seed fails partway through:

```bash
docker compose -f docker-compose.prod.yml --env-file .env.production \
  exec backend mkdir -p storage/app/public/products

for f in frontend/public/assets/images/products/*.png; do
  docker cp "$f" vestra-backend:/var/www/html/storage/app/public/products/
done

docker compose -f docker-compose.prod.yml --env-file .env.production \
  exec backend chown -R www-data:www-data storage/app/public
```

### Seed reference data

The production entrypoint **never seeds** — that would overwrite live data. Seed
the first deployment explicitly:

```bash
docker compose -f docker-compose.prod.yml --env-file .env.production \
  exec backend php artisan db:seed --force
```

This creates roles, permissions, the bootstrap administrator, categories,
products and settings. **Run it once, on a new database only.**

Verify afterwards:

```bash
docker compose -f docker-compose.prod.yml --env-file .env.production \
  exec backend php artisan media:validate
```

### Change the bootstrap password

The seeder creates `admin@vestra.com` with `BOOTSTRAP_ADMIN_PASSWORD` and sets
`force_password_change_at`, so the first login is required to set a new
password. Log in at `https://api.vestra.com/admin` and complete that prompt
before doing anything else.

Separately, the application **refuses to boot in production** if the
administrator password is still the shipped default (`Admin@12345`). That guard
targets the publicly-known credential specifically — a strong
`BOOTSTRAP_ADMIN_PASSWORD` boots normally and relies on the forced-change
prompt above.

> If every endpoint returns 500 immediately after seeding and the log shows
> *"Default bootstrap administrator password detected in production"*, then
> `BOOTSTRAP_ADMIN_PASSWORD` was left unset or set to `Admin@12345`. Set a
> strong value, re-run the seeder with `RESET_BOOTSTRAP_ADMIN=true`, and
> restart the backend.

---

## 7. Verify

```bash
curl -fsS https://api.vestra.com/api/v1/health | jq
curl -fsS https://vestra.com/api/health
curl -sI https://vestra.com | grep -i strict-transport
curl -sI http://vestra.com | head -1          # expect 301

# Queue and scheduler are running
docker compose -f docker-compose.prod.yml --env-file .env.production logs --tail=20 queue
docker compose -f docker-compose.prod.yml --env-file .env.production exec scheduler php artisan schedule:list
```

Then work through the
[Deployment Verification Checklist](DEPLOYMENT_VERIFICATION_CHECKLIST.md).

---

## 8. Automated backups

```bash
sudo crontab -e
```

```cron
# VESTRA — nightly backup at 02:00
0 2 * * * cd /opt/vestra && ./scripts/backup.sh /opt/vestra/backups >> /var/log/vestra-backup.log 2>&1
```

Verify the first run the following morning, and read
[Backup & Restore](BACKUP_AND_RESTORE_GUIDE.md).

---

## 9. Subsequent deployments

### Automated (GitHub Actions)

Pushing to `main`/`master` builds commit-tagged images, pushes them, backs up
the VPS, migrates with the new image **before** cutover, restarts, and gates on
the health endpoint. Required repository secrets:

`DOCKER_USERNAME`, `DOCKER_PASSWORD`, `SSH_HOST`, `SSH_USER`, `SSH_KEY`,
`NEXT_PUBLIC_API_URL`, `NEXT_PUBLIC_SITE_URL`, `NEXT_PUBLIC_BACKEND_URL`.

> The three `NEXT_PUBLIC_*` secrets are **build args**. They are compiled into
> the client bundle. Changing the API domain requires a rebuild — restarting
> the container will not pick it up.

### Manual

```bash
cd /opt/vestra
./scripts/deploy.sh <image-tag>   # or: ./scripts/deploy.sh --build
```

`deploy.sh` validates the environment, backs up, records the outgoing tag as
`PREVIOUS_TAG`, migrates before cutover, restarts, and polls health. Any failure
aborts before the running stack is disturbed.

### Rolling back

```bash
./scripts/rollback.sh              # to PREVIOUS_TAG
./scripts/rollback.sh <image-tag>  # to a specific tag
```

See the [Rollback Checklist](ROLLBACK_CHECKLIST.md) — rollback reverts code, not
the database.

---

## 10. Troubleshooting

| Symptom | Cause | Fix |
|---|---|---|
| `APP_KEY is required` on `up` | Empty in `.env.production` | Generate it (§4) |
| Backend restarts repeatedly | DB or Redis unreachable | `logs backend`; check `db`/`redis` health |
| Frontend calls `localhost:8000` | Image built without `NEXT_PUBLIC_API_URL` | Rebuild with the build arg; restarting cannot fix it |
| CORS errors in the browser | `CORS_ALLOWED_ORIGINS` missing or wrong | Must list the storefront origin with scheme, no trailing slash |
| Admin login appears to succeed then bounces | `SESSION_SECURE_COOKIE=true` over plain HTTP | Serve over HTTPS, or set `false` for a non-TLS test |
| Product images 404 | `storage:link` did not run | `exec backend php artisan storage:link --force` |
| Queued work never processes | `queue` container down | `ps`, then `logs queue` |
| Scheduled cleanups never run | `scheduler` container down | `exec scheduler php artisan schedule:list` |
| nginx will not start | Certificates missing | Check `certbot/conf/live/<domain>/` (§5) |
| 502 from nginx | Backend unhealthy | `exec backend curl -fsS localhost:8080/api/v1/health` |
| Webhooks rejected | `FLUTTERWAVE_WEBHOOK_SECRET` mismatch | Must match the dashboard secret hash exactly |
