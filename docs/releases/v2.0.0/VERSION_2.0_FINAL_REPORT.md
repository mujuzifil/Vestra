# VESTRA Version 2.0 — Final Release Report

> **Update:** The v2.0.0 release was tagged and merged to `master` but the automated production deployment failed. The code was subsequently deployed manually as **v2.0.1**. See `docs/releases/v2.0.1/VERSION_2.0_FINAL_REPORT.md` for the actual deployed release report.

## Summary

VESTRA Version 2.0 transforms the platform from an e-commerce storefront into a B2B corporate website focused on commercial enquiries, distributor partnerships, and institutional supply.

## Release Details

| Field | Value |
|-------|-------|
| Version | v2.0.0 |
| Merge commit | `d426966` |
| Tag | `v2.0.0` |
| Date | 2026-08-01 |

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
   - Email confirmations and admin notifications (requires SMTP credentials in production).

4. **Backend Hardening**
   - Admin API routes protected by `can:admin`.
   - Default-password guard active.

5. **Infrastructure**
   - Docker production stack.
   - SSL, backups, queue workers, scheduler, and monitoring.

## Deployment Outcome

- Automated GitHub Actions deployment failed at the Docker registry login step.
- Manual deployment succeeded as v2.0.1 with hotfixes.
- See `docs/releases/v2.0.1/` for validation results and remaining items.

## Rollback

Rollback instructions are in `docs/releases/v2.0.1/ROLLBACK_PLAN.md`.

## Conclusion

Version 2.0 code is merged to `master` and tagged `v2.0.0`. Production is running the hotfixed release `v2.0.1`. The only remaining operational item is configuring SMTP credentials for email delivery.
