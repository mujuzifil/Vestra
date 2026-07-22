# VESTRA — Environment Configuration Guide

Every variable the production stack reads, what it does, and what breaks when it
is wrong.

Template: [`.env.production.example`](../../.env.production.example) →
copy to `/opt/vestra/.env.production`, mode `600`.

---

## The build-time / run-time distinction

This is the single most common source of production incidents in this stack.

| Kind | Variables | When read | To change |
|---|---|---|---|
| **Build-time** | `NEXT_PUBLIC_*` | Compiled into the JS bundle by `next build` | **Rebuild the image** |
| **Run-time** | everything else | Read from container env at boot | Restart the container |

`NEXT_PUBLIC_*` values are substituted into the client bundle as string
literals. Setting them as container environment does nothing to the JavaScript
already shipped to the browser. `docker-compose.prod.yml` passes them as
`build.args`, and `frontend/Dockerfile` fails the build if `NEXT_PUBLIC_API_URL`
is absent — deliberately, so a mistake surfaces at build rather than as a
storefront that silently calls `localhost:8000`.

---

## Required — the stack refuses to start without these

Enforced by `${VAR:?message}` in `docker-compose.prod.yml`.

| Variable | Example | Notes |
|---|---|---|
| `APP_KEY` | `base64:…` | 32 random bytes, base64. **Permanent** — see below |
| `APP_URL` | `https://api.vestra.com` | Backend's own public URL |
| `FRONTEND_URL` | `https://vestra.com` | Used for payment redirects |
| `CORS_ALLOWED_ORIGINS` | `https://vestra.com,https://www.vestra.com` | See below |
| `DB_PASSWORD` | — | `openssl rand -base64 32` |
| `MYSQL_ROOT_PASSWORD` | — | `openssl rand -base64 32` |
| `REDIS_PASSWORD` | — | `openssl rand -base64 32` |
| `NEXT_PUBLIC_API_URL` | `https://api.vestra.com/api/v1` | Build arg |
| `NEXT_PUBLIC_SITE_URL` | `https://vestra.com` | Build arg |
| `NEXT_PUBLIC_BACKEND_URL` | `https://api.vestra.com` | Build arg |

### `APP_KEY` is permanent

Stage 9.1.2 encrypts sensitive settings at rest with this key. Rotating it after
go-live renders every encrypted setting permanently undecryptable. Generate once,
back it up, never change it.

```bash
docker run --rm php:8.4-cli php -r \
  'echo "base64:" . base64_encode(random_bytes(32)) . PHP_EOL;'
```

### `CORS_ALLOWED_ORIGINS`

Read by `backend/config/cors.php`, which falls back
`CORS_ALLOWED_ORIGINS` → `FRONTEND_URL` → `APP_URL`. Leaving it unset collapses
the allowed origin to the **API** domain, so every storefront request is blocked
by the browser.

Comma-separated, scheme included, **no trailing slash**, exact host match —
`https://vestra.com` does not cover `https://www.vestra.com`.

---

## Deployment

| Variable | Purpose |
|---|---|
| `DOCKER_REGISTRY` | Registry namespace, e.g. `docker.io/vestra-ops` |
| `IMAGE_TAG` | Tag currently deployed. `deploy.sh` rewrites this |
| `PREVIOUS_TAG` | Rollback target. `deploy.sh` records the outgoing tag here |
| `APP_DOMAIN` | Bare hostname for the storefront vhost, e.g. `vestra.com` |
| `API_DOMAIN` | Bare hostname for the API vhost, e.g. `api.vestra.com` |

`APP_DOMAIN` / `API_DOMAIN` are substituted into
`nginx/conf.d/vestra.conf.template` at container start and **must** match the
certificate directory names under `certbot/conf/live/`.

---

## Session & authentication

| Variable | Production | Notes |
|---|---|---|
| `SESSION_DOMAIN` | `.vestra.com` | Leading dot shares the cookie across subdomains |
| `SESSION_SECURE_COOKIE` | `true` | **HTTPS only** — see below |
| `SESSION_SAME_SITE` | `strict` | Use `lax` if cross-site POST flows break |
| `SESSION_LIFETIME` | `120` | Minutes |
| `SESSION_DRIVER` | `redis` | Set by compose |
| `SANCTUM_STATEFUL_DOMAINS` | `vestra.com,www.vestra.com,api.vestra.com` | Hosts allowed cookie-based SPA auth |
| `SANCTUM_TOKEN_EXPIRATION` | `10080` | Minutes (7 days); `null` disables expiry |

> **`SESSION_SECURE_COOKIE=true` over plain HTTP breaks login silently.** The
> browser accepts the response and discards the cookie. There is no error — the
> user simply bounces back to the login page. Either serve over HTTPS or set
> `false` for non-TLS testing.

