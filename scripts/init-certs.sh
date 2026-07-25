#!/bin/bash
# ==============================================================================
# VESTRA — Initial Let's Encrypt certificate provisioning
# ==============================================================================
# Usage: ./scripts/init-certs.sh
#
# Reads domains from .env.production and issues any missing certificates using
# the webroot method. Must be run while nginx is serving /.well-known/acme-challenge/
# from /var/www/certbot.
# ==============================================================================
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT_DIR"

ENV_FILE=".env.production"
CERT_DIR="${ROOT_DIR}/certbot/conf"
WEBROOT="${ROOT_DIR}/certbot/www"

log()  { echo -e "\033[0;34m[certbot]\033[0m $*"; }
ok()   { echo -e "\033[0;32m[certbot]\033[0m $*"; }
fail() { echo -e "\033[0;31m[certbot]\033[0m $*" >&2; exit 1; }

[ -f "$ENV_FILE" ] || fail "$ENV_FILE not found."

# Source the variables we need, ignoring comments and empty values.
APP_DOMAIN="$(grep -E '^APP_DOMAIN=' "$ENV_FILE" | cut -d= -f2- | tr -d '"' || true)"
API_DOMAIN="$(grep -E '^API_DOMAIN=' "$ENV_FILE" | cut -d= -f2- | tr -d '"' || true)"
ADMIN_DOMAIN="$(grep -E '^ADMIN_DOMAIN=' "$ENV_FILE" | cut -d= -f2- | tr -d '"' || true)"
CERTBOT_EMAIL="$(grep -E '^CERTBOT_EMAIL=' "$ENV_FILE" | cut -d= -f2- | tr -d '"' || true)"

[ -n "$APP_DOMAIN" ]   || fail "APP_DOMAIN is not set in $ENV_FILE."
[ -n "$API_DOMAIN" ]   || fail "API_DOMAIN is not set in $ENV_FILE."
[ -n "$ADMIN_DOMAIN" ] || fail "ADMIN_DOMAIN is not set in $ENV_FILE."
[ -n "$CERTBOT_EMAIL" ] || fail "CERTBOT_EMAIL is not set in $ENV_FILE."

mkdir -p "$CERT_DIR" "$WEBROOT"

DOMAINS=("$APP_DOMAIN" "$API_DOMAIN" "$ADMIN_DOMAIN")
MISSING=()

for domain in "${DOMAINS[@]}"; do
    if [ -f "${CERT_DIR}/live/${domain}/fullchain.pem" ]; then
        ok "Certificate already exists for ${domain}."
    else
        MISSING+=("$domain")
    fi
done

[ "${#MISSING[@]}" -gt 0 ] || { ok "All certificates are present. Nothing to do."; exit 0; }

log "Missing certificates for: ${MISSING[*]}"

# Ensure the certbot container image is available and the network exists.
COMPOSE="docker compose -f docker-compose.prod.yml --env-file ${ENV_FILE}"
$COMPOSE pull certbot >/dev/null 2>&1 || log "Could not pull certbot image; using local image if available."

for domain in "${MISSING[@]}"; do
    log "Requesting certificate for ${domain}..."

    $COMPOSE run --rm --entrypoint certbot certbot certonly \
        --webroot \
        -w /var/www/certbot \
        -d "$domain" \
        --agree-tos \
        --no-eff-email \
        -m "$CERTBOT_EMAIL" \
        --non-interactive \
        || fail "Certificate issuance failed for ${domain}."

    ok "Certificate issued for ${domain}."
done

ok "Certificate provisioning complete."
