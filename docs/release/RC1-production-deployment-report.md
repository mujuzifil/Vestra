# RC1 — Production Deployment Report

## Release Information

| Item | Value |
|------|-------|
| Release candidate | RC1 |
| Deployment target | Production VPS |
| Deployment root | `/opt/vestra` |
| Deployment script | `scripts/deploy.sh` |
| Docker Compose | `docker-compose.prod.yml` |
| Environment file | `.env.production` |

## Pre-Deployment Checklist

- [ ] Repository is on the intended release commit/tag.
- [ ] `.env.production` exists and is complete.
- [ ] Database backup completed.
- [ ] SSL certificates expected for all three domains.
- [ ] Flutterwave credentials present if payment tests are required.
- [ ] SMTP credentials present if mail delivery is required.

## Deployment Commands

Run from `/opt/vestra` on the production VPS:

```bash
cd /opt/vestra
git pull origin main
docker compose -f docker-compose.prod.yml --env-file .env.production config --quiet
./scripts/deploy.sh --build
```

If a tagged image is preferred instead:

```bash
./scripts/deploy.sh <image-tag>
```

## Deployment Log

| Step | Command | Expected Result | Actual Result |
|------|---------|-----------------|---------------|
| Pull code | `git pull origin main` | No merge conflicts | |
| Compose validation | `docker compose -f docker-compose.prod.yml --env-file .env.production config --quiet` | Silent success | |
| Backup | `scripts/backup.sh` | Backup completed | |
| Build/pull images | `deploy.sh` | Images built or pulled | |
| Migrations | `docker compose run --rm --entrypoint php backend artisan migrate --force` | Migrations applied | |
| Start services | `docker compose up -d` | All services running | |
| Backend health | `curl -fsS http://localhost:8080/api/v1/health` | `200 OK` | |
| Frontend health | `wget --spider http://localhost:3000/api/health` | `200 OK` | |
| Scheduler check | `php artisan schedule:list` | Schedule listed | |

## Service Status

```bash
docker compose -f docker-compose.prod.yml --env-file .env.production ps
```

Expected: `nginx`, `frontend`, `backend`, `queue`, `scheduler`, `db`, `redis`, `certbot` all `Up (healthy)` except `certbot` which may show `Up`.

## Post-Deployment Issues

| Issue | Severity | Resolution |
|-------|----------|------------|
| | | |

## Deployment Conclusion

- [ ] Deployment completed successfully.
- [ ] All containers are healthy.
- [ ] No migration errors.
- [ ] Rollback target recorded.
