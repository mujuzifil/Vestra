#!/bin/bash
set -e

# Wait for MySQL to be ready
echo "Waiting for MySQL..."
until php -r "try { new PDO('mysql:host=${DB_HOST:-mysql};port=${DB_PORT:-3306};dbname=${DB_DATABASE:-vestra}', '${DB_USERNAME:-vestra}', '${DB_PASSWORD:-vestrasecret}', [PDO::ATTR_TIMEOUT => 3]); echo 'OK'; } catch (Throwable \$e) { echo 'WAIT'; }" 2>/dev/null | grep -q "OK"; do
  sleep 2
done

echo "MySQL is ready."

# Ensure the public storage symlink exists
echo "Linking public storage..."
php artisan storage:link --force || true

# Run migrations and seeders
echo "Running migrations..."
php artisan migrate --force

echo "Running seeders..."
php artisan db:seed --force

# Execute the CMD from the Dockerfile or docker-compose
exec "$@"