Sanctum expiry is only enforced because the `scheduler` service runs
`sanctum:cleanup-expired` hourly. Without that container, expired tokens
accumulate indefinitely.

---

## Database, cache, queue

Set by compose; override only with reason.

| Variable | Value |
|---|---|
| `DB_CONNECTION` / `DB_HOST` / `DB_PORT` | `mysql` / `db` / `3306` |
| `DB_DATABASE` / `DB_USERNAME` | `vestra` / `vestra` |
| `CACHE_STORE` / `SESSION_DRIVER` / `QUEUE_CONNECTION` | `redis` |
| `REDIS_CLIENT` / `REDIS_HOST` / `REDIS_PORT` | `phpredis` / `redis` / `6379` |

Redis runs `--maxmemory-policy noeviction` with AOF persistence. This is
deliberate: one instance backs cache **and** queue **and** sessions. An LRU
policy would silently discard queued jobs and log users out under memory
pressure. If Redis fills, writes fail loudly — which is recoverable — rather
than quietly losing orders.

`REDIS_CLIENT=phpredis` requires the `redis` PHP extension, installed in
`Dockerfile.prod`.

---

## Payments (Flutterwave)

| Variable | Notes |
|---|---|
| `FLUTTERWAVE_PUBLIC_KEY` | Live key |
| `FLUTTERWAVE_SECRET_KEY` | Live key — server-side only |
| `FLUTTERWAVE_ENCRYPTION_KEY` | Live key |
| `FLUTTERWAVE_WEBHOOK_SECRET` | Must match the dashboard "Secret hash" **exactly** |

A `FLUTTERWAVE_WEBHOOK_SECRET` mismatch rejects every incoming webhook, so
payments complete at Flutterwave but orders never advance. Coverage:
`tests/Feature/Api/V1/WebhookSecurityTest.php`.

Configure the webhook URL in the Flutterwave dashboard as
`https://api.vestra.com/api/v1/payments/callback`. nginx exempts this path from
rate limiting — retries are legitimate and signature verification is the real
gate.

---

## Mail

| Variable | Example |
|---|---|
| `MAIL_MAILER` | `smtp` |
| `MAIL_HOST` / `MAIL_PORT` | `smtp.mailgun.org` / `587` |
| `MAIL_USERNAME` / `MAIL_PASSWORD` | provider credentials |
| `MAIL_FROM_ADDRESS` / `MAIL_FROM_NAME` | `vestradetergent@gmail.com` / `VESTRA` |

---

## Application behaviour

| Variable | Production | Notes |
|---|---|---|
| `APP_ENV` | `production` | Enables HSTS, disables debug output |
| `APP_DEBUG` | `false` | **Never `true`** — leaks stack traces via the API |
| `DEBUGBAR_ENABLED` | `false` | Dev-only since Stage 9.1.2 |
| `TRUSTED_PROXIES` | `*` | See below |
| `LOG_CHANNEL` | `stderr` | Captured by Docker's json-file driver |
| `LOG_LEVEL` | `warning` | `debug` is very noisy in production |
| `BOOTSTRAP_ADMIN_PASSWORD` | strong | Boot aborts while the default is in use |

`TRUSTED_PROXIES=*` is safe **only** because nginx is the sole ingress and
ports 3000/8080 are unpublished. Nothing else can reach the backend to forge
`X-Forwarded-For`. If you ever publish the backend port directly, this must
become an explicit CIDR list.

Without it, Laravel sees nginx's internal IP as the client: rate limiting keys
on one address for all users, audit logs record the proxy, and generated URLs
use `http`.

---

## Verifying configuration

```bash
# Fails loudly on missing required values
docker compose -f docker-compose.prod.yml --env-file .env.production config --quiet

# What the backend actually resolved
docker compose -f docker-compose.prod.yml --env-file .env.production \
  exec backend php artisan about

# Confirm debug is off and the key is set
docker compose -f docker-compose.prod.yml --env-file .env.production \
  exec backend php artisan tinker --execute="
    echo 'env: '   . config('app.env') . PHP_EOL;
    echo 'debug: ' . var_export(config('app.debug'), true) . PHP_EOL;
    echo 'key: '   . (config('app.key') ? 'set' : 'MISSING') . PHP_EOL;
    echo 'cors: '  . implode(',', config('cors.allowed_origins')) . PHP_EOL;
  "
```

After editing `.env.production`, restart and re-warm — the entrypoint rebuilds
the config cache on boot:

```bash
docker compose -f docker-compose.prod.yml --env-file .env.production up -d
```

Changing a `NEXT_PUBLIC_*` value additionally requires
`docker compose … build frontend`.
