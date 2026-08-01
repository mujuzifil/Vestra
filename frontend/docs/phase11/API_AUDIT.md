# Phase 11 — API Audit (Frontend Perspective)

## Public APIs

All public endpoints used by the B2B website are reachable and throttled where appropriate:

- `GET /api/v1/products`, `/api/v1/products/{slug}`
- `GET /api/v1/categories`
- `GET /api/v1/blog/posts`, `/api/v1/blog/posts/{slug}`
- `POST /api/v1/quote-requests`
- `POST /api/v1/distributor`
- `POST /api/v1/contact`

## Authenticated APIs

- Customer account routes require `auth:sanctum`.
- Distributor portal routes require the `distributor` middleware.
- Admin-only routes now require both `can:admin` and `RequireAdminPasswordChange`.

## Previous Fix

- Invoice download duplicate `/api/v1/api/v1` path was corrected in a prior stage.

## Recommendations

- Remove obsolete cart/checkout/payment endpoints from the public API once the backend cleanup is complete.
- Confirm rate limiting on all mutation endpoints.
