# Stage 24.11 — v2.1.0 Production Hardening Deployment Report

## Summary

Stage 24.11 / **v2.1.0** coordinated Public Website + Admin Portal deploy completed after Release Gate **PASS**. Frontend health check in `deploy.sh` timed out (known race); containers self-resolved to healthy; public/API/admin smoke returned 200.

## Commit Deployed

- **Branch:** `master`
- **Tag:** `v2.1.0`
- **Commit:** `0cf9187` (`release(v2.1.0): sign Stage 24.11 gate, docs, version bump, sitemap build fix`)
- **Image tag:** `local-20260806140735`
- **Rollback target recorded by deploy script:** `local-20260806111626`
- **Backup:** `/opt/vestra/backups/20260806_140732`

## Migrations applied

- `2026_08_06_120000_add_stage24_11_list_performance_indexes` (DONE ~395ms)

## Post-deploy ops

- `NotificationTemplateSeeder --force`
- `optimize:clear` + config/route/view cache + queue restart

## Validation

- Local Feature suite: **630 passed** (2554 assertions)
- Frontend eslint exit 0; `npm run build` success
- Production smoke:
  - All compose services healthy (backend, frontend, queue, scheduler, nginx, db, redis)
  - Site / API health / admin login: HTTP 200

## Gate

[STAGE24_11_RELEASE_GATE.md](STAGE24_11_RELEASE_GATE.md) — PASS (KI-001 residual risk accepted; operator rotation still required)

## Rollback

```bash
cd /opt/vestra && ./scripts/rollback.sh
```

See [ROLLBACK_CHECKLIST.md](ROLLBACK_CHECKLIST.md). Additive indexes only — image rollback is safe for code.
