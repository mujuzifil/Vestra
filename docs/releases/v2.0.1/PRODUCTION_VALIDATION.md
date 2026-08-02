# VESTRA v2.0.1 — Production Validation Report

## Validation Date

2026-08-01

## Services

```bash
docker compose -f /opt/vestra/docker-compose.prod.yml --env-file /opt/vestra/.env.production ps
```

| Name | Status | Health |
|------|--------|--------|
| vestra-backend | Up 37 minutes | healthy |
| vestra-certbot | Up ~1 hour | running |
| vestra-db | Up ~1 hour | healthy |
| vestra-frontend | Up 37 minutes | healthy |
| vestra-nginx | Up 52 minutes | healthy |
| vestra-queue | Up 36 minutes | healthy |
| vestra-redis | Up ~1 hour | healthy |
| vestra-scheduler | Up 36 minutes | healthy |

No container is restarting.

## Health Endpoints

| Endpoint | HTTP | Result |
|----------|------|--------|
| `https://api.vestradetergents.com/api/v1/health` | 200 | `{"success":true,"data":{"status":"healthy","checks":{"database":true,"storage":true,"cache":true}}}` |
| `https://api.vestradetergents.com/api/v1/health/ready` | 200 | OK |

## TLS & Headers

- `http://vestradetergents.com` → 301 to HTTPS (verified previously).
- HSTS: `max-age=31536000; includeSubDomains; preload` ✓
- `X-Frame-Options: DENY` ✓
- `X-Content-Type-Options: nosniff` ✓
- CSP present ✓

### Certificates

| Domain | Expiry | Status |
|--------|--------|--------|
| vestradetergents.com + www | 2026-10-21 | valid (80 days) |
| api.vestradetergents.com | 2026-10-21 | valid (80 days) |
| admin.vestradetergents.com | 2026-10-23 | valid (82 days) |

## Public Pages

| URL | HTTP | Notes |
|-----|------|-------|
| `https://vestradetergents.com/` | 200 | Home loads |
| `https://vestradetergents.com/about` | 200 | OK |
| `https://vestradetergents.com/products` | 200 | OK |
| `https://vestradetergents.com/products/ecosuit-cleaner` | 200 | Product detail loads |
| `https://vestradetergents.com/distributor` | 200 | OK |
| `https://vestradetergents.com/request-quote` | 200 | OK |
| `https://vestradetergents.com/where-to-buy` | 200 | OK |
| `https://vestradetergents.com/blog` | 200 | OK |
| `https://vestradetergents.com/contact` | 200 | OK |

No `localhost:8000` references detected.

## Business Workflows

### Request a Quote

- [x] Form page loads.
- [ ] Form submission returns success.
- [ ] Customer receives confirmation email.
- [ ] Sales/admin receives notification.

**Result:** POST to `/api/v1/quote-requests` returns HTTP 500 with `{"success":false,"message":"An unexpected error occurred."}`.

**Root cause:** `MAIL_USERNAME` and `MAIL_PASSWORD` are empty in `.env.production`. The application attempts to send the confirmation email via SMTP and fails with `530 5.7.0 Authentication required`. The database transaction rolls back, so no `quote_requests` record is created.

### Become a Distributor

- [x] Form page loads.
- [x] Form submission returns HTTP 201 success.
- [x] `distributor_requests` row created (id=1).
- [ ] Customer receives confirmation email.
- [ ] Admin receives notification.

**Result:** Submission succeeds and persists. Email notification blocked by missing mail credentials.

### Contact

- [x] Form page loads.
- [ ] Form submission returns success.
- [x] `contact_messages` row created (1 test record).
- [ ] Customer receives confirmation email.
- [ ] Admin receives notification.

**Result:** POST to `/api/v1/contact` returns HTTP 500. The contact message is saved before the mail send attempt, so the record exists. Email notification blocked by missing mail credentials.

## Authentication

- [x] Customer registration succeeds (HTTP 201, token issued).
- [ ] Customer login — not retested (registration implies auth endpoint healthy).
- [ ] Password reset — not retested.

## Admin Portal

- [x] Admin login page loads at `https://admin.vestradetergents.com/login` (200).
- [x] Admin user `admin@vestra.com` exists and `must_change_password` is `no`.
- [ ] Full admin login — credentials not available for this validation.

## API

- [x] Public endpoints return 200.
- [x] `/api/v1/products` returns product data.
- [x] `/api/v1/blog/posts` returns 200.
- [x] `/api/v1/public/distributors/stats` returns 200.

## Queue & Scheduler

- [x] Queue monitor: `redis` queue size 0, no pending/reserved jobs.
- [x] Failed jobs: 19 historical failures, all pre-deployment (analytics and mail jobs from July 25–Aug 1).
- [x] `schedule:list` shows expected commands.

## Data Integrity

- [x] No pending migrations.
- [x] Database contains 82 tables including v2 CMS, quote, distributor, and contact tables.

## Infrastructure

- [x] Docker production stack healthy.
- [x] Redis operational.
- [x] MySQL operational.
- [x] Nginx healthy.
- [x] Storage symlink exists.

## Certbot

- [x] Certificates valid and installed.
- [ ] Renewal dry-run passed.

**Result:** Dry-run failed with Let's Encrypt rate limit (`urn:ietf:params:acme:error:rateLimited`). This is transient and caused by repeated validation attempts. Auto-renewal service is running and certificates have 80+ days remaining.

## Performance

- [ ] Lighthouse score ≥ 95 — not run during this deployment window.
- [x] Response times acceptable (public pages < 1s).

## Summary

| Area | Status |
|------|--------|
| Deployment | ✅ Successful |
| Container health | ✅ All healthy |
| Public website | ✅ All pages load |
| API | ✅ Healthy |
| Customer registration | ✅ Works |
| Distributor form | ✅ Saves to database |
| Quote/Contact forms | ⚠️ Blocked by mail config |
| Email delivery | ❌ Not configured |
| SSL | ✅ Valid |
| Admin portal | ✅ Login page loads, admin exists |
| Queue/Scheduler | ✅ Operational |

## Blocking Issues for Full Acceptance

1. **Missing mail credentials** — `MAIL_USERNAME` and `MAIL_PASSWORD` are empty in `.env.production`. This causes quote and contact form submissions to return HTTP 500 and prevents customer/admin email notifications.

   **Action required:** Populate `MAIL_USERNAME` and `MAIL_PASSWORD` (or switch to a working mail driver), then re-run form validation.

## Validation Result

- [ ] **PASS** — full production acceptance blocked by mail configuration.
- [x] **CONDITIONAL PASS** — deployment successful and website is live; email workflows require configuration before final sign-off.

Operator: automated deployment validation  
Time: 2026-08-01 23:45 UTC
