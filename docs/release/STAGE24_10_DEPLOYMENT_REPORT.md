# Stage 24.10 — Companies & Quotes CRM Deployment Report

## Summary

Stage 24.10 delivered Companies/Quotes CRM hardening (company-linked quotes, distributor→company sync, quote status notifications) plus Activity/Applications/Support/Products/Blog UI hotfixes. Production deploy completed; frontend health-check race during `deploy.sh --build` self-resolved — all containers healthy.

## Commit Deployed

- **Branch:** `master`
- **Commit:** `637c327` (`feat(crm): Stage 24.10 Companies and Quotes CRM with UI hotfixes`)
- **Previous production tip before pull:** `b1072a8`
- **Image tag:** `local-20260806111626`
- **Rollback target recorded by deploy script:** `local-20260806063549`
- **Backup:** `/opt/vestra/backups/20260806_111623`

## Migrations applied

- `2026_08_06_100000_add_company_profile_id_to_quote_requests_table` (DONE ~1s; backfills company profiles for existing quotes)

## Post-deploy ops

- `php artisan db:seed --class=NotificationTemplateSeeder --force` — quote status templates
- `optimize:clear` + config/route/view cache + queue restart

## Validation

- Focused PHPUnit (local worktree): **7 passed**  
  `test_admin_can_approve_application_and_creates_distributor`, `test_admin_can_deactivate_company`, `CompanyProfileSyncTest` (2), `test_admin_can_update_quote_status`, `test_public_user_can_submit_quote_request`, `test_authenticated_user_quote_request_links_to_user`
- Production smoke:
  - Containers healthy (backend, frontend, queue, scheduler, nginx, db, redis)
  - Site / API health / admin login: HTTP 200
  - Admin Companies / Quotes: HTTP 302 (auth)

## Scope highlights

- Activity table + standard footer pagination
- Applications approve refreshes status, creates company on approval
- Support KPI `--6` full width; product curated currencies + affix select; blog toolbar active state
- Guest/public quotes create/link `CompanyProfile`; admin status changes notify customers
- Companies deactivate + Create Quote deep-link filter; Quotes `company_profile_id` FK

## Production verification checklist

- [x] Deployed tip `637c327`
- [x] Image `local-20260806111626`
- [x] Migration applied
- [x] Containers healthy after race
- [x] Public/API/admin smoke 200
- [ ] Manual UI: Activity table, Support KPIs, product currencies, article toolbar
- [ ] Manual UI: Approve application → Companies row; public quote → Quotes + company
- [ ] Manual UI: Quote approve/reject → account portal status
