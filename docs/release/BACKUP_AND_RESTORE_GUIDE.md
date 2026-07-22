# VESTRA — Backup & Restore Guide

**Scripts:** [`scripts/backup.sh`](../../scripts/backup.sh) ·
[`scripts/restore.sh`](../../scripts/restore.sh)

---

## What is backed up

| Component | Method | Included |
|---|---|---|
| Database | `mysqldump` inside the `db` container | Schema, data, routines, triggers, events |
| Uploaded storage | `tar` from `storage/app/public` | Product images, uploaded media |
| Environment | copy of `.env.production` | All secrets — mode `600` |
| Manifest | generated | Timestamp, table count, image tag, git commit |

**Not** backed up (all reproducible): Docker images, `vendor/`, `node_modules/`,
compiled caches, application logs.

---

## Taking a backup

```bash
cd /opt/vestra
./scripts/backup.sh /opt/vestra/backups
```

Produces `/opt/vestra/backups/YYYYMMDD_HHMMSS/` containing `database.sql.gz`,
`storage.tar.gz`, `env.production.bak` and `MANIFEST.txt`.

`mysqldump` runs **inside** the container — the database port is not published
on the host — and credentials are passed via `MYSQL_PWD` in the container
environment rather than on the command line, where they would appear in the
process table.

### Verification is part of the backup

The script refuses to record a backup that is not demonstrably good. It fails if
the dump is empty, lacks the `Dump completed` marker (i.e. is truncated),
contains zero `CREATE TABLE` statements, or fails `gzip -t`.

A backup that exists but is silently truncated is worse than no backup, because
it will be trusted during an incident.

### Scheduling

```cron
0 2 * * * cd /opt/vestra && ./scripts/backup.sh /opt/vestra/backups >> /var/log/vestra-backup.log 2>&1
```

Retention defaults to 30 days via `BACKUP_RETENTION_DAYS`.

### Off-site copies

Local backups do not survive loss of the VPS. Replicate them:

```bash
0 4 * * * rclone sync /opt/vestra/backups remote:vestra-backups --max-age 30d
```

The backup tree contains `.env.production` — encrypt it at rest and restrict
access to operators who already hold production credentials.

---

## Restoring

> **Destructive.** Overwrites the live database.

```bash
cd /opt/vestra
./scripts/restore.sh /opt/vestra/backups/20260722_020000
```

Sequence:

1. Print the manifest.
2. **Verify the archive before touching anything** — gzip integrity, table
   count, completion marker. A corrupt archive aborts with the live database
   untouched.
3. Require the operator to type the database name to confirm.
4. Snapshot the current database to `pre-restore-<timestamp>/`.
5. Stop `backend`, `queue` and `scheduler` so nothing writes mid-restore.
6. Restore the database, then storage.
7. Restart and poll `/api/v1/health`.
8. Report restored table count against the archive's count.

Unattended use: `FORCE_RESTORE=true ./scripts/restore.sh <dir>`.

If the restore itself fails, the pre-restore snapshot is the recovery path — its
location is printed in the failure message.

---

## Disaster recovery

**Targets:** RPO ≤ 24 h (nightly backup) · RTO ≤ 2 h (full rebuild).
RPO improves to near-zero for scenario 1, where the database volume survives.

### 1. Application failure — bad release

Fastest path; no data loss.

```bash
./scripts/rollback.sh
```

### 2. Data corruption or accidental deletion

```bash
ls -la /opt/vestra/backups/
./scripts/restore.sh /opt/vestra/backups/<most-recent-good>
```

Data written since that backup is lost. Check
`docker compose logs db` and the `audit_logs` table to scope the damage first.

### 3. Total VPS loss

```bash
# 1. Provision a replacement and prepare it (Deployment Guide §3)
# 2. Retrieve the off-site backup
rclone copy remote:vestra-backups/<timestamp> /opt/vestra/backups/<timestamp>

# 3. Clone and restore the environment — including the ORIGINAL APP_KEY
git clone <repo-url> /opt/vestra && cd /opt/vestra
cp /opt/vestra/backups/<timestamp>/env.production.bak .env.production
chmod 600 .env.production

# 4. Re-issue certificates for the new IP (update DNS first)
#    — Deployment Guide §5

# 5. Start, then restore data
docker compose -f docker-compose.prod.yml --env-file .env.production up -d --build
FORCE_RESTORE=true ./scripts/restore.sh /opt/vestra/backups/<timestamp>
```

> **The original `APP_KEY` is mandatory.** Encrypted settings cannot be
> decrypted without it. This is why `env.production.bak` is part of every
> backup — and why the backup tree must be protected like the secret it
> contains.

### 4. Database container will not start

```bash
docker compose -f docker-compose.prod.yml --env-file .env.production logs db

# Disk full?
df -h

# InnoDB recovery, escalating 1 → 6. Try the lowest that works, and dump
# immediately once it starts — the higher levels are read-only in practice.
docker compose -f docker-compose.prod.yml --env-file .env.production \
  run --rm db mysqld --innodb-force-recovery=1
```

If recovery fails, restore from backup (scenario 2).

---

## Testing your backups

An untested backup is a hypothesis. Verify quarterly on a **non-production**
host:

```bash
cp .env.production .env.rehearsal
sed -i 's/^DB_DATABASE=.*/DB_DATABASE=vestra_restore_test/' .env.rehearsal
ENV_FILE=.env.rehearsal FORCE_RESTORE=true \
  ./scripts/restore.sh /opt/vestra/backups/<recent>
```

Then confirm row counts for `orders`, `products` and `users` match production
expectations, and that the health endpoint returns 200.

Record the date and outcome. A restore drill that was never run does not count
as a recovery plan.

---

## Reference

| Variable | Default | Purpose |
|---|---|---|
| `BACKUP_RETENTION_DAYS` | `30` | Days of local backups kept |
| `COMPOSE_FILE` | `docker-compose.prod.yml` | Target stack |
| `ENV_FILE` | `.env.production` | Credential source |
| `DB_SERVICE` | `db` | Database service name |
| `FORCE_RESTORE` | unset | `true` skips the confirmation prompt |
