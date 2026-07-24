# RG1 — Domain Validation

## Objective

Validate DNS resolution, HTTPS responses, HTTP redirects, and security headers for all production domains.

## DNS Resolution

```bash
nslookup vestradetergents.com
nslookup www.vestradetergents.com
nslookup api.vestradetergents.com
nslookup admin.vestradetergents.com
```

| Domain | Expected IP | Resolved IP | Status |
|--------|-------------|-------------|--------|
| vestradetergents.com | 187.77.84.119 | | |
| www.vestradetergents.com | 187.77.84.119 | | |
| api.vestradetergents.com | 187.77.84.119 | | |
| admin.vestradetergents.com | 187.77.84.119 | | |

## HTTPS Response Validation

```bash
curl -sI https://vestradetergents.com
curl -sI https://www.vestradetergents.com
curl -sI https://api.vestradetergents.com
curl -sI https://admin.vestradetergents.com
```

| Domain | HTTP Status | Response | Status |
|--------|-------------|----------|--------|
| vestradetergents.com | 200 | Homepage | |
| www.vestradetergents.com | 301 / 200 | Redirects or serves site | |
| api.vestradetergents.com | 200 | API root / health | |
| admin.vestradetergents.com | 302 → /login or 200 | Login page | |

## HTTP-to-HTTPS Redirect

```bash
curl -sI http://vestradetergents.com
curl -sI http://api.vestradetergents.com
curl -sI http://admin.vestradetergents.com
```

| Domain | Expected | Status |
|--------|----------|--------|
| All | 301 to HTTPS | |

## Legacy Admin Redirects

```bash
curl -sI --max-redirs 0 https://api.vestradetergents.com/admin
curl -sI --max-redirs 0 https://api.vestradetergents.com/admin/login
curl -sI --max-redirs 0 https://api.vestradetergents.com/admin/orders
curl -sI --max-redirs 0 https://api.vestradetergents.com/admin/products
curl -sI --max-redirs 0 https://api.vestradetergents.com/admin/customers
```

| Legacy URL | Expected Redirect Target | Status |
|------------|--------------------------|--------|
| `/admin` | `https://admin.vestradetergents.com` | |
| `/admin/login` | `https://admin.vestradetergents.com/login` | |
| `/admin/orders` | `https://admin.vestradetergents.com/orders` | |
| `/admin/products` | `https://admin.vestradetergents.com/products` | |
| `/admin/customers` | `https://admin.vestradetergents.com/customers` | |

## Security Headers

| Header | vestradetergents.com | api.vestradetergents.com | admin.vestradetergents.com |
|--------|----------------------|--------------------------|----------------------------|
| Strict-Transport-Security | | | |
| Content-Security-Policy | | | |
| X-Frame-Options | | | |
| X-Content-Type-Options | | | |
| Referrer-Policy | | | |

## Findings

| Finding | Severity | Action |
|---------|----------|--------|
| | | |

## Conclusion

- [ ] All domains resolve to the production VPS.
- [ ] HTTPS returns expected responses.
- [ ] HTTP redirects to HTTPS.
- [ ] Legacy admin redirects are correct.
- [ ] Security headers are present.
