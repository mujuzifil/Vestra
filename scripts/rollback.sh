#!/bin/bash
# ==============================================================================
# VESTRA — Production rollback
# ==============================================================================
# Usage:
#   ./scripts/rollback.sh              roll back to PREVIOUS_TAG in .env.production
#   ./scripts/rollback.sh <image-tag>  roll back to a specific tag
#
# Rolls back APPLICATION CODE ONLY. Database migrations are not reversed —
# see the warning below.
# ==============================================================================
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT_DIR"

COMPOSE="docker compose -f docker-compose.prod.yml --env-file .env.production"
ENV_FILE=".env.production"

log()  { echo -e "\033[0;34m[rollback]\033[0m $*"; }
ok()   { echo -e "\033[0;32m[rollback]\033[0m $*"; }
warn() { echo -e "\033[0;33m[rollback]\033[0m $*"; }
fail() { echo -e "\033[0;31m[rollback]\033[0m $*" >&2; exit 1; }

[ -f "$ENV_FILE" ] || fail "$ENV_FILE not found."

CURRENT_TAG="$(grep -E '^IMAGE_TAG=' "$ENV_FILE" | cut -d= -f2- || echo '')"
TARGET_TAG="${1:-$(grep -E '^PREVIOUS_TAG=' "$ENV_FILE" | cut -d= -f2- || echo '')}"

[ -n "$TARGET_TAG" ] || fail "No rollback target. PREVIOUS_TAG is empty — pass a tag explicitly: $0 <image-tag>"
[ "$TARGET_TAG" != "$CURRENT_TAG" ] || fail "Target tag ${TARGET_TAG} is already deployed."

# ------------------------------------------------------------------------------
# Migration warning — the part that actually bites people
# ------------------------------------------------------------------------------
cat <<'WARNING'

  ┌────────────────────────────────────────────────────────────────────┐
  │  ROLLBACK REVERTS CODE, NOT THE DATABASE                           │
  │                                                                    │
  │  Any migration applied by the newer release stays applied. That is │
  │  safe for additive changes (new nullable column, new table). It is │
  │  NOT safe if the release dropped or renamed a column the older     │
  │  code still reads — that code will error on boot.                  │
  │                                                                    │
  │  If the release contained a destructive migration, restore from    │
  │  the pre-deploy backup instead:                                    │
  │      ./scripts/restore.sh /opt/vestra/backups/<timestamp>          │
  └────────────────────────────────────────────────────────────────────┘

WARNING

if [ -t 0 ]; then
    read -r -p "Roll back from '${CURRENT_TAG}' to '${TARGET_TAG}'? [y/N] " CONFIRM
    case "$CONFIRM" in
        [yY]|[yY][eE][sS]) ;;
        *) fail "Aborted." ;;
    esac
else
    warn "Non-interactive shell — proceeding without confirmation."
fi

# ------------------------------------------------------------------------------
# Safety backup of the current (broken) state, for forensics
# ------------------------------------------------------------------------------
log "Backing up current state before rolling back..."
./scripts/backup.sh "${BACKUP_DIR:-/opt/vestra/backups}" || warn "Backup failed — continuing, rollback is the priority."

# ------------------------------------------------------------------------------
# Swap tags
# ------------------------------------------------------------------------------
log "Switching IMAGE_TAG to ${TARGET_TAG}..."
sed -i "s|^IMAGE_TAG=.*|IMAGE_TAG=${TARGET_TAG}|" "$ENV_FILE"
# The failed release becomes the roll-forward target once it is fixed.
sed -i "s|^PREVIOUS_TAG=.*|PREVIOUS_TAG=${CURRENT_TAG}|" "$ENV_FILE"

log "Pulling ${TARGET_TAG}..."
$COMPOSE pull || warn "Pull failed — falling back to locally cached images."

log "Restarting services..."
$COMPOSE up -d --force-recreate

# ------------------------------------------------------------------------------
# Health gate
# ------------------------------------------------------------------------------
log "Waiting for backend health..."
for i in $(seq 1 30); do
    if $COMPOSE exec -T backend curl -fsS http://localhost:8080/api/v1/health >/dev/null 2>&1; then
        ok "Backend healthy after ${i} attempt(s)."
        $COMPOSE ps
        echo ""
        ok "Rollback complete — now running ${TARGET_TAG}."
        echo "     If the rolled-back release is also unhealthy, restore from backup:"
        echo "       ./scripts/restore.sh /opt/vestra/backups/<timestamp>"
        exit 0
    fi
    sleep 10
done

echo ""
$COMPOSE logs --tail=100 backend
fail "Backend is still unhealthy after rollback. Escalate — restore from backup may be required."
