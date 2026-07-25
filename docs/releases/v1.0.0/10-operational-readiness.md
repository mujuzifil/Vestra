# RG1 — Operational Readiness

## Objective

Verify monitoring, logging, backups, and operational procedures are ready for production.

## Container Health

```bash
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

## Database & Redis Health

| Check | Command | Expected | Status |
|-------|---------|----------|--------|
| MariaDB ping | `docker compose exec db mysqladmin ping ...` | `mysqld is alive` | |
| Redis ping | `docker compose exec redis redis-cli -a ... ping` | `PONG` | |
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

| Source | Errors / Warnings | Action |
|--------|-------------------|--------|
| backend | | |
| frontend | | |
| nginx | | |
| queue | | |

## Log Rotation

| Check | Status |
|-------|--------|
| Docker log driver limits configured | |
| No single log file unbounded | |
| Application logs rotated | |

## Backups

| Check | Location / Command | Expected | Status |
|-------|--------------------|----------|--------|
| Latest backup | `/opt/vestra/backups/` | Recent | |
| Backup size | | Reasonable | |
| Restore procedure tested | Documented | Yes | |
| Off-site replication | | Configured | |

## Monitoring & Alerting

| Check | Status |
|-------|--------|
| Uptime monitoring on `/api/v1/health` | |
| Certificate expiry alerts | |
| Disk usage alerts | |
| Memory usage alerts | |
| 5xx error rate alerts | |

## Disaster Recovery

| Procedure | Documented | Tested | Status |
|-----------|------------|--------|--------|
| Rollback | | | |
| Database restore | | | |
| Full environment rebuild | | | |

## Findings

| ID | Finding | Severity | Action | Status |
|----|---------|----------|--------|--------|
| | | | | |

## Conclusion

- [ ] All containers healthy.
- [ ] Database and Redis responsive.
- [ ] Queue and scheduler operational.
- [ ] Logs reviewed and no critical errors.
- [ ] Backups available and restore procedure documented.
- [ ] Monitoring and alerting in place.
- [ ] Operational team ready.
