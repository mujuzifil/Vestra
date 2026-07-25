# RC1 — Production Monitoring Report

## Objective

Verify container health, host resources, background jobs, logging, and backups after deployment.

## Container Health

```bash
cd /opt/vestra
docker compose -f docker-compose.prod.yml --env-file .env.production ps
```

| Service | Status | Health | Notes |
|---------|--------|--------|-------|
| nginx | | | |
| frontend | | | |
| backend | | | |
| queue | | | |
| scheduler | | | |
| db | | | |
| redis | | | |
| certbot | | | |

## Resource Utilisation

```bash
docker compose -f docker-compose.prod.yml --env-file .env.production stats --no-stream
```

| Metric | Value | Within Limit |
|--------|-------|--------------|
| CPU usage | | |
| Memory usage | | |
| Disk usage (`df -h`) | | |
| Load average | | |

## Database & Redis

| Check | Command | Expected | Status |
|-------|---------|----------|--------|
| MariaDB ping | `docker compose exec db mysqladmin ping -h localhost -u root -p$MYSQL_ROOT_PASSWORD` | `mysqld is alive` | |
| Redis ping | `docker compose exec redis redis-cli -a $REDIS_PASSWORD --no-auth-warning ping` | `PONG` | |
| Redis memory | `redis-cli INFO memory` | Below maxmemory | |
| Redis evictions | `redis-cli INFO stats` | `evicted_keys:0` | |

## Queue & Scheduler

| Check | Command | Expected | Status |
|-------|---------|----------|--------|
| Queue worker running | `docker compose ps queue` | `Up (healthy)` | |
| Scheduler running | `docker compose ps scheduler` | `Up (healthy)` | |
| Schedule registered | `docker compose exec scheduler php artisan schedule:list` | Jobs listed | |
| Failed jobs | `docker compose exec backend php artisan queue:failed` | Empty or documented | |

## Logs

```bash
docker compose -f docker-compose.prod.yml --env-file .env.production logs --tail=100 backend
docker compose -f docker-compose.prod.yml --env-file .env.production logs --tail=100 frontend
docker compose -f docker-compose.prod.yml --env-file .env.production logs --tail=100 nginx
```

| Log Source | Errors/Warnings | Action |
|------------|-----------------|--------|
| backend | | |
| frontend | | |
| nginx | | |
| queue | | |

## Backups

| Check | Command/Location | Expected | Status |
|-------|------------------|----------|--------|
| Latest backup | `/opt/vestra/backups/` | Recent backup exists | |
| Backup size | | Reasonable | |
| Backup integrity | | Restorable | |

## Log Rotation

```bash
ls -lah /var/lib/docker/containers/*/*-json.log | head
```

| Concern | Status |
|---------|--------|
| No single log file growing unbounded | |
| Docker log driver limits configured | |

## Findings

| Finding | Severity | Action |
|---------|----------|--------|
| | | |

## Conclusion

- [ ] All containers healthy.
- [ ] Database and Redis responsive.
- [ ] Queue worker and scheduler running.
- [ ] No critical log errors.
- [ ] Backups available.
