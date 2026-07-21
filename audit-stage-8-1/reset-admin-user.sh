#!/usr/bin/env bash
set -e

docker exec vestra-backend-dev sh -c 'php artisan tinker --execute="\$u=App\\Models\\User::where(\"email\",\"admin@vestra.com\")->first(); if(\$u){\$u->password=\"Admin@12345\";\$u->force_password_change_at=null;\$u->saveQuietly(); echo \"ok\";}"'
