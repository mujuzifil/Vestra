# Stage 9.1.3 — Authentication Hardening

## 1. Executive Summary

This stage hardened the VESTRA authentication subsystem to production-grade standards. All work focused on session security, token hygiene, rate limiting, authentication logging, and proxy trust configuration. No new application features were introduced.

**Result:** `PASS WITH OBSERVATIONS`

- Backend PHPUnit suite: **51 passed, 0 failed**.
- API smoke test (`/api/v1/health`): **200 OK**.
- Frontend production build: **failed** due to a pre-existing Next.js `<Html>` import issue unrelated to authentication hardening.
- All authentication-specific acceptance criteria are satisfied.

---

## 2. Root Cause

The Stage 8.11 Production Readiness Audit identified authentication-related findings:

- Session cookies used framework defaults that were not explicitly hardened.
- `TrustProxies` trusted any proxy (`*`), which is unsafe for production reverse-proxy configurations.
- Authentication events (failed logins, lockouts, disabled accounts) were not consistently audited.
- Sanctum tokens were not revoked on password change, allowing old credentials to remain valid.
- Rate limiting was broad (`api`) and did not protect sensitive entry points (login, registration, change-password) independently.
- Expired Sanctum tokens were not pruned automatically.
- The frontend persisted bearer tokens without a 401-unauthorized cleanup path.

---

## 3. Architecture Changes

### 3.1 Session & Sanctum Configuration

Updated environment template and configuration to enforce secure defaults:

- `backend/.env.example`
  - Added `TRUSTED_PROXIES` guidance.
  - Added `SANCTUM_TOKEN_EXPIRATION`.
  - Added session-security comments.
- `backend/config/session.php`
  - `encrypt` → `true`
  - `same_site` → `strict`
- `backend/config/sanctum.php`
  - Token expiration now reads from `SANCTUM_TOKEN_EXPIRATION` env.

### 3.2 TrustProxies Hardening

`backend/app/Http/Middleware/TrustProxies.php` now reads `TRUSTED_PROXIES` from the environment as a comma-separated list. If unset, no proxies are trusted, replacing the previous wildcard (`*`) configuration.

### 3.3 Authentication Logging

`backend/app/Http/Controllers/Api/V1/Auth/UnifiedLoginController.php` now records:

- `login.failed` for invalid credentials.
- `login.rejected.disabled` for inactive accounts.

### 3.4 Token Revocation & Session Invalidation

- `backend/app/Http/Controllers/Api/V1/Auth/ChangePasswordController.php` deletes all other personal access tokens after a successful password change.
- `backend/app/Filament/Pages/ForcePasswordChange.php` does the same for forced password changes.
- `backend/app/Http/Controllers/Api/V1/Auth/LogoutController.php` invalidates the web session when an API logout is performed.

### 3.5 Rate Limiting

`backend/app/Providers/RateLimitServiceProvider.php` now defines dedicated limiters:

| Limiter | Key | Max attempts |
|---|---|---|
| `login` | IP + email | 5 / min each |
| `register` | IP | 5 / min |
| `change-password` | User ID (fallback IP) | 5 / min |
| `contact` | IP | 3 / min |
| `payment` | User ID (fallback IP) | 10 / min |

`backend/routes/api.php` applies `throttle:login`, `throttle:register`, and `throttle:change-password` to the relevant routes.

### 3.6 Lockout Auditing

`backend/bootstrap/app.php` adds a renderable handler for `ThrottleRequestsException` on API routes. Login lockouts record `login.lockout` audit entries and return `429 Too Many Attempts`.

### 3.7 Expired Token Cleanup

`backend/app/Console/Commands/CleanupSanctumTokens.php` removes expired Sanctum tokens. It is scheduled hourly in `backend/routes/console.php`.

### 3.8 Frontend Token Hygiene

`frontend/lib/api/client.ts` now clears the `vestra_auth_token` cookie and redirects to `/auth/login` on `401 Unauthorized` responses.

---

## 4. Migration

No database migrations were required for this stage.

---

## 5. Seeder Updates

No seeders were modified for this stage.

---

## 6. Repository Changes

No repository classes were changed for this stage.

---

## 7. API Changes

- Login, register, and change-password endpoints now return `429 Too Many Attempts` when rate limits are exceeded.
- Successful password change now revokes all other active Sanctum tokens for the user.
- Logout endpoint now invalidates the web session when called with a bearer token.
- The public `/api/v1/health` endpoint remains unchanged.

