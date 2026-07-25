# RG1 — Production Handover

## Objective

Provide the operations team with everything needed to run and support VESTRA Commerce Platform Version 1.0.0 in production.

## System Overview

| Component | Technology | Container |
|-----------|------------|-----------|
| Frontend | Next.js 14+ | `vestra-frontend` |
| Backend API | Laravel 11+ | `vestra-backend` |
| Admin Panel | Filament 3+ | served by `vestra-backend` |
| Database | MySQL 8.0 | `vestra-db` |
| Cache / Queue / Sessions | Redis 7 | `vestra-redis` |
| Reverse Proxy | Nginx 1.27 | `vestra-nginx` |
| SSL | Let's Encrypt / Certbot | `vestra-certbot` |

## Production URLs

| Service | URL |
|---------|-----|
| Customer website | `https://vestradetergents.com` |
| REST API | `https://api.vestradetergents.com` |
| Admin portal | `https://admin.vestradetergents.com` |

## Deployment Commands

```bash
cd /opt/vestra
./scripts/deploy.sh --build
```

## Rollback Command

```bash
cd /opt/vestra
./scripts/rollback.sh
```

## Cache Rebuild

```bash
COMPOSE="docker compose -f docker-compose.prod.yml --env-file .env.production"
$COMPOSE exec backend php artisan config:cache
$COMPOSE exec backend php artisan route:cache
$COMPOSE exec backend php artisan view:cache
$COMPOSE exec backend php artisan event:cache
```

## Health Checks

```bash
$COMPOSE exec backend curl -fsS http://localhost:8080/api/v1/health
$COMPOSE exec frontend wget --spider http://localhost:3000/api/health
```

## Monitoring

| Check | Endpoint / Command | Alert Threshold |
|-------|--------------------|-----------------|
| API health | `https://api.vestradetergents.com/api/v1/health` | HTTP != 200 |
| Frontend health | `https://vestradetergents.com/api/health` | HTTP != 200 |
| Container health | `docker compose ps` | Any service not `healthy` |
| Disk usage | `df -h` | > 80% |
| Memory usage | `free -h` | > 85% |
| Certificate expiry | `openssl x509 -in ... -noout -dates` | < 14 days |

## Backup & Restore

| Task | Command |
|------|---------|
| Take backup | `./scripts/backup.sh /opt/vestra/backups` |
| List backups | `ls -la /opt/vestra/backups` |
| Restore | `./scripts/restore.sh <backup-file>` |

## Escalation Contacts

| Role | Name | Contact |
|------|------|---------|
| Engineering Lead | | |
| Operations Lead | | |
| Product Owner | | |

## Operational Runbooks

- `docs/release/OPERATIONS_RUNBOOK.md`
- `docs/release/BACKUP_AND_RESTORE_GUIDE.md`
- `docs/release/ROLLBACK_CHECKLIST.md`
- `docs/release/KNOWN_ISSUES.md`

## Sign-Off

| Role | Name | Date |
|------|------|------|
| Engineering | | |
| Operations | | |
