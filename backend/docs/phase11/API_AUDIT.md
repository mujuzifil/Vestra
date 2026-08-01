# Phase 11 — API Audit (Backend)

## Route Authorization Matrix

| Group | Middleware | Notes |
|-------|------------|-------|
| Public | None / throttle | Products, categories, blog, contact, quote, distributor |
| Customer auth | `auth:sanctum` | Profile, addresses, orders (legacy), notifications |
| Distributor | `auth:sanctum` + `distributor` | Portal resources |
| Reports | `auth:sanctum` + `can:view reports` | Business intelligence |
| Admin | `auth:sanctum` + `can:admin` + `RequireAdminPasswordChange` | Fixed in this phase |

## Fixes Applied

- Added `can:admin` gate.
- Applied `can:admin` to the admin-only route group in `routes/api.php`.

## Outstanding

- Legacy cart/checkout/payment routes still registered.
- These should be removed during backend commerce cleanup.

## Recommendations

- Generate OpenAPI documentation for public B2B endpoints.
- Add request/response DTOs for the quote and distributor endpoints.
