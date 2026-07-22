# Stage 9.1.5 — API Security Hardening

## 1. Executive Summary

Stage 9.1.5 closes Phase 9 (Security Remediation) by hardening the public and administrative API surface, resolving the pre-existing PHPUnit isolation failures, and verifying the frontend production build.

Key outcomes:

- Explicit CORS configuration added with production-safe origin controls.
- API request validation tightened across all public and authenticated endpoints.
- Rate limiting extended to public forms (`distributor`, `feedback`) and the payment webhook.
- Webhook callback logging hardened so secrets and signatures are never logged.
- Security headers, CORS, public-settings ciphertext, and webhook signature verification are now covered by automated tests.
- PHPUnit suite now passes with **zero failures** (79 tests, 762 assertions).
- Next.js production build completes successfully; the previously documented `<Html>` / `_document` error is no longer reproducible.

**Recommendation: PASS** — the platform is certified as **SECURITY REMEDIATION COMPLETE** and ready to proceed to Phase 10 — Commerce Integrity.

---

## 2. API Surface Inventory

The v1 API surface is defined in `backend/routes/api.php`.

| Category | Endpoints | Protection |
|----------|-----------|------------|
| Public health | `GET /api/v1/health`, `/health/ready`, `/health/live` | None |
| Public catalog | `GET /api/v1/categories`, `/products`, `/products/{slug}`, `/products/{slug}/reviews` | None |
| Public settings | `GET /api/v1/settings` | Returns only `is_public` settings |
| Public forms | `POST /api/v1/contact`, `/distributor`, `/feedback` | Throttle per endpoint |
| Auth | `POST /api/v1/auth/register`, `/auth/login` | Throttle |
| Authenticated | Profile, addresses, cart, orders, checkout, payments, reviews | `auth:sanctum` |
| Reports | `GET /api/v1/reports/*` | `auth:sanctum` + `can:view reports` |
| Admin moderation | `GET /api/v1/admin/reviews`, etc. | `auth:sanctum` + `RequireAdminPasswordChange` |
| Webhook | `POST /api/v1/payments/callback` | HMAC signature verification + throttle |

No file-upload endpoints exist in the API.

---

## 3. OWASP API Security Assessment

| OWASP API Risk | Status | Notes |
|----------------|--------|-------|
| API1 — Broken Object Level Authorization | ✅ Mitigated | Ownership enforced by policies/controllers (9.1.4). |
| API2 — Broken Authentication | ✅ Mitigated | Sanctum tokens, password policy, session security (9.1.3). |
| API3 — Broken Object Property Level Authorization | ✅ Mitigated | Mass-assignment fields guarded; `exclude`/`prohibited` rules added. |
| API4 — Unrestricted Resource Consumption | ✅ Mitigated | Pagination, throttling, max lengths, numeric bounds. |
| API5 — Broken Function Level Authorization | ✅ Mitigated | Policies, gates, middleware, role checks. |
| API6 — Unrestricted Access to Sensitive Business Flows | ✅ Mitigated | Throttling on checkout, payments, forms. |
| API7 — Server Side Request Forgery | ✅ Mitigated | Payment gateway uses fixed Flutterwave URLs; no user-controlled URLs. |
| API8 — Security Misconfiguration | ✅ Mitigated | CORS, security headers, production error handling. |
| API9 — Improper Inventory Management | ✅ Mitigated | Complete route inventory above; no hidden endpoints found. |
| API10 — Unsafe Consumption of APIs | ✅ Mitigated | Webhook signatures verified; external API errors handled generically. |

---

## 4. Request Validation Review

All `backend/app/Http/Requests/Api/V1/*.php` Form Requests were reviewed and hardened:

- Every string field now has an explicit `max:` rule.
- `Rule::enum` replaces loose `in:` arrays where enums exist (`PaymentMethod`, `ProductStatus`, `FeedbackCategory`).
- Email fields use `string|email|max:255`.
- Numeric fields have upper bounds where applicable.
- `StoreAddressRequest` and `UpdateProfileRequest` use `exclude` for mass-assignment attempts (`user_id`, `status`, `role`, etc.) so they are silently dropped.
- `RegisterRequest` retains `prohibited` for privilege-escalation fields because public registration must reject them.

Files updated:

- `backend/app/Http/Requests/Api/V1/StoreContactRequest.php`
- `backend/app/Http/Requests/Api/V1/StoreDistributorRequest.php`
- `backend/app/Http/Requests/Api/V1/StoreFeedbackRequest.php`
- `backend/app/Http/Requests/Api/V1/StoreReviewRequest.php`
- `backend/app/Http/Requests/Api/V1/UpdateProfileRequest.php`
- `backend/app/Http/Requests/Api/V1/StoreAddressRequest.php`
- `backend/app/Http/Requests/Api/V1/CheckoutRequest.php`
- `backend/app/Http/Requests/Api/V1/LoginRequest.php`
- `backend/app/Http/Requests/Api/V1/CustomerLoginRequest.php`

---

## 5. Response Hardening

- `SettingResource` returns only the already-filtered public list (`is_public = true`).
- Public settings response verified to never contain ciphertext (`eyJpdiI6…`).
- Production API exception responses do not expose `exception`, `file`, `line`, or `trace`.
- Webhook signature failures return a generic `Invalid webhook signature.` message with no payload or secret details.

---

## 6. Exception Handling Review

`backend/bootstrap/app.php` registers production-safe renderables for:

- `AuthenticationException` → 401 JSON
- `ValidationException` → 422 JSON with validation errors
- `AuthorizationException` / `AccessDeniedHttpException` → 403 JSON + audit log
- `ModelNotFoundException` / `NotFoundHttpException` → 404 JSON
- `ThrottleRequestsException` → 429 JSON + login lockout audit
- Generic `Throwable` → 500 JSON; debug details only when `APP_DEBUG=true`

