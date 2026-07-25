#!/bin/bash
# ==============================================================================
# VESTRA — Manual production deployment
# ==============================================================================
# Usage:
#   ./scripts/deploy.sh <image-tag>     deploy a published tag
#   ./scripts/deploy.sh --build         build locally and deploy
#
# Run from the deployment root (/opt/vestra) with .env.production present.
# ==============================================================================
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT_DIR"

COMPOSE="docker compose -f docker-compose.prod.yml --env-file .env.production"
ENV_FILE=".env.production"

log()  { echo -e "\033[0;34m[deploy]\033[0m $*"; }
ok()   { echo -e "\033[0;32m[deploy]\033[0m $*"; }
fail() { echo -e "\033[0;31m[deploy]\033[0m $*" >&2; exit 1; }

[ -f "$ENV_FILE" ] || fail "$ENV_FILE not found. Copy .env.production.example and fill it in."

MODE="${1:-}"
[ -n "$MODE" ] || fail "Usage: $0 <image-tag> | --build"

# ------------------------------------------------------------------------------
# Pre-flight
# ------------------------------------------------------------------------------
log "Validating compose configuration..."
$COMPOSE config --quiet || fail "Compose configuration is invalid."

for VAR in APP_DOMAIN API_DOMAIN ADMIN_DOMAIN APP_KEY APP_URL FRONTEND_URL \
           CORS_ALLOWED_ORIGINS DB_PASSWORD MYSQL_ROOT_PASSWORD REDIS_PASSWORD \
           NEXT_PUBLIC_API_URL NEXT_PUBLIC_SITE_URL NEXT_PUBLIC_BACKEND_URL \
           CERTBOT_EMAIL; do
    VALUE="$(grep -E "^${VAR}=" "$ENV_FILE" | cut -d= -f2- || true)"
    [ -n "$VALUE" ] || fail "$VAR is empty in $ENV_FILE."
done
ok "Environment looks complete."

# ------------------------------------------------------------------------------
# Back up before changing anything
# ------------------------------------------------------------------------------
log "Taking a pre-deployment backup..."
./scripts/backup.sh "${BACKUP_DIR:-/opt/vestra/backups}" || fail "Backup failed — aborting deploy."
ok "Backup complete."

# ------------------------------------------------------------------------------
# Start persistent infrastructure first
# ------------------------------------------------------------------------------
log "Starting database and cache..."
$COMPOSE up -d db redis || fail "Could not start database or cache."

for svc in db redis; do
    log "Waiting for ${svc} to be healthy..."
    for i in $(seq 1 30); do
        if $COMPOSE ps "$svc" | grep -q "healthy"; then
            ok "${svc} healthy after ${i} attempt(s)."
            break
        fi
        [ "$i" -eq 30 ] && fail "${svc} did not become healthy."
        sleep 2
    done
done

# ------------------------------------------------------------------------------
# Record the outgoing tag, then move to the new one
# ------------------------------------------------------------------------------
CURRENT_TAG="$(grep -E '^IMAGE_TAG=' "$ENV_FILE" | cut -d= -f2- || echo '')"

if [ "$MODE" = "--build" ]; then
    NEW_TAG="local-$(date +%Y%m%d%H%M%S)"
    log "Building images locally as ${NEW_TAG}..."
    sed -i "s|^IMAGE_TAG=.*|IMAGE_TAG=${NEW_TAG}|" "$ENV_FILE"
    $COMPOSE build || fail "Image build failed."
else
    NEW_TAG="$MODE"
    log "Deploying published tag ${NEW_TAG}..."
    sed -i "s|^IMAGE_TAG=.*|IMAGE_TAG=${NEW_TAG}|" "$ENV_FILE"
    $COMPOSE pull || fail "Could not pull ${NEW_TAG}. Check the tag exists in the registry."
fi

# Only record PREVIOUS_TAG once the new tag is committed to the env file, so a
# failed pull/build does not destroy the rollback target.
if [ -n "$CURRENT_TAG" ] && [ "$CURRENT_TAG" != "$NEW_TAG" ]; then
    sed -i "s|^PREVIOUS_TAG=.*|PREVIOUS_TAG=${CURRENT_TAG}|" "$ENV_FILE"
    log "Rollback target recorded: ${CURRENT_TAG}"
fi

# ------------------------------------------------------------------------------
# Migrate before cutover — a failing migration must not take the site down
# ------------------------------------------------------------------------------
log "Running migrations with the new image..."
$COMPOSE run --rm --entrypoint php backend artisan migrate --force \
    || fail "Migration failed. The running stack is untouched; investigate before retrying."
