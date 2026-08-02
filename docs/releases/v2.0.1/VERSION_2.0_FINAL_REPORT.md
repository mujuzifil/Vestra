# VESTRA Version 2.0 — Final Release Report (v2.0.1 Hotfix)

## Summary

VESTRA Version 2.0 transforms the platform from an e-commerce storefront into a B2B corporate website focused on commercial enquiries, distributor partnerships, and institutional supply. The production deployment was completed manually on the VPS after the GitHub Actions workflow failed due to missing Docker registry credentials. A hotfix (`v2.0.1`) was required to correct a PHP syntax error in the Filament blog resource.

## Release Details

| Field | Value |
|-------|-------|
| Version | v2.0.1 |
| Master commit | `b478a01` |
| Develop commit | `ca65269` |
| Server deployed commit | `f211605` |
| Tag | `v2.0.1` |
| Previous tag | `v2.0.0` (`d426966`) |
| Date | 2026-08-01 |
| Image tag | `local-20260801233059` |

## What Was Delivered

1. **Public Website Restructure**
   - Removed shopping cart, checkout, and retail purchasing UX.
   - Replaced with Request a Quote, Become a Distributor, and Contact Sales CTAs.
   - New navigation, footer, mobile menu, and routing.

2. **Corporate Pages**
   - Home, About, Products, Distributor, Quote, Where to Buy, Blog, Contact.
   - Premium design system applied consistently.

3. **Business Workflows**
   - Fully backend-integrated quote, distributor, and contact forms.
   - Filament admin resources for sales and operations teams.
   - Email confirmations and admin notifications (blocked pending SMTP credentials).

4. **Backend Hardening**
   - Admin API routes protected by `can:admin`.
   - Default-password guard active.
   - Admin user password verified as changed.

5. **Infrastructure**
   - Docker production stack deployed.
   - SSL, backups, queue workers, scheduler, and monitoring in place.

## Deployment Issues Resolved

| Issue | Cause | Fix |
|-------|-------|-----|
| Nginx restart loop | Duplicate `default_server` on port 80 | Remove bootstrap `default.conf` before rendering SSL vhost |
| Frontend healthcheck failure | `curl` missing from runtime image | Install `curl` in frontend runtime stage |
| Backend build failure | Syntax error in `BlogPostResource.php` line 116 | Remove trailing comma after `->label()` |

## Validation

- Public website loads on all tested pages.
- API health endpoint returns 200 with all checks passing.
- Customer registration succeeds.
- Distributor form submits and persists.
- Quote and contact forms validate input but fail at email dispatch due to missing SMTP credentials.
- Queue empty, scheduler active, no post-deployment failed jobs.
- SSL certificates valid for 80+ days.

## Known Limitations

- **Email delivery is not configured.** `MAIL_USERNAME` and `MAIL_PASSWORD` are empty in `.env.production`. This is the only remaining blocker for full production acceptance.
- Legacy commerce APIs remain registered but unreachable from the public site.
- Blog and distributor directory are CMS-ready but require content population.
- SMS notifications currently log instead of send.

## Rollback

Rollback target image: `local-20260801232957`

```bash
cd /opt/vestra
export IMAGE_TAG=local-20260801232957
docker compose -f docker-compose.prod.yml --env-file .env.production up -d --no-build
```

All v2.0.x migrations are additive, so a code-only rollback is safe.

## Conclusion

Version 2.0.1 is deployed and live on production. The corporate website is operational and accepting distributor applications. Quote and contact submissions are received by the backend but return HTTP 500 to the user because the SMTP credentials are not configured. Once `MAIL_USERNAME` and `MAIL_PASSWORD` are provided in `.env.production` and containers are recreated, the platform will be fully operationally ready for B2B lead generation and distributor recruitment.

## Next Steps

1. Configure `MAIL_USERNAME` and `MAIL_PASSWORD` in `/opt/vestra/.env.production`.
2. Recreate backend/queue/scheduler containers to load new mail config.
3. Re-validate quote and contact form submissions and email delivery.
4. Monitor error logs and queue health for 24 hours.
5. Populate blog articles and distributor records.
6. Schedule backend commerce cleanup.
7. Run Lighthouse and accessibility scans.
