# VESTRA v2.0.0 — Production Validation

## How to Use This Checklist

Run every check from a clean browser session and from a mobile device. Record the result and any anomalies.

## Services

```bash
DC='docker compose -f /opt/vestra/docker-compose.prod.yml --env-file /opt/vestra/.env.production'
$DC ps
```

- [ ] All eight services report `healthy`.
- [ ] No container is restarting repeatedly.

## Health Endpoints

- [ ] `https://api.vestra.com/api/v1/health` → 200, `database`, `storage`, `cache` all `true`
- [ ] `https://api.vestra.com/api/v1/health/ready` → 200, `database`, `cache`, `redis` all `true`
- [ ] `https://vestra.com/api/health` → 200

## TLS & Headers

- [ ] `http://vestra.com` → 301 to HTTPS
- [ ] HSTS header present with `max-age=31536000`
- [ ] `X-Frame-Options`, `X-Content-Type-Options`, CSP present
- [ ] Certificate valid and > 21 days remaining

## Public Pages

- [ ] `https://vestra.com` (Home) loads
- [ ] `https://vestra.com/about` loads
- [ ] `https://vestra.com/products` loads with images
- [ ] `https://vestra.com/products/<slug>` loads
- [ ] `https://vestra.com/distributor` loads
- [ ] `https://vestra.com/request-quote` loads
- [ ] `https://vestra.com/where-to-buy` loads
- [ ] `https://vestra.com/blog` loads
- [ ] `https://vestra.com/blog/<slug>` loads
- [ ] `https://vestra.com/contact` loads
- [ ] No console errors
- [ ] No calls to `localhost:8000`

## Business Workflows

### Request a Quote

- [ ] Form submits successfully
- [ ] `quote_requests` row created
- [ ] Customer receives confirmation email
- [ ] Sales/admin receives notification
- [ ] Filament resource shows the request

### Become a Distributor

- [ ] Form submits successfully
- [ ] `distributor_requests` row created
- [ ] Customer receives confirmation email
- [ ] Admin receives notification
- [ ] Filament resource shows the request

### Contact

- [ ] Form submits successfully
- [ ] `contact_messages` row created
- [ ] Customer receives confirmation email
- [ ] Admin receives notification

## Authentication

- [ ] Customer registration succeeds
- [ ] Customer login succeeds
- [ ] Password reset email delivered
- [ ] Logout invalidates session
- [ ] Admin login at `https://admin.vestra.com/admin` succeeds
- [ ] Admin password is **not** the shipped default

## Admin Portal

- [ ] Dashboard loads
- [ ] Products resource works
- [ ] Blog CMS resources work
- [ ] Distributor requests manageable
- [ ] Quote requests manageable
- [ ] Contact enquiries manageable
- [ ] Notifications deliver

## API

- [ ] Public endpoints return 200
- [ ] Protected endpoints return 401 without token
- [ ] Admin endpoints reject non-admin users
- [ ] CORS allows the storefront origin

## Queue & Scheduler

- [ ] Queue depth returns to 0 after activity
- [ ] No unexpected failed jobs
- [ ] `schedule:list` shows cleanup commands

## Data Integrity

- [ ] No pending migrations
- [ ] `media:validate` passes
- [ ] Product/category counts match expectations

## Performance

- [ ] Lighthouse homepage score ≥ 95 where practical
- [ ] LCP, CLS, INP within acceptable thresholds

## Operations

- [ ] Disk usage below 70%
- [ ] Memory headroom adequate
- [ ] Backup exists from the last 26 hours
- [ ] `IMAGE_TAG` matches `v2.0.0` release
- [ ] `PREVIOUS_TAG` populated

## Result

- [ ] **PASS** — production deployment verified
- [ ] **FAIL** — roll back per `ROLLBACK_PLAN.md`

Operator: ______________  Time: ______________