ok "Migrations applied."

# ------------------------------------------------------------------------------
# Bootstrap nginx so ACME challenges can be served
# ------------------------------------------------------------------------------
# At this point the full SSL vhost is not rendered yet. Nginx starts using the
# HTTP-only bootstrap configuration (default.conf.template), which serves
# /.well-known/acme-challenge/ and returns a 503 placeholder for everything else.
log "Bootstrapping nginx for certificate provisioning..."
$COMPOSE up -d nginx certbot || fail "Could not start nginx/certbot."

log "Waiting for nginx to be healthy..."
for i in $(seq 1 30); do
    if $COMPOSE exec -T nginx wget --no-verbose --tries=1 --spider \
         http://127.0.0.1/nginx-health >/dev/null 2>&1; then
        ok "Nginx healthy after ${i} attempt(s)."
        break
    fi
    [ "$i" -eq 30 ] && fail "Nginx did not become healthy."
    sleep 2
done

# ------------------------------------------------------------------------------
# Issue any missing certificates
# ------------------------------------------------------------------------------
APP_DOMAIN="$(grep -E '^APP_DOMAIN=' "$ENV_FILE" | cut -d= -f2- || true)"
API_DOMAIN="$(grep -E '^API_DOMAIN=' "$ENV_FILE" | cut -d= -f2- || true)"
ADMIN_DOMAIN="$(grep -E '^ADMIN_DOMAIN=' "$ENV_FILE" | cut -d= -f2- || true)"
CERT_DIR="${ROOT_DIR}/certbot/conf"

MISSING=false
for domain in "$APP_DOMAIN" "$API_DOMAIN" "$ADMIN_DOMAIN"; do
    if [ ! -f "${CERT_DIR}/live/${domain}/fullchain.pem" ]; then
        MISSING=true
        break
    fi
done

if [ "$MISSING" = "true" ]; then
    log "Missing SSL certificates detected; provisioning..."
    ./scripts/init-certs.sh || fail "Certificate provisioning failed."
else
    ok "All SSL certificates are present."
fi

# ------------------------------------------------------------------------------
# Render full SSL configuration and reload nginx
# ------------------------------------------------------------------------------
log "Rendering full SSL nginx configuration..."
./scripts/render-nginx.sh || fail "Could not render SSL nginx configuration."
ok "Nginx serving HTTPS."

# ------------------------------------------------------------------------------
# Cutover — start the remaining application services
# ------------------------------------------------------------------------------
log "Starting application services..."
$COMPOSE up -d backend queue scheduler frontend || fail "Could not start application services."

# ------------------------------------------------------------------------------
# Health gate
# ------------------------------------------------------------------------------
log "Waiting for backend health..."
HEALTHY=false
for i in $(seq 1 30); do
    if $COMPOSE exec -T backend curl -fsS http://localhost:8080/api/v1/health >/dev/null 2>&1; then
        HEALTHY=true
        ok "Backend healthy after ${i} attempt(s)."
        break
    fi
    sleep 10
done

if [ "$HEALTHY" != "true" ]; then
    echo ""
    $COMPOSE logs --tail=100 backend
    fail "Backend did not become healthy. Roll back with: ./scripts/rollback.sh"
fi

log "Waiting for frontend health..."
for i in $(seq 1 18); do
    if $COMPOSE exec -T frontend wget --no-verbose --tries=1 --spider \
         http://localhost:3000/api/health >/dev/null 2>&1; then
        ok "Frontend healthy after ${i} attempt(s)."
        break
    fi
    [ "$i" -eq 18 ] && fail "Frontend did not become healthy. Roll back with: ./scripts/rollback.sh"
    sleep 10
done

# ------------------------------------------------------------------------------
# Post-deploy verification
# ------------------------------------------------------------------------------
log "Service status:"
$COMPOSE ps

log "Verifying the scheduler is registered..."
$COMPOSE exec -T scheduler php artisan schedule:list || log "WARNING: could not list the schedule."

log "Testing certificate renewal dry-run..."
$COMPOSE exec -T certbot certbot renew --dry-run || log "WARNING: certbot renewal dry-run failed."

echo ""
ok "Deployment complete — now running ${NEW_TAG}."
echo "     Rollback target: ${CURRENT_TAG:-none recorded}"
echo "     Verify against docs/release/DEPLOYMENT_VERIFICATION_CHECKLIST.md"
