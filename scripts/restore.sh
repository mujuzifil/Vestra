#!/bin/bash
set -euo pipefail

# VESTRA Database Restore Script
# Usage: ./restore.sh <backup-directory>

if [ $# -eq 0 ]; then
    echo "Usage: $0 <backup-directory>"
    echo "Example: $0 ./backups/20250716_120000"
    exit 1
fi

BACKUP_DIR="$1"
DB_NAME="${DB_DATABASE:-vestra}"
DB_USER="${DB_USERNAME:-root}"
DB_PASS="${DB_PASSWORD:-}"
DB_HOST="${DB_HOST:-localhost}"

if [ ! -d "$BACKUP_DIR" ]; then
    echo "Error: Backup directory not found: $BACKUP_DIR"
    exit 1
fi

echo "Restoring VESTRA from backup: $BACKUP_DIR"

# Restore database
if [ -f "$BACKUP_DIR/database.sql.gz" ]; then
    echo "Restoring database..."
    gunzip -c "$BACKUP_DIR/database.sql.gz" | mysql -h "$DB_HOST" -u "$DB_USER" ${DB_PASS:+-p"$DB_PASS"} "$DB_NAME"
    echo "Database restored."
else
    echo "Warning: Database backup not found."
fi

# Restore storage
if [ -f "$BACKUP_DIR/storage.tar.gz" ]; then
    echo "Restoring storage..."
    tar -xzf "$BACKUP_DIR/storage.tar.gz" -C "$(dirname "$0")/../backend/storage"
    echo "Storage restored."
fi

echo "Restore complete."
