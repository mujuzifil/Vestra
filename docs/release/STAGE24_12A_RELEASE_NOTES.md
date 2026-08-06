# Stage 24.12A — Public Website Production Fixes & Account Data Synchronization

## Objective

Stabilize production after Stage 24.12: restore blocked Contact map, fix blank Become a Distributor page, ship VESTRA favicons, and synchronize Security page timestamps with real authentication data.

## Root causes

| Issue | Root cause |
| --- | --- |
| Contact map “This content is blocked” | CSP `default-src 'self'` with no `frame-src`, so Google Maps iframes were blocked by the browser |
| Become a Distributor blank (footer only) | `app/distributor/layout.tsx` wrapped the **public** marketing route in `DistributorLayout`, which returns `null` / redirects guests to login |
| Default favicon | No `favicon.ico` / PWA icons; metadata did not declare VESTRA icons |
| Last Login “Not available” | `UnifiedLoginController` audited logins but never wrote `users.last_login_at` |

## Functional fixes

1. **Map** — Allow `frame-src` / `child-src` for Google Maps hosts; use `https://www.google.com/maps?...&output=embed`; Directions opens `maps/dir/?api=1&destination=…`.
2. **Distributor marketing** — Public routes (`/distributor`, `/distributor/success`) render without the portal shell; portal routes keep `DistributorLayout`.
3. **Favicon** — Generated ICO/PNG/Apple/Android icons from the official logo; metadata + manifest use `?v=2412a` cache bust; Filament admin uses `favicon.ico?v=2412a`.
4. **Security UI** — Last Login / Last Password Change formatted as `06 Aug 2026 • 22:41 EAT`; missing password change shows “Using original account password”.

## Backend synchronization

- Successful `/api/v1/auth/login` sets `last_login_at` and continues to write audit `login` events (IP/UA on `audit_logs`).
- `UnifiedLoginResource` and `CustomerResource` expose `last_login_at` / `password_changed_at`.
- Password changes already set `password_changed_at` via `clearPasswordChangeRequired()`.

## Validation

- PHPUnit: customer login asserts `last_login_at` + audit row; AccountProfile + AuthenticationSecurity suites green.
- Frontend production build succeeded.
- Account pages reviewed for placeholder text masking live API fields (Security fixed; remaining `—` only for empty optional fields).

## Deployment

See `STAGE24_12A_DEPLOYMENT_REPORT.md` after live deploy.
