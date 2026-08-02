# VESTRA v2.0.1 — Launch Checklist

## Pre-Launch

- [x] All phases 1–11 complete and merged to `develop`.
- [x] Hotfix for `BlogPostResource.php` syntax error applied.
- [x] `develop` merged into `master`.
- [x] Tag `v2.0.1` created and pushed.
- [x] Production server inspected.
- [x] Pre-deployment backup created.

## Deployment

- [x] Latest code pulled/merged on production server.
- [x] Docker images built successfully.
- [x] Migrations run (`php artisan migrate --force`).
- [x] Laravel caches cleared and warmed.
- [x] Queue worker restarted.
- [x] Scheduler verified.

## Post-Launch Validation

- [x] All Docker containers healthy.
- [x] API health endpoint returns 200.
- [x] Public website pages load (home, about, products, distributor, quote, where-to-buy, blog, contact).
- [x] Product detail page loads.
- [x] Admin login page loads.
- [x] Customer registration succeeds.
- [x] Distributor form submits and persists.
- [ ] Quote form submits and persists.
- [ ] Contact form submits and persists.
- [ ] Customer/admin email notifications delivered.
- [ ] Admin login succeeds.
- [ ] Full admin dashboard smoke test passed.

## Configuration

- [x] SSL certificates valid.
- [x] Nginx serving traffic correctly.
- [x] Redis operational.
- [x] MySQL operational.
- [ ] `MAIL_USERNAME` and `MAIL_PASSWORD` configured.
- [ ] `BOOTSTRAP_ADMIN_PASSWORD` rotated (admin `must_change_password` is `no`).

## Notes

- Deployment is live and functional.
- Mail configuration is the remaining item before full launch acceptance.
- Certbot dry-run was rate-limited; certificates are valid and auto-renewal is configured.
