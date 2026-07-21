# VESTRA Environment Variables

## Backend (.env)

| Variable | Description | Example |
|----------|-------------|---------|
| `APP_NAME` | Application name | `VESTRA` |
| `APP_ENV` | Environment | `production` |
| `APP_KEY` | Encryption key | `base64:...` |
| `APP_DEBUG` | Debug mode | `false` |
| `APP_URL` | Backend URL | `https://api.vestra.com` |
| `DB_CONNECTION` | Database driver | `mysql` |
| `DB_HOST` | Database host | `db` |
| `DB_DATABASE` | Database name | `vestra` |
| `DB_USERNAME` | Database user | `vestra` |
| `DB_PASSWORD` | Database password | (secret) |
| `SESSION_DRIVER` | Session storage | `redis` |
| `SESSION_SECURE_COOKIE` | HTTPS-only cookies | `true` |
| `SESSION_SAME_SITE` | SameSite policy | `strict` |
| `CACHE_STORE` | Cache driver | `redis` |
| `QUEUE_CONNECTION` | Queue driver | `redis` |
| `REDIS_HOST` | Redis host | `redis` |
| `REDIS_PASSWORD` | Redis password | (secret) |
| `MAIL_MAILER` | Mail driver | `smtp` |
| `MAIL_HOST` | SMTP host | `smtp.mailgun.org` |
| `MAIL_USERNAME` | SMTP username | (secret) |
| `MAIL_PASSWORD` | SMTP password | (secret) |
| `MAIL_FROM_ADDRESS` | From email | `vestradetergent@gmail.com` |
| `SANCTUM_STATEFUL_DOMAINS` | SPA domains | `vestra.com,www.vestra.com` |
| `FLUTTERWAVE_PUBLIC_KEY` | Flutterwave public key | (secret) |
| `FLUTTERWAVE_SECRET_KEY` | Flutterwave secret key | (secret) |
| `FLUTTERWAVE_ENCRYPTION_KEY` | Flutterwave encryption | (secret) |
| `FLUTTERWAVE_WEBHOOK_SECRET` | Webhook secret | (secret) |
| `FRONTEND_URL` | Frontend URL | `https://vestra.com` |
| `LOG_CHANNEL` | Log channel | `stderr` |
| `LOG_LEVEL` | Log level | `warning` |

## Frontend (.env.local)

| Variable | Description | Example |
|----------|-------------|---------|
| `NEXT_PUBLIC_API_URL` | Backend API URL | `https://api.vestra.com/api/v1` |
| `NEXT_PUBLIC_SITE_URL` | Site URL | `https://vestra.com` |
| `NEXT_PUBLIC_CDN_HOST` | CDN hostname | `cdn.vestra.com` |

## Docker Compose

| Variable | Description |
|----------|-------------|
| `MYSQL_ROOT_PASSWORD` | MySQL root password |
| `DB_PASSWORD` | MySQL application password |
| `REDIS_PASSWORD` | Redis password |
| `NEXT_PUBLIC_API_URL` | Frontend API URL |
