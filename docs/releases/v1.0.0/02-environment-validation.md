# RG1 — Environment Validation

## Objective

Verify that all production environment variables and infrastructure dependencies are present and operational.

## Environment Variable Validation

```bash
cd /opt/vestra
for VAR in APP_DOMAIN API_DOMAIN ADMIN_DOMAIN APP_URL FRONTEND_URL \
           DB_HOST DB_DATABASE DB_USERNAME DB_PASSWORD \
           REDIS_HOST REDIS_PASSWORD \
           MAIL_MAILER MAIL_HOST MAIL_PORT MAIL_USERNAME MAIL_PASSWORD MAIL_FROM_ADDRESS \
           FLUTTERWAVE_PUBLIC_KEY FLUTTERWAVE_SECRET_KEY FLUTTERWAVE_ENCRYPTION_KEY FLUTTERWAVE_WEBHOOK_SECRET \
           SESSION_DOMAIN SESSION_SECURE_COOKIE SANCTUM_STATEFUL_DOMAINS \
           CORS_ALLOWED_ORIGINS TRUSTED_PROXIES; do
    VALUE="$(grep -E "^${VAR}=" .env.production | cut -d= -f2- || true)"
    [ -n "$VALUE" ] && echo "✅ $VAR" || echo "❌ $VAR MISSING"
done
```

| Variable | Status | Notes |
|----------|--------|-------|
| `APP_DOMAIN` | | |
| `API_DOMAIN` | | |
| `ADMIN_DOMAIN` | | |
| `APP_URL` | | |
| `FRONTEND_URL` | | |
| `DB_HOST` | | |
| `DB_DATABASE` | | |
| `DB_USERNAME` | | |
| `DB_PASSWORD` | | |
| `REDIS_HOST` | | |
| `REDIS_PASSWORD` | | |
| `MAIL_MAILER` | | |
| `MAIL_HOST` | | |
| `MAIL_PORT` | | |
| `MAIL_USERNAME` | | |
| `MAIL_PASSWORD` | | |
| `MAIL_FROM_ADDRESS` | | |
| `FLUTTERWAVE_PUBLIC_KEY` | | |
| `FLUTTERWAVE_SECRET_KEY` | | |
| `FLUTTERWAVE_ENCRYPTION_KEY` | | |
| `FLUTTERWAVE_WEBHOOK_SECRET` | | |
| `SESSION_DOMAIN` | | |
| `SESSION_SECURE_COOKIE` | | |
| `SANCTUM_STATEFUL_DOMAINS` | | |
| `CORS_ALLOWED_ORIGINS` | | |
| `TRUSTED_PROXIES` | | |

## Infrastructure Checks

| Component | Command | Expected | Actual |
|-----------|---------|----------|--------|
| Docker Compose config | `docker compose -f docker-compose.prod.yml --env-file .env.production config --quiet` | Silent success | |
| MariaDB ping | `docker compose exec db mysqladmin ping ...` | `mysqld is alive` | |
| Redis ping | `docker compose exec redis redis-cli -a ... ping` | `PONG` | |
| Backend health | `docker compose exec backend curl -fsS http://localhost:8080/api/v1/health` | 200 OK | |
| Storage symlink | `docker compose exec backend ls -la public/storage` | Symlink present | |
| Queue worker | `docker compose ps queue` | `Up (healthy)` | |
| Scheduler | `docker compose ps scheduler` | `Up (healthy)` | |

## Findings

| Finding | Severity | Action |
|---------|----------|--------|
| | | |

## Conclusion

- [ ] All required variables are present.
- [ ] Docker Compose configuration is valid.
- [ ] Database and Redis are reachable.
- [ ] Backend health endpoint returns 200.
- [ ] Queue worker and scheduler are running.
