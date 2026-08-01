# Phase 11 — Known Issues (Backend)

## Environment Limitations

- PHP and Docker are unavailable in this session, so automated tests cannot be run.
- Runtime validation is limited to static review.

## Legacy Commerce Subsystem

- Cart, checkout, wishlist, saved-for-later, and payment endpoints remain registered.
- `carts` and `cart_items` tables remain.
- These are intentional; removal is scheduled for a dedicated cleanup phase.

## Follow-Up Required

1. Run `php artisan test` in CI.
2. Run `php artisan migrate --fresh --seed` on a staging database.
3. Verify queue workers process quote/distributor notifications.
4. Audit email templates for responsive formatting.
