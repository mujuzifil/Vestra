# VESTRA Website
# Stage 7.3.1 — Production Authentication Hardening (One-Time Exchange Token)

## 1. Exchange Token Design

**Status: PASS**

A dedicated `ExchangeToken` model stores only a SHA-256 hash of a cryptographically random 64-character token. The plaintext token is never persisted.

| Property | Implementation |
|----------|----------------|
| Token entropy | 64 random characters (~380 bits) generated via `Illuminate\Support\Str::random` |
| Storage | `token_hash` SHA-256, indexed |
| TTL | 30 seconds (`ExchangeTokenService::TTL_SECONDS`) |
| Single use | Enforced via `used_at` timestamp |
| Scope | Administrator-only exchange into a Filament web session |
| Hashing | SHA-256 of plaintext; plaintext never stored or logged |

Created files:

- `backend/app/Models/ExchangeToken.php`
- `backend/database/migrations/2026_07_19_202448_create_exchange_tokens_table.php`
- `backend/app/Services/ExchangeToken/ExchangeTokenService.php`
- `backend/app/Services/ExchangeToken/Exceptions/ExchangeTokenException.php`
- `backend/app/Services/ExchangeToken/Exceptions/InvalidExchangeTokenException.php`
- `backend/app/Services/ExchangeToken/Exceptions/ExpiredExchangeTokenException.php`
- `backend/app/Services/ExchangeToken/Exceptions/UsedExchangeTokenException.php`

## 2. Login Flow

**Status: PASS**

`POST /api/v1/auth/login` remains the single authentication endpoint for all users.

For administrators, the response now includes:

```json
{
  "authenticated": true,
  "role": "super-administrator",
  "redirect_to": "/admin",
  "exchange_token": "<64-char plaintext token>",
  "must_change_password": true
}
```

For customers, `exchange_token` is `null`.

The login controller audits `exchange_token.created` whenever an admin token is issued.

## 3. Exchange Endpoint

**Status: PASS**

`POST /api/v1/auth/exchange` is registered as a web route in `backend/routes/web.php`.

Flow:

1. Validate `exchange_token` (required, string, size 64).
2. Look up the token by SHA-256 hash.
3. Reject unknown, expired, already-used, non-admin, or inactive users with appropriate status codes.
4. Mark the token as used.
5. Log the user into the `web` guard and regenerate the session.
6. Redirect to `/admin` or `/admin/force-password-change`.

Because the endpoint is invoked via a cross-origin form POST from the public frontend, it is explicitly excluded from CSRF verification in `backend/bootstrap/app.php`. The exchange token itself is the credential, so CSRF protection is unnecessary here.

## 4. Session Creation

**Status: PASS**

After successful exchange:

- `Auth::guard('web')->login($user)` establishes the Filament session.
- `$request->session()->regenerate()` mitigates session fixation.
- Redirect is returned as a standard HTTP 302 so the browser follows it naturally.
- No Sanctum token or exchange token appears in the URL.

## 5. Replay Protection

**Status: PASS**

The first successful exchange marks the token's `used_at` timestamp. Any subsequent redemption of the same plaintext token returns `409 Conflict` because the token is already used. Automated tests confirm this behavior.

## 6. Token Expiration

**Status: PASS**

Tokens expire 30 seconds after creation. An expired token lookup returns `410 Gone` and the expired record is removed immediately.

## 7. Audit Logging

**Status: PASS**

`AuditService` was updated to accept a nullable `User` so failed exchange attempts can still be logged safely.

Logged events:

- `exchange_token.created` — issued to an admin at login.
- `exchange_token.used` — successful exchange into a web session.
- `exchange_token.invalid` — unknown token submitted.
- `exchange_token.expired` — token past TTL submitted.
- `exchange_token.replayed` — already-used token submitted.
- `exchange_token.rejected` — non-admin or inactive account attempt.

Each log entry includes the user (when available), timestamp, IP address, and user agent.

## 8. Cleanup Strategy

**Status: PASS**

Created `backend/app/Console/Commands/CleanupExchangeTokens.php` (`auth:cleanup-exchange-tokens`).

The command removes:

- Expired tokens (`expires_at < now()`).
- Used tokens (`used_at IS NOT NULL`).

It is scheduled to run hourly in `backend/routes/console.php`:

```php
Schedule::command('auth:cleanup-exchange-tokens')->hourly();
```

## 9. Security Testing

**Status: PASS**

Automated test coverage in `backend/tests/Feature/Api/V1/ApiEndpointsTest.php`:

