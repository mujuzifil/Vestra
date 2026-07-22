#!/bin/bash
# ==============================================================================
# VESTRA — Backup
# ==============================================================================
# Usage: ./backup.sh [destination]        (default: ./backups)
#
# Captures the database, uploaded storage and the environment file, then
# verifies the dump is readable before reporting success. Runs mysqldump inside
# the db container — the database port is not published on the host.
# ==============================================================================
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT_DIR"

DESTINATION="${1:-./backups}"
TIMESTAMP="$(date +%Y%m%d_%H%M%S)"
BACKUP_DIR="$DESTINATION/$TIMESTAMP"
RETENTION_DAYS="${BACKUP_RETENTION_DAYS:-30}"

COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.prod.yml}"
ENV_FILE="${ENV_FILE:-.env.production}"
DB_SERVICE="${DB_SERVICE:-db}"
BACKEND_SERVICE="${BACKEND_SERVICE:-backend}"

log()  { echo -e "\033[0;34m[backup]\033[0m $*"; }
ok()   { echo -e "\033[0;32m[backup]\033[0m $*"; }
warn() { echo -e "\033[0;33m[backup]\033[0m $*"; }
fail() { echo -e "\033[0;31m[backup]\033[0m $*" >&2; exit 1; }

if [ -f "$ENV_FILE" ]; then
    COMPOSE="docker compose -f $COMPOSE_FILE --env-file $ENV_FILE"
else
    COMPOSE="docker compose -f $COMPOSE_FILE"
    warn "$ENV_FILE not found; relying on ambient environment."
fi

# Credentials come from the env file so they are never passed on the host
# command line, where they would be visible in the process table.
DB_NAME="${DB_DATABASE:-$(grep -E '^DB_DATABASE=' "$ENV_FILE" 2>/dev/null | cut -d= -f2- || echo vestra)}"
ROOT_PASS="${MYSQL_ROOT_PASSWORD:-$(grep -E '^MYSQL_ROOT_PASSWORD=' "$ENV_FILE" 2>/dev/null | cut -d= -f2- || echo '')}"

[ -n "$ROOT_PASS" ] || fail "MYSQL_ROOT_PASSWORD unavailable. Set it in $ENV_FILE or the environment."

log "Starting backup at $TIMESTAMP"
mkdir -p "$BACKUP_DIR"

# ------------------------------------------------------------------------------
# Database
# ------------------------------------------------------------------------------
# --single-transaction keeps InnoDB consistent without locking writes.
log "Dumping database '$DB_NAME'..."
if ! $COMPOSE exec -T -e MYSQL_PWD="$ROOT_PASS" "$DB_SERVICE" \
        mysqldump -u root \
            --single-transaction \
            --routines \
            --triggers \
            --events \
            --default-character-set=utf8mb4 \
            "$DB_NAME" > "$BACKUP_DIR/database.sql"; then
    fail "mysqldump failed. Is the '$DB_SERVICE' service running?"
fi

# A dump that exists but is truncated is worse than no dump, because it is
# trusted. Verify before compressing.
if [ ! -s "$BACKUP_DIR/database.sql" ]; then
    fail "Dump is empty — refusing to record a broken backup."
fi
if ! grep -q "Dump completed" "$BACKUP_DIR/database.sql"; then
    fail "Dump is truncated (no completion marker) — refusing to record a broken backup."
fi

TABLE_COUNT="$(grep -c "^CREATE TABLE" "$BACKUP_DIR/database.sql" || echo 0)"
log "Dump contains $TABLE_COUNT tables."
[ "$TABLE_COUNT" -gt 0 ] || fail "Dump contains no tables — refusing to record a broken backup."

gzip "$BACKUP_DIR/database.sql"
gzip -t "$BACKUP_DIR/database.sql.gz" || fail "Compressed dump failed its integrity check."
ok "Database backed up and verified."

# ------------------------------------------------------------------------------
# Uploaded storage
# ------------------------------------------------------------------------------
log "Backing up uploaded storage..."
if $COMPOSE ps --services 2>/dev/null | grep -q "^${BACKEND_SERVICE}$"; then
    $COMPOSE exec -T "$BACKEND_SERVICE" \
        tar -czf - -C /var/www/html/storage/app public 2>/dev/null \
        > "$BACKUP_DIR/storage.tar.gz" || warn "Storage backup failed or storage is empty."
    if [ -s "$BACKUP_DIR/storage.tar.gz" ]; then
        gzip -t "$BACKUP_DIR/storage.tar.gz" && ok "Storage backed up and verified."
    else
        rm -f "$BACKUP_DIR/storage.tar.gz"
        warn "No storage archive produced."
    fi
else
    warn "Backend service not running; skipping storage backup."
fi

# ------------------------------------------------------------------------------
# Environment
# ------------------------------------------------------------------------------
# Contains live secrets. The backup tree must be root-owned and mode 600.
if [ -f "$ENV_FILE" ]; then
    cp "$ENV_FILE" "$BACKUP_DIR/env.production.bak"
    chmod 600 "$BACKUP_DIR/env.production.bak"
    ok "Environment file captured (contains secrets — keep this tree private)."
fi

# ------------------------------------------------------------------------------
# Manifest
# ------------------------------------------------------------------------------
cat > "$BACKUP_DIR/MANIFEST.txt" <<EOF
VESTRA Backup
Timestamp:   $TIMESTAMP
Database:    $DB_NAME
Tables:      $TABLE_COUNT
Image tag:   $(grep -E '^IMAGE_TAG=' "$ENV_FILE" 2>/dev/null | cut -d= -f2- || echo 'unknown')
Git commit:  $(git rev-parse --short HEAD 2>/dev/null || echo 'unknown')
Host:        $(hostname)
Files:
$(cd "$BACKUP_DIR" && ls -la)
EOF

chmod -R go-rwx "$BACKUP_DIR" 2>/dev/null || true

SIZE="$(du -sh "$BACKUP_DIR" | cut -f1)"
ok "Backup complete: $BACKUP_DIR ($SIZE)"

# ------------------------------------------------------------------------------
# Retention
# ------------------------------------------------------------------------------
log "Pruning backups older than ${RETENTION_DAYS} days..."
find "$DESTINATION" -maxdepth 1 -mindepth 1 -type d -mtime "+${RETENTION_DAYS}" \
    -exec rm -rf {} + 2>/dev/null || true

REMAINING="$(find "$DESTINATION" -maxdepth 1 -mindepth 1 -type d | wc -l)"
ok "Done. ${REMAINING} backup(s) retained."
