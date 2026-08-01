# VESTRA v2.0.0 — Operations Guide

## Repository Layout on VPS

```
/opt/vestra/
├── docker-compose.prod.yml
├── .env.production
├── scripts/
├── nginx/
├── certbot/
├── backups/
└── frontend/docs/
    └── releases/
```

## Common Commands

```bash
cd /opt/vestra
DC='docker compose -f docker-compose.prod.yml --env-file .env.production'

# Service status
$DC ps

# Logs
$DC logs --tail 100 backend
$DC logs --tail 100 frontend
$DC logs --tail 100 queue

# Laravel artisan
$DC exec backend php artisan <command>

# Queue status
$DC exec backend php artisan queue:failed
$DC exec redis redis-cli -a "$REDIS_PASSWORD" --no-auth-warning llen queues:default

# Scheduler
$DC exec scheduler php artisan schedule:list

# Database
$DC exec db mysql -u root -p"$MYSQL_ROOT_PASSWORD" vestra
```

## Backup

```bash
./scripts/backup.sh /opt/vestra/backups
```

Backups include database dump, uploaded storage, and the environment file. Retention defaults to 30 days.

## Monitoring

- Health: `https://api.vestra.com/api/v1/health`
- Frontend health: `https://vestra.com/api/health`
- Nginx health: internal probe at `/nginx-health`
- Logs: `docker compose logs` or `/var/log/` on host

## Alert Thresholds

- 5xx rate > 5%
- Health endpoint 503 for > 2 minutes
- Disk usage > 70%
- Memory usage sustained > 90%
- Certificate expiry < 14 days
- Queue depth increasing over 15 minutes

## SSL Renewal

Certbot attempts renewal twice daily. Verify with:

```bash
$DC exec certbot certbot renew --dry-run
```

## Scaling

Current resource limits are defined in `docker-compose.prod.yml`:

| Service | Memory Limit |
|---------|--------------|
| nginx | 256 MB |
| frontend | 1 GB |
| backend | 1 GB |
| queue | 512 MB |
| scheduler | 512 MB |
| db | 1.5 GB |
| redis | 640 MB |

Increase limits and restart services if load grows.

## Emergency Contacts

- On-call engineering: see `docs/release/SUPPORT_HANDOVER.md`
- Business owner: see launch checklist
