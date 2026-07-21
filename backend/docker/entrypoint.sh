#!/bin/sh
set -e

# Start PHP-FPM in background
php-fpm -D

# Wait for PHP-FPM to be ready
sleep 2

# Start Nginx in foreground
exec nginx -g 'daemon off;'
