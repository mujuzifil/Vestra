# VESTRA v2.0.1 — Release Notes

## Overview

VESTRA v2.0.1 is the production hotfix release for the Version 2.0 corporate website launch. It deploys the same B2B feature set as v2.0.0 and resolves three deployment-time issues discovered during manual deployment on the production VPS.

## What's New in v2.0 (Compared to v1.x)

- Complete removal of retail e-commerce UX from the public website.
- New corporate homepage, about, products, distributor, quote, where-to-buy, blog, and contact pages.
- B2B lead-generation workflows: Request a Quote, Become a Distributor, Contact Sales.
- Filament admin resources for quotes, distributors, contact messages, and blog CMS.
- Customer account portal remains available for future distributor and commercial features.
- New design system with consistent typography, spacing, cards, buttons, and forms.

## Hotfixes in v2.0.1

1. **Nginx duplicate `default_server` fix**
   - Prevents nginx restart loop by removing the bootstrap `default.conf` before the SSL vhost is rendered.

2. **Frontend runtime `curl` health probe**
   - Installs `curl` in the frontend runtime image so Docker healthchecks pass.

3. **Filament `BlogPostResource.php` syntax fix**
   - Removes an invalid trailing comma that caused backend image builds to fail.

## Deployment Information

- **Deployed:** 2026-08-01
- **Server:** 187.77.84.119
- **Image tag:** `local-20260801233059`
- **Domains:**
  - https://vestradetergents.com
  - https://api.vestradetergents.com
  - https://admin.vestradetergents.com

## Known Issue

- Email notifications are not yet configured. `MAIL_USERNAME` and `MAIL_PASSWORD` must be added to `/opt/vestra/.env.production` for quote and contact forms to complete successfully.

## Upgrade Notes

For future deployments from GitHub, ensure `DOCKER_USERNAME` and `DOCKER_PASSWORD` repository secrets are configured, or continue using `./scripts/deploy.sh --build` on the VPS.
