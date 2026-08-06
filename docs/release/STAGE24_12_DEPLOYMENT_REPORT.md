# Stage 24.12 — Deployment Report

## Code status

- **Branch tip:** `master` @ `466ad01` (includes UX summary docs)
- **Feature merge commit:** `147efc4` — Stage 24.12 UX account hardening
- **Remotes:** `origin/master` and `origin/develop` updated

## Validation before deploy

- Profile API fields test: Pass
- Frontend eslint (changed files): Pass
- `npm run build`: Pass

## Production deploy

**Attempted 2026-08-06:** SSH to `deploy@187.77.84.119` failed with `Permission denied (publickey)` (key offered and accepted by server, signature step failed — likely passphrase/agent unavailable in this session).

### Operator deploy steps

```bash
ssh deploy@187.77.84.119
cd /opt/vestra
git fetch origin master && git checkout master && git pull origin master
./scripts/deploy.sh --build
# After deploy (if frontend health race):
docker compose -f docker-compose.prod.yml ps
curl -s -o /dev/null -w "%{http_code}\n" https://vestradetergents.com/
curl -s -o /dev/null -w "%{http_code}\n" https://api.vestradetergents.com/api/v1/health
```

### Pages to verify live

- `/account/distributor` (approved distributor dashboard)
- Business Portal sidebar logo
- Public nav single-line labels
- `/about` Household category icon
- `/contact` map embed
- `/account/preferences`, `/account/security`, `/account/profile`

## Rollback

`cd /opt/vestra && ./scripts/rollback.sh` — see [ROLLBACK_CHECKLIST.md](ROLLBACK_CHECKLIST.md)
