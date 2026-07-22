# VESTRA Deployment

Deployment documentation lives in [`docs/release/`](docs/release/).

| I want to… | Read |
|---|---|
| Deploy to a fresh VPS | [Production Deployment Guide](docs/release/PRODUCTION_DEPLOYMENT_GUIDE.md) |
| Understand every setting | [Environment Configuration Guide](docs/release/ENVIRONMENT_CONFIGURATION_GUIDE.md) |
| Verify a deployment | [Deployment Verification Checklist](docs/release/DEPLOYMENT_VERIFICATION_CHECKLIST.md) |
| Roll back a bad release | [Rollback Checklist](docs/release/ROLLBACK_CHECKLIST.md) |
| Go live | [Go-Live Checklist](docs/release/GO_LIVE_CHECKLIST.md) |

## Quick reference

```bash
cd /opt/vestra

./scripts/deploy.sh <image-tag>     # deploy a published tag
./scripts/deploy.sh --build         # build locally and deploy
./scripts/rollback.sh               # revert to PREVIOUS_TAG
./scripts/backup.sh /opt/vestra/backups
```

## Three things that will bite you

**`NEXT_PUBLIC_*` are compiled into the frontend bundle at build time.**
Changing the API domain requires rebuilding the frontend image — restarting the
container does nothing. Symptom: the browser calls `localhost:8000`.

**Never call `env()` outside `config/*.php`.**
`config:cache` runs on every production boot and makes `env()` return null, so
such code silently falls back to defaults in production while working perfectly
in development. `ProductionConfigIntegrityTest` enforces this.

**Rollback reverts code, not the database.**
Safe past additive migrations; not safe past destructive ones. Restore from
backup in that case.
