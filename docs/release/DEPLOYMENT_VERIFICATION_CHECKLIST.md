# VESTRA — Deployment Verification Checklist

Run after **every** deployment. Roughly 15 minutes.

```bash
DC='docker compose -f /opt/vestra/docker-compose.prod.yml --env-file /opt/vestra/.env.production'
```

**Release:** ____________ **Date:** ____________ **Operator:** ____________

---

## 1. Services

```bash
$DC ps
```

- [ ] All eight report `healthy`: nginx, frontend, backend, queue, scheduler, db, redis, certbot
- [ ] No container restarting repeatedly (`$DC ps` twice, a minute apart)

> The `queue` container restarting roughly hourly is **expected** —
> `--max-time=3600` bounds worker memory by design.

## 2. Health endpoints

```bash
curl -fsS https://api.vestra.com/api/v1/health | jq
curl -fsS https://api.vestra.com/api/v1/health/ready | jq
curl -fsS https://vestra.com/api/health
```

- [ ] `/health` → 200, `database`, `storage`, `cache` all `true`
- [ ] `/health/ready` → 200, `database`, `cache`, `redis` all `true`
- [ ] Frontend health → 200

## 3. TLS and headers

```bash
curl -sI http://vestra.com | head -1
curl -sI https://vestra.com | grep -iE "strict-transport|x-frame|x-content|content-security"
echo | openssl s_client -connect vestra.com:443 2>/dev/null | openssl x509 -noout -dates
```

- [ ] HTTP redirects 301 → HTTPS
- [ ] HSTS present with `max-age=31536000`
- [ ] `X-Frame-Options`, `X-Content-Type-Options`, CSP present
- [ ] Certificate valid, > 21 days remaining
- [ ] No header appears **twice** (nginx and the middleware both emitting)

## 4. Storefront

- [ ] `https://vestra.com` loads
- [ ] Product listing renders **with images** (blank images ⇒ `storage:link` or seeding)
- [ ] Product detail page loads
- [ ] Browser console: no CORS errors
- [ ] Network tab: XHRs go to `api.vestra.com`, **never** `localhost:8000`

> A request to `localhost:8000` means the frontend image was built without
> `NEXT_PUBLIC_API_URL`. Rebuilding is the only fix — restarting will not help.

## 5. API

```bash
curl -s -o /dev/null -w "%{http_code}\n" https://api.vestra.com/api/v1/products
curl -s -o /dev/null -w "%{http_code}\n" https://api.vestra.com/api/v1/categories
curl -s -o /dev/null -w "%{http_code}\n" https://api.vestra.com/api/v1/orders          # 401
curl -s -o /dev/null -w "%{http_code}\n" -H "Accept: application/json" \
     https://api.vestra.com/api/v1/orders                                              # 401
```

- [ ] Public endpoints → 200
- [ ] Protected endpoint → **401 with and without** an `Accept` header
- [ ] CORS allows the storefront origin:
      `curl -sI -H "Origin: https://vestra.com" https://api.vestra.com/api/v1/products | grep -i access-control-allow-origin`
- [ ] A foreign origin does **not** receive its own origin echoed back

## 6. Authentication

- [ ] Customer registration succeeds
- [ ] Customer login succeeds, session persists across navigation
- [ ] Logout invalidates the session
- [ ] Admin login at `https://api.vestra.com/admin` succeeds
- [ ] Admin password is **not** the shipped default
- [ ] Rate limiting engages after repeated failed logins
- [ ] `audit_logs` records the login attempts **with real client IPs**, not nginx's

```bash
$DC exec db mysql -u root -p"$MYSQL_ROOT_PASSWORD" vestra -e \
  "SELECT created_at, action, ip_address FROM audit_logs ORDER BY created_at DESC LIMIT 10;"
```

> Every row showing the same internal address means `TRUSTED_PROXIES` is not
> reaching the application — rate limits are then shared across all users.

## 7. Commerce

- [ ] Add to cart, update quantity, remove item
- [ ] Cart survives a page reload
- [ ] Checkout accepts a delivery address
- [ ] Order is created with status `pending`
- [ ] Payment redirect to Flutterwave works
- [ ] Test payment completes and returns to the site
- [ ] Webhook received: order advances to `paid`
- [ ] Product stock decremented **exactly once** (verify `stock_decremented`)
- [ ] Order confirmation email queued and sent
- [ ] Order visible in the admin panel

```bash
$DC exec db mysql -u root -p"$MYSQL_ROOT_PASSWORD" vestra -e \
  "SELECT id, status, payment_status, stock_decremented FROM orders ORDER BY id DESC LIMIT 5;"
```

## 8. Queue and scheduler

```bash
$DC exec redis redis-cli -a "$REDIS_PASSWORD" --no-auth-warning llen queues:default
$DC exec backend php artisan queue:failed
$DC exec scheduler php artisan schedule:list
```

- [ ] Queue depth returns to 0 after activity
- [ ] No unexpected failed jobs
- [ ] Schedule lists `auth:cleanup-exchange-tokens` and `sanctum:cleanup-expired`

## 9. Data integrity

```bash
$DC exec backend php artisan migrate:status | grep -i pending
$DC exec backend php artisan media:validate
```

- [ ] No pending migrations
- [ ] Media validation passes
- [ ] Product, category and settings counts match expectations

## 10. Operations

```bash
df -h /
free -h
ls -la /opt/vestra/backups | tail -3
grep -E '^(IMAGE_TAG|PREVIOUS_TAG)=' /opt/vestra/.env.production
```

- [ ] Disk below 70%
- [ ] Memory headroom adequate
- [ ] A backup exists from the last 26 hours
- [ ] `IMAGE_TAG` matches the intended release
- [ ] `PREVIOUS_TAG` is populated — **rollback has a target**

## 11. Error log review

```bash
$DC logs --since 30m backend | grep -iE "error|exception|critical" | head -30
```

- [ ] No unexpected errors since deployment

---

## Result

- [ ] **PASS** — deployment verified
- [ ] **FAIL** — roll back per the [Rollback Checklist](ROLLBACK_CHECKLIST.md)

Notes:

_______________________________________________

Operator: ______________  Time: ______________
