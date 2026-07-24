# RC1 — Security Validation Report

## Objective

Confirm that production security controls remain intact after deployment.

## HTTPS & HSTS

| Domain | HTTPS Enforced | HSTS Header | Status |
|--------|----------------|-------------|--------|
| vestradetergents.com | ✅ | `max-age=31536000; includeSubDomains; preload` | |
| api.vestradetergents.com | ✅ | Present | |
| admin.vestradetergents.com | ✅ | Present | |

## Security Headers

| Header | Expected | vestradetergents.com | api.vestradetergents.com | admin.vestradetergents.com |
|--------|----------|----------------------|--------------------------|----------------------------|
| Content-Security-Policy | Present | | | |
| X-Frame-Options | `DENY` / `SAMEORIGIN` | | | |
| X-Content-Type-Options | `nosniff` | | | |
| Referrer-Policy | `strict-origin-when-cross-origin` | | | |
| Permissions-Policy | Present | | | |

## Authentication & Session Security

| Control | Verification | Status |
|---------|--------------|--------|
| CSRF tokens | Forms include `_token` | |
| Secure cookies | `Secure; HttpOnly; SameSite=Strict` | |
| Session driver | Redis | |
| Session encryption | Enabled | |
| Session domain | `.vestradetergents.com` | |
| Admin isolation | Separate subdomain | |
| Password reset | Rate limited | |
| Login rate limiting | 5 requests/burst | |

## API Security

| Control | Verification | Status |
|---------|--------------|--------|
| CORS origins | Restricted to production domains | |
| Trusted proxies | Configured | |
| Sanctum stateful domains | Includes website and admin | |
| Payment webhooks | Signature verified | |
| Callback validation | Server-side verification | |

## Admin Security

| Control | Verification | Status |
|---------|--------------|--------|
| Admin only on `admin.vestradetergents.com` | No `/admin` on API domain | |
| Force password change middleware | Active for new admins | |
| Authentication middleware | Required for all admin routes | |
| CSRF | Required for Livewire/Filament forms | |

## Findings

| Finding | Severity | Recommendation |
|---------|----------|----------------|
| | | |

## Conclusion

- [ ] HTTPS enforced on all domains.
- [ ] HSTS and security headers present.
- [ ] Sessions secured with Redis and encrypted cookies.
- [ ] Admin isolated on dedicated subdomain.
- [ ] API security controls active.
