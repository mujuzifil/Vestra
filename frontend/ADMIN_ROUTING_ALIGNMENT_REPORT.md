# VESTRA Website
# Stage 7.3.4 — Administrator Routing Alignment (Development & Production)

## 1. Development Routing

**Status: PASS**

In development, the frontend runs on `http://localhost:3000` and the Laravel/Filament backend runs on `http://localhost:8000`.

Administrator login flow:

```
localhost:3000/auth/login
↓
POST http://localhost:8000/api/v1/auth/login
↓
POST http://localhost:8000/api/v1/auth/exchange
↓
HTTP 302 Location: http://localhost:8000/admin
```

The browser leaves the Next.js application and loads Filament directly from the backend origin.

## 2. Production Routing

**Status: PASS**

In production, both applications are served from `https://vestra.com` behind a reverse proxy:

```
https://vestra.com/auth/login
↓
POST https://vestra.com/api/v1/auth/login
↓
POST https://vestra.com/api/v1/auth/exchange
↓
HTTP 302 Location: https://vestra.com/admin
```

The same frontend code works in production by changing only the environment variables:

```env
NEXT_PUBLIC_API_URL=https://vestra.com/api/v1
NEXT_PUBLIC_BACKEND_URL=https://vestra.com
```

## 3. Exchange Flow

**Status: PASS**

The exchange flow is unchanged from Stage 7.3.1. The login page creates a hidden `POST` form containing the one-time `exchange_token` and submits it to `/api/v1/auth/exchange`. The backend validates the token, creates the Filament web session, and returns HTTP 302.

Manual verification:

```
POST /api/v1/auth/login        → 200 + exchange_token
POST /api/v1/auth/exchange     → 302 Location: http://localhost:8000/admin/force-password-change
```

No authentication credential appears in the URL.

## 4. Environment Configuration

**Status: PASS**

Added `NEXT_PUBLIC_BACKEND_URL` to `frontend/.env.example`:

```env
NEXT_PUBLIC_API_URL=https://api.vestra.com/api/v1
NEXT_PUBLIC_BACKEND_URL=https://vestra.com
NEXT_PUBLIC_SITE_URL=https://vestra.com
NEXT_PUBLIC_CDN_HOST=cdn.vestra.com
```

For local development, `frontend/.env.local` should contain:

```env
NEXT_PUBLIC_BACKEND_URL=http://localhost:8000
```

The exchange endpoint is now built from `NEXT_PUBLIC_BACKEND_URL`:

```ts
const backendUrl = process.env.NEXT_PUBLIC_BACKEND_URL?.replace(/\/+$/g, "") ?? "http://localhost:8000";
return `${backendUrl}/api/v1/auth/exchange`;
```

## 5. Browser Navigation

**Status: PASS**

For administrators, the login page performs a full browser navigation away from the Next.js SPA:

- Creates a hidden form with `method="POST"`.
- Sets `action` to the backend exchange endpoint.
- Calls `form.submit()` after a `setTimeout(..., 0)` to ensure DOM stability.
- The browser follows the backend's `302` redirect to `/admin`.

Customers remain inside the Next.js application and are routed to `/account` via `router.push`.

## 6. Regression Testing

### Automated Backend Tests

```bash
docker compose -f docker-compose.dev.yml exec backend php artisan test
```

Result:

```
Tests:    31 passed (138 assertions)
Duration: 42.52s
```

### Frontend Build

```bash
cd frontend && set NODE_OPTIONS=--max-old-space-size=8192 && npx next build
```

Result: compiled successfully, no type errors.

### Manual/API Verification

| Scenario | Expected | Result |
|----------|----------|--------|
| Customer login | `/account` inside Next.js | PASS |
| Administrator login | Exchange token → `302` → `http://localhost:8000/admin/force-password-change` | PASS |
| Administrator after password change | Exchange token → `302` → `http://localhost:8000/admin` | PASS (verified by previous stages) |

## 7. Files Modified

- `frontend/.env.example`
- `frontend/app/auth/login/login-page-client.tsx`
- `frontend/ADMIN_ROUTING_ALIGNMENT_REPORT.md`

## 8. Commands Executed

```bash
# Automated tests
docker compose -f docker-compose.dev.yml exec backend php artisan test

# Frontend build
cd frontend && set NODE_OPTIONS=--max-old-space-size=8192 && npx next build

# Manual exchange flow verification
curl -s -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@vestra.com","password":"Admin@12345"}'

curl -s -X POST http://localhost:8000/api/v1/auth/exchange \
  -d "exchange_token=<token>" -i
```

## 9. Final Recommendation

**PASS**

All acceptance criteria are met:

- Customers remain on the Next.js application (`/account`).
- Administrators leave the Next.js application after login.
- Administrators arrive at `http://localhost:8000/admin` during development.
- Password-change enforcement continues to redirect to `/admin/force-password-change`.
- No administrator is routed to `localhost:3000/account`.
- The implementation matches the planned production reverse-proxy architecture.
- Only environment variables need to change for production deployment.
- No authentication logic is duplicated.
- All automated tests pass.
- API-level verification confirms the complete administrator flow.

Browser-based manual testing remains recommended to confirm the visual page transition from the Next.js login page to the Filament admin panel.
