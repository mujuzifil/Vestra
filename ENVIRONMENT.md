# VESTRA Environment Variables

Full reference: [Environment Configuration Guide](docs/release/ENVIRONMENT_CONFIGURATION_GUIDE.md)
Template: [`.env.production.example`](.env.production.example)

## The distinction that matters

| Kind | Variables | Read at | To change |
|---|---|---|---|
| **Build-time** | `NEXT_PUBLIC_*` | `next build` | **Rebuild the frontend image** |
| **Run-time** | everything else | container boot | Restart the container |

`NEXT_PUBLIC_*` values are compiled into the client bundle as string literals.
Setting them as container environment has no effect on JavaScript already served
to the browser.

## Required — the stack refuses to start without these

`APP_KEY` · `APP_URL` · `FRONTEND_URL` · `CORS_ALLOWED_ORIGINS` · `DB_PASSWORD` ·
`MYSQL_ROOT_PASSWORD` · `REDIS_PASSWORD` · `NEXT_PUBLIC_API_URL` ·
`NEXT_PUBLIC_SITE_URL` · `NEXT_PUBLIC_BACKEND_URL`

Each was silently broken or absent before Phase 15, so the compose file now
fails fast rather than starting in a degraded state.

## Two that are easy to get wrong

**`APP_KEY` is permanent.** Sensitive settings are encrypted at rest with it.
Changing it after go-live makes them permanently undecryptable.

**`TRUSTED_PROXIES` must be set.** Without it Laravel treats nginx as the client:
per-user rate limits collapse into one shared bucket, audit logs record the proxy
address, and HTTPS detection fails.

## For developers

Never call `env()` outside `config/*.php`. `config:cache` runs on every
production boot and makes it return null — code that does this works in
development and silently uses defaults in production. Add the value to
`config/app.php` and read it with `config()`.
`ProductionConfigIntegrityTest` fails the build otherwise.
