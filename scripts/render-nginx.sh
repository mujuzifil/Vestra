#!/bin/bash
# ==============================================================================
# VESTRA — Render full SSL nginx configuration
# ==============================================================================
# Usage: ./scripts/render-nginx.sh
#
# Renders nginx/conf.d/vestra.conf.ssl.template into the running nginx
# container's /etc/nginx/conf.d/vestra.conf and reloads nginx. Must be run
# after all SSL certificates have been provisioned.
# ==============================================================================
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT_DIR"

ENV_FILE=".env.production"
CERT_DIR="${ROOT_DIR}/certbot/conf"
TEMPLATE="${ROOT_DIR}/nginx/conf.d/vestra.conf.ssl.template"

log()  { echo -e "\033[0;34m[nginx]\033[0m $*"; }
ok()   { echo -e "\033[0;32m[nginx]\033[0m $*"; }
fail() { echo -e "\033[0;31m[nginx]\033[0m $*" >&2; exit 1; }

[ -f "$ENV_FILE" ]  || fail "$ENV_FILE not found."
[ -f "$TEMPLATE" ]  || fail "Template ${TEMPLATE} not found."

APP_DOMAIN="$(grep -E '^APP_DOMAIN=' "$ENV_FILE" | cut -d= -f2- | tr -d '"' || true)"
API_DOMAIN="$(grep -E '^API_DOMAIN=' "$ENV_FILE" | cut -d= -f2- | tr -d '"' || true)"
ADMIN_DOMAIN="$(grep -E '^ADMIN_DOMAIN=' "$ENV_FILE" | cut -d= -f2- | tr -d '"' || true)"

[ -n "$APP_DOMAIN" ]   || fail "APP_DOMAIN is not set in $ENV_FILE."
[ -n "$API_DOMAIN" ]   || fail "API_DOMAIN is not set in $ENV_FILE."
[ -n "$ADMIN_DOMAIN" ] || fail "ADMIN_DOMAIN is not set in $ENV_FILE."

DOMAINS=("$APP_DOMAIN" "$API_DOMAIN" "$ADMIN_DOMAIN")

for domain in "${DOMAINS[@]}"; do
    [ -f "${CERT_DIR}/live/${domain}/fullchain.pem" ] || fail "Certificate for ${domain} is missing. Run ./scripts/init-certs.sh first."
done

COMPOSE="docker compose -f docker-compose.prod.yml --env-file ${ENV_FILE}"

log "Rendering full SSL nginx configuration..."

# Render the template inside the nginx container using the same envsubst the
# official image uses. Output goes to /etc/nginx/conf.d/vestra.conf.
$COMPOSE exec -T nginx sh -c "envsubst '\${APP_DOMAIN} \${API_DOMAIN} \${ADMIN_DOMAIN}' < /etc/nginx/templates/vestra.conf.ssl.template > /etc/nginx/conf.d/vestra.conf" \
    || fail "Failed to render nginx configuration."

log "Testing nginx configuration..."
$COMPOSE exec -T nginx nginx -t || fail "Nginx configuration test failed."

log "Reloading nginx..."
$COMPOSE exec -T nginx nginx -s reload || fail "Nginx reload failed."

ok "Nginx SSL configuration rendered and reloaded."
