#!/bin/bash
# ==============================================================================
# VESTRA — Stage 17.3: Production environment initialisation
# ==============================================================================
# Usage (on the VPS, from the deployment root /opt/vestra):
#   ./scripts/init-production-env.sh
#
# Creates .env.production from .env.production.example with freshly generated
# secrets and the production domain values. Runs ONCE: the script refuses to
# overwrite an existing .env.production — rotate values deliberately instead.
#
# Third-party credentials (Flutterwave live keys, SMTP) are intentionally left
# empty. The stack boots without them; payments and mail go live when the owner
# supplies the real credentials (edit .env.production, restart containers).
#
# Secrets never leave the server and are never printed.
# ==============================================================================
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT_DIR"

log()  { echo -e "\033[0;34m[env-init]\033[0m $*"; }
ok()   { echo -e "\033[0;32m[env-init]\033[0m $*"; }
fail() { echo -e "\033[0;31m[env-init]\033[0m $*" >&2; exit 1; }

ENV_FILE=".env.production"
TEMPLATE=".env.production.example"

[ -f "$TEMPLATE" ] || fail "$TEMPLATE not found — run from the repository root."
[ ! -f "$ENV_FILE" ] || fail "$ENV_FILE already exists. Refusing to overwrite; edit it by hand or remove it deliberately."

DOMAIN="${APP_DOMAIN:-vestradetergents.com}"
API_DOMAIN="${API_DOMAIN:-api.$DOMAIN}"
IMAGE_TAG="$(git rev-parse --short=12 HEAD 2>/dev/null || echo latest)"

log "Creating $ENV_FILE for domain $DOMAIN (image tag $IMAGE_TAG)..."
cp "$TEMPLATE" "$ENV_FILE"
chmod 600 "$ENV_FILE"

set_var() { # set_var KEY VALUE — replace the KEY= line in .env.production
    local key="$1" value="$2"
    value="${value//\\/\\\\}"; value="${value//&/\\&}"; value="${value//|/\\|}"
    if grep -qE "^${key}=" "$ENV_FILE"; then
        sed -i "s|^${key}=.*|${key}=${value}|" "$ENV_FILE"
    else
        echo "${key}=${value}" >> "$ENV_FILE"
    fi
}

# --- Generated secrets (256-bit; never echoed) ---------------------------------
set_var APP_KEY               "base64:$(openssl rand -base64 32)"
set_var DB_PASSWORD           "$(openssl rand -base64 32)"
set_var MYSQL_ROOT_PASSWORD   "$(openssl rand -base64 32)"
set_var REDIS_PASSWORD        "$(openssl rand -base64 32)"
set_var BOOTSTRAP_ADMIN_PASSWORD "$(openssl rand -base64 24)"

# --- Domain & routing ------------------------------------------------------------
set_var DOCKER_REGISTRY       "vestra"
set_var IMAGE_TAG             "$IMAGE_TAG"
set_var APP_DOMAIN            "$DOMAIN"
set_var API_DOMAIN            "$API_DOMAIN"
set_var APP_URL               "https://$API_DOMAIN"
set_var FRONTEND_URL          "https://$DOMAIN"
set_var TRUSTED_PROXIES       "*"
set_var CORS_ALLOWED_ORIGINS  "https://$DOMAIN,https://www.$DOMAIN"
set_var SESSION_DOMAIN        ".$DOMAIN"
set_var SESSION_SECURE_COOKIE "true"
set_var SANCTUM_STATEFUL_DOMAINS "$DOMAIN,www.$DOMAIN"
set_var NEXT_PUBLIC_API_URL   "https://$API_DOMAIN/api/v1"
set_var NEXT_PUBLIC_SITE_URL  "https://$DOMAIN"
set_var NEXT_PUBLIC_BACKEND_URL "https://$API_DOMAIN"

ok "$ENV_FILE created (mode 600) with generated secrets and production domain values."
log "Flutterwave and MAIL_* are empty by design — payments/mail activate when real credentials are added."
log "Validate with: docker compose -f docker-compose.prod.yml --env-file .env.production config --quiet"
