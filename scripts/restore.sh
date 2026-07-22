#!/bin/bash
# ==============================================================================
# VESTRA — Restore
# ==============================================================================
# Usage: ./restore.sh <backup-directory>
# Example: ./restore.sh ./backups/20260722_020000
#
# DESTRUCTIVE. Overwrites the live database with the backup's contents.
# ==============================================================================
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT_DIR"

COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.prod.yml}"
ENV_FILE="${ENV_FILE:-.env.production}"
DB_SERVICE="${DB_SERVICE:-db}"
BACKEND_SERVICE="${BACKEND_SERVICE:-backend}"

log()  { echo -e "\033[0;34m[restore]\033[0m $*"; }
ok()   { echo -e "\033[0;32m[restore]\033[0m $*"; }
warn() { echo -e "\033[0;33m[restore]\033[0m $*"; }
fail() { echo -e "\033[0;31m[restore]\033[0m $*" >&2; exit 1; }

[ $# -ge 1 ] || fail "Usage: $0 <backup-directory>"
BACKUP_DIR="$1"
[ -d "$BACKUP_DIR" ] || fail "Backup directory not found: $BACKUP_DIR"

if [ -f "$ENV_FILE" ]; then
    COMPOSE="docker compose -f $COMPOSE_FILE --env-file $ENV_FILE"
else
    COMPOSE="docker compose -f $COMPOSE_FILE"
fi

DB_NAME="${DB_DATABASE:-$(grep -E '^DB_DATABASE=' "$ENV_FILE" 2>/dev/null | cut -d= -f2- || echo vestra)}"
ROOT_PASS="${MYSQL_ROOT_PASSWORD:-$(grep -E '^MYSQL_ROOT_PASSWORD=' "$ENV_FILE" 2>/dev/null | cut -d= -f2- || echo '')}"
[ -n "$ROOT_PASS" ] || fail "MYSQL_ROOT_PASSWORD unavailable."

[ -f "$BACKUP_DIR/MANIFEST.txt" ] && { echo ""; cat "$BACKUP_DIR/MANIFEST.txt"; echo ""; }

# ------------------------------------------------------------------------------
# Verify the archive BEFORE destroying anything
# ------------------------------------------------------------------------------
DUMP="$BACKUP_DIR/database.sql.gz"
[ -f "$DUMP" ] || fail "No database.sql.gz in $BACKUP_DIR"

log "Verifying archive integrity..."
gzip -t "$DUMP" || fail "Archive is corrupt. Aborting — the live database is untouched."

TABLE_COUNT="$(gunzip -c "$DUMP" | grep -c "^CREATE TABLE" || echo 0)"
[ "$TABLE_COUNT" -gt 0 ] || fail "Archive contains no tables. Aborting."
gunzip -c "$DUMP" | tail -5 | grep -q "Dump completed" \
    || fail "Archive is truncated. Aborting — the live database is untouched."
ok "Archive verified: $TABLE_COUNT tables."

# ------------------------------------------------------------------------------
# Confirm
# ------------------------------------------------------------------------------
cat <<EOF

  ┌────────────────────────────────────────────────────────────────────┐
  │  DESTRUCTIVE OPERATION                                             │
  │  Database '${DB_NAME}' will be REPLACED by this backup.            │
  │  All data written since the backup was taken will be lost.         │
  └────────────────────────────────────────────────────────────────────┘

EOF

if [ -t 0 ] && [ "${FORCE_RESTORE:-}" != "true" ]; then
    read -r -p "Type the database name '${DB_NAME}' to confirm: " CONFIRM
    [ "$CONFIRM" = "$DB_NAME" ] || fail "Confirmation did not match. Aborted."
else
    warn "Running unattended (FORCE_RESTORE=true or non-interactive)."
fi

# ------------------------------------------------------------------------------
# Safety net — snapshot the current state first
# ------------------------------------------------------------------------------
SAFETY_DIR="${BACKUP_DIR%/*}/pre-restore-$(date +%Y%m%d_%H%M%S)"
log "Snapshotting current database to $SAFETY_DIR..."
mkdir -p "$SAFETY_DIR"
if $COMPOSE exec -T -e MYSQL_PWD="$ROOT_PASS" "$DB_SERVICE" \
        mysqldump -u root --single-transaction --routines --triggers \
        "$DB_NAME" 2>/dev/null | gzip > "$SAFETY_DIR/database.sql.gz"; then
    ok "Pre-restore snapshot saved."
else
    warn "Could not snapshot the current database (it may not exist yet)."
fi

# ------------------------------------------------------------------------------
# Stop writers so nothing mutates mid-restore
# ------------------------------------------------------------------------------
log "Pausing application services..."
$COMPOSE stop "$BACKEND_SERVICE" queue scheduler 2>/dev/null || warn "Some services were not running."

# ------------------------------------------------------------------------------
# Restore
# ------------------------------------------------------------------------------
log "Restoring database..."
gunzip -c "$DUMP" | $COMPOSE exec -T -e MYSQL_PWD="$ROOT_PASS" "$DB_SERVICE" \
    mysql -u root --default-character-set=utf8mb4 "$DB_NAME" \
    || fail "Restore failed. Recover from the snapshot at $SAFETY_DIR"
ok "Database restored."

# ------------------------------------------------------------------------------
# Storage
# ------------------------------------------------------------------------------
if [ -f "$BACKUP_DIR/storage.tar.gz" ]; then
    log "Restoring uploaded storage..."
    gzip -t "$BACKUP_DIR/storage.tar.gz" || fail "Storage archive is corrupt."
    $COMPOSE start "$BACKEND_SERVICE" >/dev/null 2>&1 || true
    sleep 5
    $COMPOSE exec -T "$BACKEND_SERVICE" \
        tar -xzf - -C /var/www/html/storage/app < "$BACKUP_DIR/storage.tar.gz" \
        && ok "Storage restored." || warn "Storage restore failed."
else
    warn "No storage archive in this backup."
fi

# ------------------------------------------------------------------------------
# Bring everything back and verify
# ------------------------------------------------------------------------------
log "Restarting services..."
$COMPOSE up -d

log "Waiting for backend health..."
for i in $(seq 1 30); do
    if $COMPOSE exec -T "$BACKEND_SERVICE" curl -fsS http://localhost:8080/api/v1/health >/dev/null 2>&1; then
        ok "Backend healthy after ${i} attempt(s)."
        RESTORED_TABLES="$($COMPOSE exec -T -e MYSQL_PWD="$ROOT_PASS" "$DB_SERVICE" \
            mysql -u root -N -B -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$DB_NAME';" 2>/dev/null | tr -d '\r')"
        echo ""
        ok "Restore complete. Tables in '$DB_NAME': ${RESTORED_TABLES:-unknown} (archive had $TABLE_COUNT)."
        echo "     Pre-restore snapshot kept at: $SAFETY_DIR"
        exit 0
    fi
    sleep 10
done

$COMPOSE logs --tail=100 "$BACKEND_SERVICE"
fail "Backend unhealthy after restore. Snapshot of prior state: $SAFETY_DIR"
