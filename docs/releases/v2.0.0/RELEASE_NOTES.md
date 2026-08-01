# VESTRA v2.0.0 — Release Notes

## Overview

VESTRA v2.0 relaunches the public platform as a premium B2B corporate website for detergent manufacturing, distribution recruitment, and commercial supply enquiries.

## What Changed

### Business Model

- The website no longer behaves like an online store.
- Retail purchasing, shopping cart, checkout, and payment flows have been removed from the public experience.
- Primary conversions are now:
  - **Request a Quote**
  - **Become a Distributor**
  - **Contact Sales**

### Public Website

- **Homepage** — corporate hero, product categories, industries served, distributor CTA, testimonials, articles.
- **About Us** — company story, mission/vision/values, manufacturing identity, quality commitment.
- **Products** — B2B catalogue with filtering, package sizes, industries, no pricing or cart.
- **Become a Distributor** — partner benefits, application process, real backend-integrated form.
- **Request a Quote** — flagship quotation workflow with multiple products and attachments.
- **Where to Buy** — distribution network and future-ready directory architecture.
- **Blog / Knowledge Centre** — CMS-ready corporate knowledge hub.
- **Contact** — enquiry form, Google Maps, social links, FAQ.

### Backend

- New quotation request system with migrations, service, resource, policy, notifications, and emails.
- Enhanced distributor application workflow.
- Enhanced contact enquiry workflow.
- Blog CMS architecture: authors, categories, tags, posts, views.
- Public distributor directory API and coverage regions.
- Admin route authorization hardening (`can:admin`).
- Default-password guard retained.

### Frontend

- New global design system with consistent typography, spacing, cards, buttons, and forms.
- Corporate-first layouts across all public pages.
- Responsive mobile navigation and footer.
- SEO metadata, sitemap, robots, and structured data.

### Infrastructure

- Docker production stack with health checks.
- Nginx Livewire route fix.
- Render script default.conf cleanup.
- GitHub Actions CI/CD pipeline.

## Migration Notes

- All migrations are additive; rollback is safe for code-only rollbacks.
- Existing customer accounts and admin credentials remain valid.
- No action is required from existing users.

## Known Issues

See `KNOWN_LIMITATIONS.md`.

## Deployment

- Release merged to `master` and tagged `v2.0.0`.
- Deploy workflow triggered: https://github.com/mujuzifil/Vestra/actions/runs/30716076211
- The workflow failed at the Docker registry login step due to missing/incorrect `DOCKER_USERNAME`/`DOCKER_PASSWORD` secrets.
- Production is not yet updated; the previous release remains running.
- To complete the deploy, fix the secrets and re-run the workflow, or run `./scripts/deploy.sh --build` manually on the VPS.
