# Phase 11 — Infrastructure Audit (Backend)

## Docker

- Laravel Dockerfile uses PHP 8.2 with required extensions.
- Queue worker and scheduler are defined in compose files.

## Nginx

- `backend/docker/nginx/default.conf` includes a Livewire route override to prevent static-file mismatches.
- `scripts/render-nginx.sh` removes the bootstrap `default.conf` before rendering the SSL vhost.

## SSL / Security

- Production uses Let's Encrypt certificates via `init-certs.sh`.
- Automatic renewal should be verified on the server.

## Recommendations

- Confirm queue workers restart on deployment.
- Verify log rotation for Laravel, Nginx, and queue logs.
- Test rollback procedure before production release.
