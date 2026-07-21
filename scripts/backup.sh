#!/bin/bash
set -euo pipefail

# VESTRA Database Backup Script
# Usage: ./backup.sh [destination]

DESTINATION="${1:-./backups}"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="$DESTINATION/$TIMESTAMP"
DB_NAME="${DB_DATABASE:-vestra}"
DB_USER="${DB_USERNAME:-root}"
DB_PASS="${DB_PASSWORD:-}"
DB_HOST="${DB_HOST:-localhost}"
RETENTION_DAYS="${BACKUP_RETENTION_DAYS:-30}"

echo "Starting VESTRA backup at $TIMESTAMP..."

mkdir -p "$BACKUP_DIR"

# Database backup
echo "Backing up database..."
mysqldump -h "$DB_HOST" -u "$DB_USER" ${DB_PASS:+-p"$DB_PASS"} "$DB_NAME" \
    --single-transaction \
    --routines \
    --triggers \
    > "$BACKUP_DIR/database.sql"

gzip "$BACKUP_DIR/database.sql"

# Storage backup (uploads, invoices)
echo "Backing up storage..."
tar -czf "$BACKUP_DIR/storage.tar.gz" -C "$(dirname "$0")/../backend/storage" app/public app/invoices 2>/dev/null || true

# Environment backup
echo "Backing up environment..."
cp "$(dirname "$0")/../backend/.env" "$BACKUP_DIR/.env" 2>/dev/null || true

echo "Backup complete: $BACKUP_DIR"

# Cleanup old backups
find "$DESTINATION" -maxdepth 1 -type d -mtime +$RETENTION_DAYS -exec rm -rf {} + 2>/dev/null || true

echo "Backup finished successfully."
