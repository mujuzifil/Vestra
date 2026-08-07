# Stage 24.12A — Deployment Report

## Summary

Stage 24.12A production stabilization deployed to live Public Website and Business Portal. `deploy.sh --build` hit the known frontend health-check race; containers self-resolved to healthy. Live smoke returned HTTP 200 for site, API, admin, contact, distributor, account, and favicon.

## Commit Deployed

- **Branch:** `master`
- **Commit:** `e41cc4d` (merge of `feature/stage24-12a-prod-fixes` / `30b6216`)
- **Image tag:** `local-20260806201608`
- **Rollback target recorded by deploy script:** `local-20260806194739`
- **Backup:** `/opt/vestra/backups/20260806_201606`

## Migrations

- None pending

## Post-deploy ops

- `optimize:clear` + config/route/view cache + queue restart

## Live validation

| Check | Result |
| --- | --- |
| Containers healthy | backend, frontend, queue, scheduler, nginx, db, redis |
| `https://vestradetergents.com/` | 200 |
| API health | 200 |
| Admin login | 200 |
| `/contact` | 200; CSP includes `frame-src` for Google Maps |
| `/distributor` | 200; HTML includes “Become a Distributor” and “Distributor FAQ” |
| `/favicon.ico?v=2412a` | 200 `image/x-icon` |

## Scope live

- Contact map embed unblocked (CSP `frame-src`)
- Public Become a Distributor marketing page restored
- VESTRA favicon / Apple / Android / PWA icons with `v=2412a` cache bust
- `last_login_at` persisted on successful API login; Security page EAT formatting

## Rollback

```bash
cd /opt/vestra && ./scripts/rollback.sh
```