| Scenario | Expected | Result |
|----------|----------|--------|
| Valid admin token → redirect | `/admin` | PASS |
| Valid admin with `must_change_password` | `/admin/force-password-change` | PASS |
| Unknown token | 401 Unauthorized | PASS |
| Expired token | 410 Gone | PASS |
| Reused token | 409 Conflict | PASS |
| Customer exchange attempt | 403 Forbidden | PASS |
| Missing token | 422 Unprocessable | PASS |

Manual verification:

- Browser history contains no Sanctum token or exchange token.
- Server logs contain no plaintext tokens.
- URL never contains an authentication credential.

## 10. Regression Testing

**Status: PASS**

### Automated Backend Tests

```bash
docker compose -f docker-compose.dev.yml exec backend php artisan test
```

Result:

```
Tests:    31 passed (138 assertions)
Duration: 126.93s
```

### Frontend Build

```bash
cd frontend && npx next build
```

Result: compiled successfully with no type errors. (`npm run build` using Turbopack terminated with a segmentation fault in this environment; the standard Next.js build completed successfully.)

### Manual Scenarios

| Scenario | Result |
|----------|--------|
| Customer login → `/account` | PASS |
| Super Administrator login → exchange token → `/admin/force-password-change` | PASS |
| Super Administrator after password change → exchange token → `/admin` | PASS |
| Customer cannot exchange token | PASS |
| Reused exchange token rejected | PASS |
| Logout terminates session | PASS |

## 11. Files Modified

### Backend

- `backend/app/Services/AuditService.php`
- `backend/app/Http/Controllers/Api/V1/Auth/UnifiedLoginController.php`
- `backend/app/Http/Resources/V1/UnifiedLoginResource.php`
- `backend/routes/api.php`
- `backend/routes/web.php`
- `backend/routes/console.php`
- `backend/bootstrap/app.php`

### Frontend

- `frontend/types/index.ts`
- `frontend/lib/api/auth.ts`
- `frontend/lib/auth-context.tsx`
- `frontend/app/auth/login/login-page-client.tsx`
- `frontend/UNIFIED_AUTHENTICATION_IMPLEMENTATION_REPORT.md`

### Created

- `backend/app/Models/ExchangeToken.php`
- `backend/database/migrations/2026_07_19_202448_create_exchange_tokens_table.php`
- `backend/app/Services/ExchangeToken/ExchangeTokenService.php`
- `backend/app/Services/ExchangeToken/Exceptions/ExchangeTokenException.php`
- `backend/app/Services/ExchangeToken/Exceptions/InvalidExchangeTokenException.php`
- `backend/app/Services/ExchangeToken/Exceptions/ExpiredExchangeTokenException.php`
- `backend/app/Services/ExchangeToken/Exceptions/UsedExchangeTokenException.php`
- `backend/app/Http/Controllers/Api/V1/Auth/ExchangeTokenController.php`
- `backend/app/Console/Commands/CleanupExchangeTokens.php`
- `frontend/AUTHENTICATION_EXCHANGE_TOKEN_HARDENING_REPORT.md`

### Deleted

- `backend/app/Http/Controllers/Api/V1/Auth/FilamentSessionController.php`

## 12. Commands Executed

```bash
# Apply the exchange tokens table migration
docker compose -f docker-compose.dev.yml exec backend php artisan migrate --force

# Run the full backend test suite
docker compose -f docker-compose.dev.yml exec backend php artisan test

# Build the frontend
cd frontend && npx next build
```

## 13. Final Recommendation

**PASS**

All acceptance criteria for Stage 7.3.1 are met:

- Sanctum tokens are no longer transmitted via URL query parameters.
- A one-time exchange token is used for Filament session creation.
- Exchange tokens are cryptographically secure and stored only as SHA-256 hashes.
- Exchange tokens expire after 30 seconds.
- Exchange tokens are single-use with replay detection returning `409 Conflict`.
- Customers cannot exchange administrator sessions (`403 Forbidden`).
- Expired and used tokens are cleaned up automatically.
- Audit logging captures all exchange lifecycle events.
- Browser history, referrer headers, and server logs contain no authentication tokens.
- Stages 7.2, 7.2.1, and 7.3 functionality continues to work.
- All automated tests pass.
- Manual verification confirms the secure login experience for all user roles.

The VESTRA authentication architecture now meets production security best practices by ensuring authentication credentials are never exposed in URLs while maintaining a seamless single-login experience for both customers and administrators.
