# Stage 24.13B — Production Deployment Report

## Summary

Stage 24.13B (homepage Where to Buy CTA + distributor locator enhancement) is live on production.

Deploy script exited non-zero on the known frontend health race; containers self-healed and smoke checks passed.

## Release identity

| Item | Value |
|------|--------|
| Feature branch | `feature/stage24-13b-homepage-locator` |
| Feature commit | `0ac90be` — `feat(stage24.13b): homepage Where to Buy CTA and distributor locator` |
| Pre-deploy tip | `5443b07` (`v2.1.0-31-g5443b07`) |
| Pre-deploy image | `local-20260807093405` |
| Post-deploy tip | `0ac90be` (`v2.1.0-32-g0ac90be`) |
| Post-deploy image | `local-20260809213146` |
| Rollback target recorded | `local-20260807093405` |
| Backup | `/opt/vestra/backups/20260809_213143` |
| Migration | `2026_08_09_120000_add_locator_fields_to_distributors_table` — DONE |

## Git / merge

1. Validated branch based on `5443b07`; Stage 24.13B-only tree
2. PHPUnit: **31 tests, 114 assertions** OK
3. Commit `0ac90be` on feature branch
4. Fast-forward `develop` → `origin/develop`
5. Fast-forward `master` → `origin/master`
6. Production `/opt/vestra` pulled `master` to `0ac90be` and ran `./scripts/deploy.sh --build`

## Production verification

| Check | Result |
|------|--------|
| Containers healthy on `local-20260809213146` | Pass |
| Homepage 200 + three hero CTAs (`hero-primary-cta`, `hero-secondary-cta`, `hero-where-to-buy-cta`) | Pass |
| Nav items preserved (Home–Contact including Where to Buy) | Pass |
| `/request-quote`, `/distributor`, `/where-to-buy` 200 | Pass |
| Public distributors API 200, `data: []` (empty ACTIVE directory valid) | Pass |
| Invalid `tier=platinum` → 422 | Pass |
| Schema columns `tier`, `whatsapp`, `google_maps_url`, `stock_availability` (+ district/city) | Pass |
| Frontend build contains locator copy (`All tiers`, empty-state, authorized heading) | Pass |
| No fabricated distributor records | Pass |

## Preserved (non-negotiable)

- All 8 primary nav items, mobile drawer, search, login
- Existing Quote / Distributor / Where to Buy pages
- Existing public distributor API foundation + DirectoryList reuse
- Existing homepage Quote + Distributor CTAs (Where to Buy additive only)

## Notes

- Live ACTIVE partner count remains **0**; empty locator state is expected and intentional.
- Admin can set tier / stock / WhatsApp / Maps / location / hours via Filament distributor create/edit and Active Partners Edit without further code deploys for content.
