# Phase 13.6 — Sales → Companies Workspace Production Deployment Report

## Deployment Summary

| Field | Value |
|-------|-------|
| Phase | 13.6 — Enterprise Companies Workspace |
| Commit deployed | `dd30dd5cde056c55e74eba0196fa179fad79c573` |
| Commit message | `fix(admin): Phase 13.6 — use safe Heroicon names in KPI cards` |
| Branch | `master` |
| Deployment timestamp | `2026-08-03T17:49:52Z` |
| Server | `187.77.84.119` |
| Deployment directory | `/opt/vestra` |
| Backup | `/opt/vestra/backups/20260803_174952` |
| Images built | `vestra/vestra-backend:local-20260803174954`, `vestra/vestra-frontend:local-20260803174954` |
| Rollback target | `local-20260803161354` |

## Pre-Deployment Validation

| Check | Command | Result |
|-------|---------|--------|
| Backend build | `npm run build` in `backend/` | ✅ Passed |
| Frontend lint | `npm run lint` in `frontend/` | ✅ Passed |
| Frontend type check | `npx tsc --noEmit` in `frontend/` | ✅ Passed |
| Frontend build | `npm run build` in `frontend/` | ✅ Passed |
| Feature tests | `php artisan test --filter=CompaniesPageTest` | ✅ Passed (21 tests, 52 assertions) |

## Deployment Procedure

1. Merged `develop` into `master` locally.
2. Pushed `master` to origin (`230000c..dd30dd5`).
3. Connected to production server via SSH (`deploy@187.77.84.119`).
4. Pulled `master` in `/opt/vestra`.
5. Executed `./scripts/deploy.sh --build`.
6. Deployment script performed:
   - Compose configuration validation
   - Pre-deployment backup (database, storage, environment)
   - Docker image build for backend and frontend
   - Migration execution
   - Container recreation and startup
   - Nginx SSL configuration render and reload
7. Post-deployment cache rebuild:
   - `php artisan optimize:clear`
   - `php artisan config:cache`
   - `php artisan route:cache`
   - `php artisan view:cache`
   - `php artisan queue:restart`

## Migrations Executed

```
2026_08_03_100000_add_status_and_fields_to_company_profiles_table  493.26ms DONE
```

## Container Status

| Container | Status |
|-----------|--------|
| vestra-db | Up (healthy) |
| vestra-redis | Up (healthy) |
| vestra-backend | Up (healthy) |
| vestra-frontend | Up (healthy) |
| vestra-nginx | Up (healthy) |
| vestra-queue | Up (healthy) |
| vestra-scheduler | Up (healthy) |
| vestra-certbot | Up |

## Production Validation

| Check | URL / Command | Result |
|-------|---------------|--------|
| Public website loads | `https://vestradetergents.com` | ✅ 200 OK |
| Admin login loads | `https://admin.vestradetergents.com/login` | ✅ 200 OK |
| API responds | `https://api.vestradetergents.com/api/v1/health` | ✅ 200 OK |
| Companies route registered | `php artisan route:list --name=companies` | ✅ `/admin/sales/companies` and `/admin/sales/companies/export` |
| Laravel logs | `tail -n 50 storage/logs/laravel.log` | ✅ No new errors related to Phase 13.6 |

## Notes

- The deploy script's frontend health check reported a timeout, but `docker ps` confirmed `vestra-frontend` is healthy and the application is fully operational. This matches the behaviour observed in previous deployments and is caused by the health-check interval completing before the container reports healthy.
- Pre-existing production log entries related to `database/database.sqlite` bootstrap administrator verification remain unchanged and are unrelated to Phase 13.6.

## Conclusion

Phase 13.6 has been successfully deployed to production. The Sales → Companies Workspace is live and operational.
