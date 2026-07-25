# RG1 — Security Assessment

## Objective

Confirm that production security controls are in force and effective.

## HTTPS & TLS

| Domain | HTTPS Enforced | HSTS | Valid Cert | TLS 1.2+ | Status |
|--------|----------------|------|------------|----------|--------|
| vestradetergents.com | | | | | |
| www.vestradetergents.com | | | | | |
| api.vestradetergents.com | | | | | |
| admin.vestradetergents.com | | | | | |

## Security Headers

| Header | Expected | vestradetergents.com | api.vestradetergents.com | admin.vestradetergents.com |
|--------|----------|----------------------|--------------------------|----------------------------|
| Strict-Transport-Security | `max-age=31536000; includeSubDomains; preload` | | | |
| Content-Security-Policy | Present | | | |
| X-Frame-Options | `DENY` / `SAMEORIGIN` | | | |
| X-Content-Type-Options | `nosniff` | | | |
| Referrer-Policy | `strict-origin-when-cross-origin` | | | |
| Permissions-Policy | Present | | | |

## Authentication & Sessions

| Control | Verification | Status |
|---------|--------------|--------|
| CSRF tokens | Present on forms and state-changing API requests | |
| Secure cookies | `Secure; HttpOnly; SameSite=Strict` | |
| Session driver | Redis | |
| Session encryption | Enabled | |
| Session domain | `.vestradetergents.com` | |
| Session lifetime | Reasonable | |
| Password reset | Rate limited | |
| Login rate limiting | Active on auth endpoints | |

## API Security

| Control | Verification | Status |
|---------|--------------|--------|
| CORS origins | Restricted to production origins | |
| Trusted proxies | Configured (`*`) | |
| Sanctum stateful domains | Includes storefront and admin | |
| Payment callbacks | Server-side signature verification | |
| Payment webhooks | Signature verified, idempotent | |
| Input validation | Present on API endpoints | |

## Admin Portal Security

| Control | Verification | Status |
|---------|--------------|--------|
| Dedicated subdomain | `admin.vestradetergents.com` | |
| No `/admin` on API domain | Redirects only | |
| Authentication required | All admin routes protected | |
| Force password change | Active for new admins | |
| Role-based access | Filament policies enforced | |
| CSRF on Livewire forms | Tokens validated | |

## Findings

| ID | Finding | Severity | Recommendation | Status |
|----|---------|----------|----------------|--------|
| | | | | |

## Conclusion

- [ ] HTTPS and HSTS enforced.
- [ ] Security headers present.
- [ ] Sessions secured with Redis and encrypted cookies.
- [ ] Authentication and authorization controls active.
- [ ] Admin isolated on dedicated subdomain.
- [ ] Payment callbacks and webhooks verified.
- [ ] No unresolved critical or high security issues.
