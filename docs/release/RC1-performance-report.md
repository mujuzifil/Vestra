# RC1 — Performance Report

## Objective

Measure response times for key customer, API, and admin pages after deployment.

## Measurement Method

Use `curl` with timing output:

```bash
curl -s -o /dev/null -w "%{time_total}\n" https://vestradetergents.com/
```

Or use browser DevTools Network tab for frontend routes.

## Customer Website Performance

| Page | URL | TTFB (s) | Total (s) | Status |
|------|-----|----------|-----------|--------|
| Homepage | `/` | | | |
| Products | `/products` | | | |
| Product detail | `/products/{slug}` | | | |
| Cart | `/cart` | | | |
| Checkout | `/checkout` | | | |
| Order confirmation | `/checkout/confirm` | | | |
| Orders | `/account/orders` | | | |
| Order detail | `/account/orders/{id}` | | | |
| Dashboard | `/account` | | | |

## API Performance

| Endpoint | URL | Response Time (s) | Status |
|----------|-----|-------------------|--------|
| Health | `/api/v1/health` | | |
| Products list | `/api/v1/products` | | |
| Product detail | `/api/v1/products/{slug}` | | |
| Cart | `/api/v1/cart` | | |
| Customer orders | `/api/v1/customer/orders` | | |
| Order detail | `/api/v1/customer/orders/{id}` | | |

## Admin Portal Performance

| Page | URL | TTFB (s) | Status |
|------|-----|----------|--------|
| Login | `/login` | | |
| Dashboard | `/` | | |
| Products list | `/products` | | |
| Orders list | `/orders` | | |
| Customers list | `/customers` | | |
| Reports | `/reports` | | |

## Resource Utilisation

```bash
docker compose -f docker-compose.prod.yml --env-file .env.production stats --no-stream
```

| Service | CPU % | Memory / Limit | Status |
|---------|-------|----------------|--------|
| nginx | | | |
| frontend | | | |
| backend | | | |
| queue | | | |
| scheduler | | | |
| db | | | |
| redis | | | |

## Bottlenecks Identified

| Page/Endpoint | Observed Issue | Recommendation |
|---------------|----------------|----------------|
| | | |

## Conclusion

- [ ] Key pages measured.
- [ ] Response times acceptable for production.
- [ ] Resource utilisation within limits.
