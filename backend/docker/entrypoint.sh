#!/bin/bash
# ==============================================================================
# VESTRA — Production backend entrypoint
# ==============================================================================
# Shared by the backend, queue and scheduler services. Role is selected by:
#
#   RUN_MIGRATIONS=true   run migrations (backend service only — never the
#                         queue/scheduler replicas, which would race it)
#   CONTAINER_ROLE        queue | scheduler | (unset = web)
#
# This entrypoint NEVER seeds. Seeding production would overwrite live data.
# ==============================================================================
set -e

log() { echo "[entrypoint] $*"; }

# ------------------------------------------------------------------------------
# Wait for MySQL
# ------------------------------------------------------------------------------
log "Waiting for database at ${DB_HOST:-db}:${DB_PORT:-3306}..."
ATTEMPTS=0
MAX_ATTEMPTS=60
until php -r '
    try {
        new PDO(
            "mysql:host=" . getenv("DB_HOST") . ";port=" . getenv("DB_PORT") . ";dbname=" . getenv("DB_DATABASE"),
            getenv("DB_USERNAME"),
            getenv("DB_PASSWORD"),
            [PDO::ATTR_TIMEOUT => 3]
        );
        exit(0);
    } catch (Throwable $e) {
        exit(1);
    }
' 2>/dev/null; do
    ATTEMPTS=$((ATTEMPTS + 1))
    if [ "$ATTEMPTS" -ge "$MAX_ATTEMPTS" ]; then
        log "FATAL: database unreachable after ${MAX_ATTEMPTS} attempts."
        exit 1
    fi
    sleep 2
done
log "Database is ready."

# ------------------------------------------------------------------------------
# Wait for Redis — cache, queue and sessions all depend on it
# ------------------------------------------------------------------------------
if [ -n "${REDIS_HOST}" ]; then
    log "Waiting for Redis at ${REDIS_HOST}:${REDIS_PORT:-6379}..."
    ATTEMPTS=0
    until php -r '
        try {
            $r = new Redis();
            $r->connect(getenv("REDIS_HOST"), (int) (getenv("REDIS_PORT") ?: 6379), 3);
            if (getenv("REDIS_PASSWORD")) { $r->auth(getenv("REDIS_PASSWORD")); }
            $r->ping();
            exit(0);
        } catch (Throwable $e) {
            exit(1);
        }
    ' 2>/dev/null; do
        ATTEMPTS=$((ATTEMPTS + 1))
        if [ "$ATTEMPTS" -ge 30 ]; then
            log "FATAL: Redis unreachable after 30 attempts."
            exit 1
        fi
        sleep 2
    done
    log "Redis is ready."
fi

# ------------------------------------------------------------------------------
# Migrations — backend service only
# ------------------------------------------------------------------------------
if [ "${RUN_MIGRATIONS}" = "true" ]; then
    log "Running migrations..."
    php artisan migrate --force --no-interaction
    log "Migrations complete."
else
    log "Skipping migrations (RUN_MIGRATIONS=${RUN_MIGRATIONS:-unset})."
fi

# ------------------------------------------------------------------------------
# Storage — without the symlink every uploaded product image 404s
# ------------------------------------------------------------------------------
mkdir -p storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/views \
         storage/logs \
         storage/app/public

log "Linking public storage..."
php artisan storage:link --force || log "WARNING: storage:link failed; uploaded media may 404."

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# ------------------------------------------------------------------------------
# Warm caches
# ------------------------------------------------------------------------------
# The image builds these already. Re-running at start is what picks up the
# runtime environment — a config cache baked at build time holds build-time
# values, not the production secrets injected here.
log "Warming caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
log "Caches warmed."

# ------------------------------------------------------------------------------
# Dispatch by role
# ------------------------------------------------------------------------------
if [ "$#" -gt 0 ]; then
    log "Starting role '${CONTAINER_ROLE:-custom}': $*"
    exec "$@"
fi

log "Starting web role (php-fpm + nginx)."
php-fpm -D
sleep 2
exec nginx -g 'daemon off;'
