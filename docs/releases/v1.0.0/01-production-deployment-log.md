# RG1 — Production Deployment Log

## Release Information

| Item | Value |
|------|-------|
| Release | Version 1.0.0 |
| Release gate | RG1 |
| Deployment target | Production VPS |
| Deployment root | `/opt/vestra` |
| Branch / commit | |
| Deployed by | |
| Deployment date | |

## Pre-Deployment State

| Check | Status |
|-------|--------|
| `.env.production` present and `chmod 600` | |
| Required secrets populated | |
| SSL certificates present | |
| Database backup completed | |
| `PREVIOUS_TAG` set for rollback | |

## Deployment Commands Executed

```bash
cd /opt/vestra
git pull origin main
docker compose -f docker-compose.prod.yml --env-file .env.production config --quiet
./scripts/deploy.sh --build
```

## Deployment Output Log

```
(Paste deploy.sh output here)
```

## Post-Deployment Optimisation

```bash
COMPOSE="docker compose -f docker-compose.prod.yml --env-file .env.production"
$COMPOSE exec backend php artisan config:cache
$COMPOSE exec backend php artisan route:cache
$COMPOSE exec backend php artisan view:cache
$COMPOSE exec backend php artisan event:cache
$COMPOSE exec backend php artisan migrate:status
$COMPOSE restart queue
$COMPOSE exec scheduler php artisan schedule:list
$COMPOSE exec nginx nginx -s reload
```

## Migration Status

```
(Paste output of `php artisan migrate:status` here)
```

## Health Checks

| Check | Command | Expected | Actual |
|-------|---------|----------|--------|
| Backend health | `curl -fsS http://localhost:8080/api/v1/health` | 200 OK | |
| Frontend health | `wget --spider http://localhost:3000/api/health` | 200 OK | |
| Nginx health | `wget --spider http://127.0.0.1/nginx-health` | 200 OK | |

## Service Status

```bash
docker compose -f docker-compose.prod.yml --env-file .env.production ps
```

| Service | Status | Health |
|---------|--------|--------|
| nginx | | |
| frontend | | |
| backend | | |
| queue | | |
| scheduler | | |
| db | | |
| redis | | |
| certbot | | |

## Issues & Resolutions

| Issue | Severity | Resolution |
|-------|----------|------------|
| | | |

## Deployment Conclusion

- [ ] Deployment completed successfully.
- [ ] All containers are healthy.
- [ ] Migrations up to date.
- [ ] Caches rebuilt.
- [ ] Queue and scheduler restarted.
- [ ] Rollback target recorded.
