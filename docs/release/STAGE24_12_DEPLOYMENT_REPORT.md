# Stage 24.12 — Deployment Report

## Summary

Stage 24.12 Public Website UX & Account Experience Hardening deployed to production. `deploy.sh` frontend health check timed out (known race); containers self-resolved to healthy; public smoke returned HTTP 200.

## Commit Deployed

- **Branch:** `master`
- **Commit:** `9c47cb1`
- **Image tag:** `local-20260806194739`
- **Rollback target recorded by deploy script:** `local-20260806140735`
- **Backup:** `/opt/vestra/backups/20260806_194736`

## Migrations

- None pending (Stage 24.12 was frontend/API resource fields only)

## Post-deploy ops

- `optimize:clear` + config/route/view cache + queue restart

## Validation

- Containers healthy (backend, frontend, queue, scheduler, nginx, db, redis)
- Site / API / admin / account / contact: HTTP 200

## Scope live

- Account distributor dashboard (approved)
- Business Portal logo
- Public nav single-line labels
- About Household icon
- Contact map embed
- Preferences / Security / Profile layout & password change

## Rollback

```bash
cd /opt/vestra && ./scripts/rollback.sh
```
