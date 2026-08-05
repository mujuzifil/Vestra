# Phase 13.35 — Testing Evidence

## Command

```bash
docker run --rm --network backend_default --entrypoint php -e DB_HOST=mysql \
  -v "F:\vestra-wt-integrate\backend:/var/www/html" \
  -v "F:\Vestra website\backend\vendor:/var/www/html/vendor" \
  -v "F:\Vestra website\backend\.env:/var/www/html/.env" \
  -w /var/www/html backend-app artisan test --filter=StaffPageTest
```

## Result

**28 passed** (178 assertions)

Coverage includes:
- Staff list, search, filters, KPIs, export URL
- Dynamic roles filter
- Detail drawer + disable action
- Create Staff form sections
- Permission discovery + search
- Create staff persistence + welcome notification
- First-login password change (reuse blocked + flag cleared)
- Admin API roles + permission tree

## Deployment

None. Development branch only.