---

## 8. Security Validation

- Verified that `TrustProxies` no longer uses `*` in production.
- Verified that login failures, disabled-account rejections, and lockouts are recorded in `audit_logs`.
- Verified that Sanctum tokens are revoked on password change and pruned when expired.
- Verified that dedicated rate limiters protect authentication endpoints.
- Verified that the frontend clears stored auth tokens on `401`.
- Confirmed no secrets, tokens, or passwords are logged.

---

## 9. PHPUnit Results

```text
Tests:    51 passed (620 assertions)
Duration: 66.38s
```

Key coverage added in `backend/tests/Feature/AuthenticationSecurityTest.php`:

- Failed-login auditing
- Disabled-account rejection auditing
- Rate-limiter configuration for login, register, and change-password
- Route-level 429 responses after limits are exhausted
- Logout token deletion
- Password-change token revocation
- Login lockout auditing
- Expired Sanctum token pruning
- TrustProxies environment handling
- Session cookie security configuration

---

## 10. Files Modified

| File | Change |
|---|---|
| `backend/.env.example` | Added `TRUSTED_PROXIES`, `SANCTUM_TOKEN_EXPIRATION`, session comments |
| `backend/config/session.php` | Hardened `encrypt` and `same_site` defaults |
| `backend/config/sanctum.php` | Env-driven token expiration |
| `backend/app/Http/Middleware/TrustProxies.php` | Env-controlled trusted proxy list |
| `backend/app/Http/Controllers/Api/V1/Auth/UnifiedLoginController.php` | Authentication logging |
| `backend/app/Http/Controllers/Api/V1/Auth/ChangePasswordController.php` | Revoke other tokens on change |
| `backend/app/Http/Controllers/Api/V1/Auth/LogoutController.php` | Invalidate web session |
| `backend/app/Filament/Pages/ForcePasswordChange.php` | Revoke tokens on forced change |
| `backend/app/Providers/RateLimitServiceProvider.php` | Dedicated authentication limiters |
| `backend/app/Providers/AppServiceProvider.php` | Removed stale Lockout listener (lockouts handled in exception handler) |
| `backend/bootstrap/app.php` | `ThrottleRequestsException` renderable for lockout auditing |
| `backend/routes/api.php` | Applied dedicated throttle middleware |
| `backend/routes/console.php` | Scheduled expired-token cleanup |
| `backend/app/Console/Commands/CleanupSanctumTokens.php` | New command |
| `backend/tests/Feature/AuthenticationSecurityTest.php` | New test coverage |
| `frontend/lib/api/client.ts` | 401 token cleanup |
| `docker-compose.prod.yml` | Debugbar removal / production dependency notes |

---

## 11. Known Limitations

1. **Frontend production build** fails with a pre-existing Next.js error (`<Html> should not be imported outside of pages/_document`). This is unrelated to authentication hardening and must be addressed in a frontend-specific stage.
2. **Config cache**: Tests must be executed with `php artisan config:clear` (or with a config cache built using the test environment's `CACHE_STORE=array`). Running tests against a config cache built from the development `.env` (`CACHE_STORE=database`) causes rate-limit tests to use the database cache store and produce cross-test pollution.

---

## 12. Recommendation

**PASS WITH OBSERVATIONS**

The authentication hardening objectives are complete and verified:

- Sessions and cookies are configured securely.
- Proxy trust is environment-controlled.
- Authentication events are audited.
- Tokens are revoked on password change and pruned when expired.
- Sensitive authentication endpoints have dedicated rate limiting.
- The frontend clears tokens on unauthorized responses.
- The full backend test suite passes.

The only blocking item is the pre-existing frontend build failure, which is out of scope for this stage and should be resolved before production deployment.

---

## 13. Commands Executed

```bash
# Tests
docker exec vestra-backend-dev php artisan config:clear
docker exec vestra-backend-dev sh -c "cd /var/www/html && php artisan test"

# Caches / smoke
docker exec vestra-backend-dev sh -c "cd /var/www/html && php artisan config:cache"
docker exec vestra-backend-dev sh -c "cd /var/www/html && php artisan route:cache"
docker exec vestra-backend-dev sh -c "cd /var/www/html && php artisan view:cache"
docker exec vestra-backend-dev sh -c "curl -s -o /dev/null -w '%{http_code}' http://localhost:8000/api/v1/health"

# Frontend build (failed, pre-existing)
docker exec vestra-frontend-dev sh -c "cd /app && npm run build"
```
