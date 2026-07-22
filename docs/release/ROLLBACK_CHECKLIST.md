# VESTRA — Rollback Checklist

**Script:** [`scripts/rollback.sh`](../../scripts/rollback.sh)

---

## Decide first: roll back, or restore?

These are different operations. Choosing wrong costs time during an incident.

| Situation | Action |
|---|---|
| Bad release, data intact | **Rollback** — `./scripts/rollback.sh` |
| Data corrupted or wrongly deleted | **Restore** — `./scripts/restore.sh <backup>` |
| Bad release **with a destructive migration** | **Restore** — rollback alone will not work |
| Infrastructure failure, code fine | Neither — fix the infrastructure |

### The migration caveat

**Rollback reverts code. It does not revert the database.**

Any migration the newer release applied stays applied. That is safe for
additive changes — a new nullable column, a new table — because older code
simply ignores them.

It is **not** safe if the release dropped or renamed a column the older code
still reads. That code will error on boot, and you will have replaced one
outage with another. In that case restore from the pre-deploy backup instead.

Check before deciding:

```bash
docker compose -f docker-compose.prod.yml --env-file .env.production \
  exec backend php artisan migrate:status | tail -20

git diff <previous-tag>..<current-tag> -- backend/database/migrations/
```

---

## Rollback criteria

Roll back without further debate if any of these hold after a deploy:

- Payments fail, double-charge, or orders do not advance after payment
- Authentication is broken for customers or administrators
- 5xx rate above 5%, or health endpoints returning 503
- Data loss or corruption is suspected
- A security control is found not to be in force

Diagnose after service is restored, not before. A production incident is not
the time to find the root cause.

---

## Procedure

### 1. Confirm the problem is the release

```bash
DC='docker compose -f /opt/vestra/docker-compose.prod.yml --env-file /opt/vestra/.env.production'

$DC ps
$DC logs --tail 100 backend
$DC exec backend curl -fsS http://127.0.0.1:8080/api/v1/health
grep -E '^(IMAGE_TAG|PREVIOUS_TAG)=' /opt/vestra/.env.production
```

- [ ] Failure began after the deploy, not before
- [ ] `PREVIOUS_TAG` is populated (otherwise pass a tag explicitly)
- [ ] Migration impact assessed per above

### 2. Communicate

- [ ] Notify stakeholders that a rollback is starting
- [ ] Enable maintenance mode if the outage is user-visible:
      `$DC exec backend php artisan down --retry=60 --secret="<token>"`

### 3. Execute

```bash
cd /opt/vestra
./scripts/rollback.sh                 # to PREVIOUS_TAG
./scripts/rollback.sh <image-tag>     # to a specific tag
```

The script backs up current state first, swaps `IMAGE_TAG`, sets `PREVIOUS_TAG`
to the failed release (so you can roll forward once it is fixed), pulls,
recreates and polls health for up to 5 minutes.

- [ ] Rollback completed and reported healthy

### 4. Verify

```bash
$DC ps                                                    # all healthy
$DC exec backend curl -fsS http://127.0.0.1:8080/api/v1/health
curl -fsS https://vestra.com/api/health
```

- [ ] All services healthy
- [ ] Storefront loads and products display
- [ ] Login works
- [ ] A test purchase completes
- [ ] Queue is draining
- [ ] Error rate back to baseline
- [ ] Maintenance mode lifted: `$DC exec backend php artisan up`

### 5. Close out

- [ ] Stakeholders informed that service is restored
- [ ] Failed tag recorded: ____________
- [ ] Root cause captured while fresh
- [ ] Regression test written before the fix is redeployed
- [ ] [Known Issues](KNOWN_ISSUES.md) updated if the cause is not yet fixed

---

## If the rollback also fails

1. **Restore from the pre-deploy backup** — `deploy.sh` takes one before every
   deployment:
   ```bash
   ls -la /opt/vestra/backups/
   ./scripts/restore.sh /opt/vestra/backups/<pre-deploy-timestamp>
   ```
2. If restore fails, the pre-restore snapshot path is printed in the failure
   message — that is the last safe state.
3. Escalate. Do not keep cycling deployments; each attempt adds state to
   untangle.

→ [Backup & Restore Guide](BACKUP_AND_RESTORE_GUIDE.md) · disaster recovery §

---

## Incident record

| Field | Value |
|---|---|
| Date / time | |
| Failed tag | |
| Rolled back to | |
| Trigger | |
| Detected by | |
| Time to detect | |
| Time to restore | |
| Customer impact | |
| Root cause | |
| Follow-up actions | |
