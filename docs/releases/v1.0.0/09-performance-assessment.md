# RG1 — Performance Assessment

## Objective

Measure response times and resource usage for key production workloads.

## Measurement Method

```bash
curl -s -o /dev/null -w "total: %{time_total}s, ttfb: %{time_starttransfer}s\n" https://vestradetergents.com/
```

Browser DevTools Network tab was also used for frontend routes.

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

## Container Resource Usage

```bash
docker compose -f docker-compose.prod.yml --env-file .env.production stats --no-stream
```

| Service | CPU % | Memory Used / Limit | Status |
|---------|-------|---------------------|--------|
| nginx | | | |
| frontend | | | |
| backend | | | |
| queue | | | |
| scheduler | | | |
| db | | | |
| redis | | | |

## Host Resource Usage

| Metric | Value | Within Limit |
|--------|-------|--------------|
| CPU load | | |
| Memory usage | | |
| Disk usage (`df -h`) | | |

## Bottlenecks & Recommendations

| Page/Endpoint | Observation | Recommendation |
|---------------|-------------|----------------|
| | | |

## Conclusion

- [ ] Key pages measured.
- [ ] API response times acceptable.
- [ ] Admin portal responsive.
- [ ] Resource utilisation within limits.
- [ ] No critical performance blockers.
