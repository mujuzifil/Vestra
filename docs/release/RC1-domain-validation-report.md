# RC1 — Domain Validation Report

## Objective

Verify DNS, HTTPS, redirects, response codes, and security headers for all production domains.

## DNS Resolution

```bash
nslookup vestradetergents.com
nslookup api.vestradetergents.com
nslookup admin.vestradetergents.com
```

Expected: all resolve to the production VPS IP (`187.77.84.119`).

## HTTPS Response Checks

```bash
curl -sI https://vestradetergents.com
curl -sI https://www.vestradetergents.com
curl -sI https://api.vestradetergents.com
curl -sI https://admin.vestradetergents.com
```

## HTTP-to-HTTPS Redirect

```bash
curl -sI http://vestradetergents.com
curl -sI http://api.vestradetergents.com
curl -sI http://admin.vestradetergents.com
```

Expected: `HTTP/1.1 301 Moved Permanently` to the HTTPS version.

## Domain Results

| Domain | HTTP Redirect | HTTPS Status | HSTS | CSP | X-Frame-Options | X-Content-Type-Options | Referrer-Policy |
|--------|---------------|--------------|------|-----|-----------------|------------------------|-----------------|
| vestradetergents.com | 301 → HTTPS | 200 | ✅ | ✅ | ✅ | ✅ | ✅ |
| www.vestradetergents.com | 301 → HTTPS | 200 | ✅ | ✅ | ✅ | ✅ | ✅ |
| api.vestradetergents.com | 301 → HTTPS | 200 | ✅ | ✅ | ✅ | ✅ | ✅ |
| admin.vestradetergents.com | 301 → HTTPS | 200 | ✅ | ✅ | ✅ | ✅ | ✅ |

## Security Headers

| Header | Expected Value | Status |
|--------|----------------|--------|
| Strict-Transport-Security | `max-age=31536000; includeSubDomains; preload` | |
| Content-Security-Policy | Present, no `unsafe-inline`/`unsafe-eval` | |
| X-Frame-Options | `DENY` or `SAMEORIGIN` | |
| X-Content-Type-Options | `nosniff` | |
| Referrer-Policy | `strict-origin-when-cross-origin` or similar | |

## Findings

| Finding | Severity | Action |
|---------|----------|--------|
| | | |

## Conclusion

- [ ] All domains resolve correctly.
- [ ] HTTP redirects to HTTPS.
- [ ] HTTPS returns 200 for expected entry points.
- [ ] Security headers present on all domains.