---

## 7. Security Headers Review

`backend/app/Http/Middleware/SecurityHeaders.php` applies:

- `X-Frame-Options: DENY`
- `X-Content-Type-Options: nosniff`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy: camera=(), microphone=(), geolocation=()`
- `Strict-Transport-Security` (production only)
- `Content-Security-Policy`
- Removes `X-Powered-By`

Automated tests verify all of the above in `backend/tests/Feature/Api/V1/SecurityHeadersTest.php`.

---

## 8. CORS Review

A new `backend/config/cors.php` was created:

- Paths limited to `api/*`, `sanctum/csrf-cookie`, auth, and admin routes.
- `allowed_origins` driven by `CORS_ALLOWED_ORIGINS` / `FRONTEND_URL` env vars.
- No wildcard origins in production.
- `supports_credentials` enabled for stateful Sanctum requests.

Tests in `backend/tests/Feature/Api/V1/CorsSecurityTest.php` verify headers and production origin restrictions.

---

## 9. File Upload Security

No API file-upload endpoints were identified. Upload handling is out of scope for this stage and remains a future review item if/when uploads are added.

---

## 10. Webhook Security

- `PaymentCallbackRequest` verifies the Flutterwave HMAC-SHA256 signature via `verif-hash` header.
- Invalid or missing signatures return 403 without exposing the secret or payload.
- `PaymentController::callback` logs webhook processing failures at warning level, recording only `tx_ref`, `status`, IP, and a generic message.
- A dedicated `webhook` rate limiter (30/min per IP) protects `/api/v1/payments/callback` from flooding.

---

## 11. Middleware Review

`backend/bootstrap/app.php` API middleware stack:

```php
HandleCors::class,
TrustProxies::class,
SecurityHeaders::class,
```

Order is correct: CORS first, then proxy trust, then security headers. Authentication, authorization, and throttle middleware are applied per route.

---

## 12. PHPUnit Results

### Backend test suite

```bash
docker compose -f docker-compose.dev.yml exec -T backend php artisan test
```

Result:

```
Tests:    79 passed (762 assertions)
Duration: 148.41s
```

All previously failing tests now pass:

- `ApiEndpointsTest` — admin password-change and exchange-token state isolation resolved.
- `AuthenticationSecurityTest` — rate-limiter state leak resolved by forcing RateLimiter onto the array cache.
- `AdminUserSeederTest` — preserved-password test now passes after removing the testing-environment auto-reset override.

New test coverage added:

- `backend/tests/Feature/Api/V1/CorsSecurityTest.php`
- `backend/tests/Feature/Api/V1/SecurityHeadersTest.php`
- `backend/tests/Feature/Api/V1/PublicSettingsSecurityTest.php`
- `backend/tests/Feature/Api/V1/ApiExceptionSecurityTest.php`
- `backend/tests/Feature/Api/V1/WebhookSecurityTest.php`

### Frontend production build

```bash
cd frontend && npm run build
```

Result: successful static generation with Next.js 15.5.20 + Turbopack. No `<Html>` / `next/document` error. The previously documented build failure is no longer reproducible.

---

## 13. Files Modified

- `backend/config/cors.php` (new)
- `backend/app/Providers/RateLimitServiceProvider.php`
- `backend/routes/api.php`
- `backend/app/Http/Requests/Api/V1/StoreContactRequest.php`
- `backend/app/Http/Requests/Api/V1/StoreDistributorRequest.php`
- `backend/app/Http/Requests/Api/V1/StoreFeedbackRequest.php`
- `backend/app/Http/Requests/Api/V1/StoreReviewRequest.php`
- `backend/app/Http/Requests/Api/V1/UpdateProfileRequest.php`
- `backend/app/Http/Requests/Api/V1/StoreAddressRequest.php`
- `backend/app/Http/Requests/Api/V1/CheckoutRequest.php`
- `backend/app/Http/Requests/Api/V1/LoginRequest.php`
- `backend/app/Http/Requests/Api/V1/CustomerLoginRequest.php`
- `backend/app/Http/Controllers/Api/V1/PaymentController.php`
- `backend/app/Http/Requests/Api/V1/PaymentCallbackRequest.php`
- `backend/database/seeders/AdminUserSeeder.php`
- `backend/tests/TestCase.php`
- `backend/tests/Feature/Api/V1/ApiEndpointsTest.php`
- `backend/tests/Feature/AuthenticationSecurityTest.php`
- `backend/tests/Feature/Api/V1/CorsSecurityTest.php` (new)
- `backend/tests/Feature/Api/V1/SecurityHeadersTest.php` (new)
- `backend/tests/Feature/Api/V1/PublicSettingsSecurityTest.php` (new)
- `backend/tests/Feature/Api/V1/ApiExceptionSecurityTest.php` (new)
- `backend/tests/Feature/Api/V1/WebhookSecurityTest.php` (new)
- `docs/remediation/STAGE_9_1_5_API_SECURITY_HARDENING.md` (new)

---

## 14. Remaining Risks

- **No file upload endpoints** currently exist; if added later, they require dedicated MIME, extension, size, and storage-security review.
- **No external secret manager** is in place (out of scope for Phase 9).
- **CORS origin values** must be set correctly per environment (`CORS_ALLOWED_ORIGINS` / `FRONTEND_URL`).
- **Rate limits** should be tuned based on production traffic patterns.

---

## 15. Recommendation

**PASS**

Stage 9.1.5 has eliminated the remaining API security findings from the Stage 8.11 Production Readiness Audit, restored a fully passing backend PHPUnit suite, verified the frontend production build, and closed Phase 9. The platform is ready to proceed to Phase 10 — Commerce Integrity.
