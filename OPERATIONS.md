# VESTRA Operations Manual

## Daily Operations

### Health Checks

```bash
# Check all services
docker compose -f docker-compose.prod.yml ps

# Check logs
docker compose -f docker-compose.prod.yml logs -f backend
docker compose -f docker-compose.prod.yml logs -f frontend
docker compose -f docker-compose.prod.yml logs -f db
```

### Monitoring Endpoints

| Endpoint | Purpose |
|----------|---------|
| `GET /api/v1/health` | Full health check (DB, storage, cache) |
| `GET /api/v1/health/ready` | Readiness probe |
| `GET /api/v1/health/live` | Liveness probe |
| `GET /api/health` | Frontend health |

### Log Locations

| Service | Location |
|---------|----------|
| Backend | `docker compose logs backend` or `/var/www/html/storage/logs/` |
| Frontend | `docker compose logs frontend` |
| Nginx | `docker compose logs nginx` |
| Database | `docker compose logs db` |

## Backup Procedures

### Automated Daily Backup

```bash
# Add to crontab (runs daily at 2 AM)
0 2 * * * /opt/vestra/scripts/backup.sh /opt/vestra/backups >> /opt/vestra/logs/backup.log 2>&1
```

### Manual Backup

```bash
./scripts/backup.sh /path/to/backup/destination
```

### Restore

```bash
./scripts/restore.sh /path/to/backups/20250716_120000
```

## Alerting Thresholds

| Metric | Warning | Critical |
|--------|---------|----------|
| HTTP 5xx rate | > 1% | > 5% |
| Response time (p95) | > 500ms | > 2s |
| Disk usage | > 70% | > 90% |
| Memory usage | > 70% | > 90% |
| Database connections | > 80% | > 95% |

## Incident Response

### 1. Identify
- Check monitoring dashboards
- Review error logs
- Reproduce the issue

### 2. Contain
- Scale up if under load
- Enable maintenance mode if needed
- Block malicious IPs if under attack

### 3. Resolve
- Apply fix or rollback
- Verify resolution
- Monitor for recurrence

### 4. Post-Incident
- Document root cause
- Update runbooks
- Schedule preventive work

## Maintenance Windows

- **Database migrations**: During low-traffic periods
- **Dependency updates**: Weekly security patches
- **Major releases**: Monthly, with 48-hour notice
