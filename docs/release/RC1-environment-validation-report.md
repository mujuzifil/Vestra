# RC1 — Environment Validation Report

## Objective

Verify that all required production environment variables and infrastructure dependencies are present before and after deployment.

## Validation Commands

Run from `/opt/vestra` on the production VPS:

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

## Variable Checklist

| Variable | Purpose | Status |
|----------|---------|--------|
| `APP_DOMAIN` | Public website domain | |
| `API_DOMAIN` | REST API domain | |
| `ADMIN_DOMAIN` | Filament admin domain | |
| `APP_URL` | Laravel application URL | |
| `FRONTEND_URL` | Next.js frontend URL | |
| `DB_HOST` | Database host | |
| `DB_DATABASE` | Database name | |
| `DB_USERNAME` | Database user | |
| `DB_PASSWORD` | Database password | |
| `REDIS_HOST` | Redis host | |
| `REDIS_PASSWORD` | Redis password | |
| `MAIL_MAILER` | Mail driver | |
| `MAIL_HOST` | SMTP host | |
| `MAIL_PORT` | SMTP port | |
| `MAIL_USERNAME` | SMTP user | |
| `MAIL_PASSWORD` | SMTP password | |
| `MAIL_FROM_ADDRESS` | Sender email | |
| `FLUTTERWAVE_PUBLIC_KEY` | Flutterwave public key | |
| `FLUTTERWAVE_SECRET_KEY` | Flutterwave secret key | |
| `FLUTTERWAVE_ENCRYPTION_KEY` | Flutterwave encryption key | |
| `FLUTTERWAVE_WEBHOOK_SECRET` | Flutterwave webhook secret | |
| `SESSION_DOMAIN` | Cookie domain | |
| `SESSION_SECURE_COOKIE` | Secure cookie flag | |
| `SANCTUM_STATEFUL_DOMAINS` | Sanctum domains | |
| `CORS_ALLOWED_ORIGINS` | Allowed CORS origins | |
| `TRUSTED_PROXIES` | Trusted proxy IPs | |

## Infrastructure Checks

| Component | Command | Expected Result | Status |
|-----------|---------|-----------------|--------|
| Database | `docker compose exec db mysqladmin ping -h localhost -u root -p$MYSQL_ROOT_PASSWORD` | `mysqld is alive` | |
| Redis | `docker compose exec redis redis-cli -a $REDIS_PASSWORD --no-auth-warning ping` | `PONG` | |
| Storage | `docker compose exec backend ls -la storage/app/public` | Symlink/public dir exists | |
| Queue | `docker compose ps queue` | `Up (healthy)` | |
| Scheduler | `docker compose ps scheduler` | `Up (healthy)` | |

## Docker Compose Validation

```bash
docker compose -f docker-compose.prod.yml --env-file .env.production config --quiet
```

Expected: silent success (exit code 0).

## Findings

| Finding | Severity | Action |
|---------|----------|--------|
| | | |

## Conclusion

- [ ] All required variables are present.
- [ ] Compose configuration is valid.
- [ ] Database and Redis are reachable.
