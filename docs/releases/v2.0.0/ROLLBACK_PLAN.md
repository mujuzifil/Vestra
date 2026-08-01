# VESTRA v2.0.0 — Rollback Plan

## Quick Decision Matrix

| Situation | Action |
|-----------|--------|
| Bad release, data intact | `./scripts/rollback.sh` |
| Data corrupted or deleted | `./scripts/restore.sh <backup>` |
| Bad release with destructive migration | `./scripts/restore.sh <backup>` |
| Infrastructure failure | Fix infrastructure; do not roll code |

## Important Caveat

**Rollback reverts code, not the database.**

Any migration applied by v2.0.0 stays applied. This is safe because all v2.0.0 migrations are additive (new tables, new nullable columns). If a future release introduces destructive migrations, restore from the pre-deploy backup instead.

## Rollback Criteria

Roll back immediately if any of the following are true:

- Payments fail or orders do not advance after payment
- Authentication broken for customers or administrators
- 5xx rate exceeds 5% or health endpoints return 503
- Data loss or corruption suspected
- A security control is found not to be in force

## Rollback Procedure

1. **Confirm the problem is the release**
   ```bash
   cd /opt/vestra
   DC='docker compose -f docker-compose.prod.yml --env-file .env.production'
   $DC ps
   $DC logs --tail 100 backend
   $DC exec backend curl -fsS http://127.0.0.1:8080/api/v1/health
   grep -E '^(IMAGE_TAG|PREVIOUS_TAG)=' .env.production
   ```

2. **Communicate**
   - Notify stakeholders.
   - Enable maintenance mode if needed:
     ```bash
     $DC exec backend php artisan down --retry=60 --secret="<token>"
     ```

3. **Execute**
   ```bash
   cd /opt/vestra
   ./scripts/rollback.sh
   ```
   Or to a specific tag:
   ```bash
   ./scripts/rollback.sh <previous-image-tag>
   ```

4. **Verify**
   - All services healthy
   - Health endpoints return 200
   - Storefront loads and products display
   - Login works
   - A test quote/distributor/contact submission succeeds
   - Queue drains
   - Error rate back to baseline

5. **Lift maintenance mode**
   ```bash
   $DC exec backend php artisan up
   ```

6. **Close out**
   - Record the failed tag and root cause.
   - Update `docs/release/KNOWN_ISSUES.md`.
   - Write a regression test before redeploying.

## Restore from Backup

If rollback is unsafe due to database changes:

```bash
ls -la /opt/vestra/backups
./scripts/restore.sh /opt/vestra/backups/<pre-deploy-timestamp>
```

See `docs/release/BACKUP_AND_RESTORE_GUIDE.md` for full disaster-recovery steps.
