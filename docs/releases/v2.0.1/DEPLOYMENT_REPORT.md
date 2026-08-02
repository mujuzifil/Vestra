# VESTRA v2.0.1 — Production Deployment Report

## Release Metadata

| Field | Value |
|-------|-------|
| Version | v2.0.1 |
| Release date | 2026-08-01 |
| Deployed commit | `b478a01` (master) |
| Server commit | `f211605` (develop / deploy-v2) |
| Previous release | v2.0.0 (`d426966`) |
| Deployed by | Manual deployment via VPS |
| Image tag | `local-20260801233059` |
| Previous image tag | `local-20260801232957` |

## Deployment Summary

The VESTRA Version 2.0 corporate website was deployed to production using a controlled, validated manual deployment on the production VPS. The initial v2.0.0 tag failed to deploy via GitHub Actions due to missing Docker registry credentials, so the release was deployed directly from the `develop` branch on the server, with a post-tag hotfix (`v2.0.1`) applied to correct a PHP syntax error discovered during the build.

## Pre-Flight Checks

- [x] Server inspection completed (git, Docker, networks, volumes).
- [x] Pre-deployment health verified (db, redis, nginx running).
- [x] Pre-deployment backup created: `/opt/vestra/backups/20260801_232104`.
- [x] Git working tree clean.
- [x] No uncommitted blocking changes.

## Issues Fixed During Deployment

### 1. Nginx Duplicate `default_server` Conflict

The existing production nginx container was in a restart loop because a bootstrap `default.conf` and the rendered SSL vhost both declared `default_server` on port 80. This was resolved by removing the bootstrap file before rendering the SSL vhost.

### 2. Frontend Health Probe Required `curl`

The frontend runtime stage did not include `curl`, causing the Docker healthcheck to fail. The runtime image was updated to install `curl` and use it for health probes.

### 3. PHP Syntax Error in `BlogPostResource.php`

The backend image build failed with:

```
syntax error, unexpected token "->", expecting "]"
In BlogPostResource.php line 116
```

Root cause: a trailing comma after `->label('Featured on Knowledge Centre'),` followed by `->default(false)`. The comma was removed and the fix was committed as `f211605`, then pushed to `origin/develop` and merged into `master` as `v2.0.1`.

## Deployment Steps Executed

1. `git fetch origin`
2. Created `deploy-v2` from `origin/develop` and merged server-local deployment fixes.
3. Fixed `BlogPostResource.php` syntax error.
4. Ran `./scripts/deploy.sh --build`.
5. Build completed and all services recreated successfully.
6. Post-deployment validation executed.

## Services After Deployment

| Service | Image | Status |
|---------|-------|--------|
| nginx | `nginx:1.27-alpine` | healthy |
| frontend | `vestra/vestra-frontend:local-20260801233059` | healthy |
| backend | `vestra/vestra-backend:local-20260801233059` | healthy |
| queue | `vestra/vestra-backend:local-20260801233059` | healthy |
| scheduler | `vestra/vestra-backend:local-20260801233059` | healthy |
| db | `mysql:8.0` | healthy |
| redis | `redis:7-alpine` | healthy |
| certbot | `certbot/certbot:latest` | running |

## Migration Status

```
php artisan migrate:status --pending
INFO  No pending migrations.
```

All Phases 1.5–11 migrations were already applied.

## Git Outcome

- `origin/develop` updated with hotfix: `1e7b161..ca65269`
- `origin/master` updated with merge commit: `bc078c7..b478a01`
- Tag `v2.0.1` created and pushed.
- Original `v2.0.0` tag remains at `d426966`.

## Rollback Plan

Rollback target image: `local-20260801232957`

```bash
cd /opt/vestra
export IMAGE_TAG=local-20260801232957
docker compose -f docker-compose.prod.yml --env-file .env.production up -d --no-build
```

All v2.0.x migrations are additive; a code-only rollback is safe.

## Operator Notes

- Quote and contact form submissions save to the database but cannot send email until `MAIL_USERNAME` and `MAIL_PASSWORD` are configured in `.env.production`.
- Certbot dry-run was rate-limited by Let's Encrypt due to repeated attempts; certificates are valid for 80+ days and auto-renewal is configured.
