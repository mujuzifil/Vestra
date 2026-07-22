# VESTRA Operations

Operations documentation lives in [`docs/release/`](docs/release/).

| I want to… | Read |
|---|---|
| Run the platform day to day | [Operations Runbook](docs/release/OPERATIONS_RUNBOOK.md) |
| Back up or restore | [Backup & Restore Guide](docs/release/BACKUP_AND_RESTORE_GUIDE.md) |
| Recover from disaster | [Backup & Restore Guide](docs/release/BACKUP_AND_RESTORE_GUIDE.md) § Disaster recovery |
| Hand over to a new operator | [Support Handover](docs/release/SUPPORT_HANDOVER.md) |
| See what's still open | [Known Issues](docs/release/KNOWN_ISSUES.md) |

## Quick reference

```bash
alias DC='docker compose -f /opt/vestra/docker-compose.prod.yml --env-file /opt/vestra/.env.production'

DC ps                                   # all eight services should be healthy
DC logs -f backend
curl -fsS https://api.vestra.com/api/v1/health | jq
DC exec scheduler php artisan schedule:list
DC exec backend php artisan queue:failed
```

## Health endpoints

| Endpoint | Meaning |
|---|---|
| `/api/v1/health` | DB, cache, storage. **503** when any fails |
| `/api/v1/health/ready` | DB, cache, Redis. **503** when not ready |
| `/api/v1/health/live` | Process only — no dependencies, by design |

Monitoring must alert on the **status code**. These endpoints previously
returned 200 while unhealthy.
