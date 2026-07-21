# VESTRA Website
# Stage 7.3.2 — Unified Authentication Login Failure Audit & Remediation

## 1. Executive Summary

**Status: PASS**

Manual administrator login with `admin@vestra.com` / `Admin@12345` was failing with "The provided credentials are incorrect." An evidence-based audit determined that the development database was completely empty: no users, roles, categories, or products existed. The authentication controller was working correctly; it simply could not find the bootstrap administrator record.

After seeding the database, login succeeded. However, running the automated test suite immediately emptied the development database again because PHPUnit's SQLite / in-memory settings were being overridden by Docker Compose environment variables, causing tests to run against the shared MySQL development database.

The final remediation therefore had two parts:

1. Seed the empty development database.
2. Force the test suite to use an isolated in-memory SQLite database and array session/cache so it can no longer destroy development data.

All automated tests now pass, the development database persists after test runs, and both administrator and customer logins work correctly.

## 2. Bootstrap Administrator Audit

**Status: PASS**

Initial state:

```
User::where('email', 'admin@vestra.com')->first() => null
```

After remediation:

```
exists                 => true
name                   => "VESTRA Administrator"
email                  => "admin@vestra.com"
is_admin               => true
status                 => "active"
force_password_change_at => 2026-07-19 18:25:44
roles                  => ["Super Administrator"]
Hash::check default    => true
```

## 3. Password Audit

**Status: PASS**

The default bootstrap password `Admin@12345` validates successfully against the stored hash. The seeder's password-preservation logic is intact: if an existing administrator's password is no longer the default, the seeder will not overwrite it unless `RESET_BOOTSTRAP_ADMIN=true`.

## 4. Seeder Audit

**Status: PASS**

`AdminUserSeeder` behaves as designed:

- Uses `updateOrCreate` keyed on `admin@vestra.com`.
- Only resets to the default password when `RESET_BOOTSTRAP_ADMIN=true`.
- Preserves an already-changed password when `RESET_BOOTSTRAP_ADMIN=false`.
- Sets `force_password_change_at` for new or reset accounts.
- Assigns the `Super Administrator` role.

The seeder is not defective; the failure was caused by the database never having been seeded in the current environment.

## 5. Authentication Pipeline Audit

**Status: PASS**

Traced `POST /api/v1/auth/login`:

1. `CustomerLoginRequest` validates `email` and `password`.
2. `UnifiedLoginController::login` queries `User::where('email', ...)->first()`.
3. `Hash::check` validates the password.
4. Active status, role detection, exchange-token creation, and response generation all execute correctly.

When the administrator record is present, the pipeline returns HTTP 200 with role `super-administrator`, `redirect_to` `/admin`, and a valid `exchange_token`.

## 6. Database Audit

**Status: PASS**

Initial state:

```
products   => 0
categories => 0
users      => 0
roles      => 0
```

After seeding:

```
products   => 6
categories => 4
users      => 1
roles      => 4
```

No duplicate or orphaned records were found.

## 7. Environment Audit

**Status: PASS**

Runtime environment:

```
APP_ENV                => local
RESET_BOOTSTRAP_ADMIN  => null (false)
DB_CONNECTION          => mysql
DB_DATABASE            => vestra
CACHE_DRIVER           => file
SESSION_DRIVER         => database
```

The local development configuration is correct. The only issue was that these Docker-injected environment variables were overriding `phpunit.xml` settings, causing tests to use the dev database.

## 8. Runtime Logs

**Status: PASS**

Laravel logs showed earlier production-security warnings about the default bootstrap password, but no login-related exceptions. After fixing test isolation, no new errors appear.

## 9. Root Cause Analysis

**Problem**
Administrator login returned "The provided credentials are incorrect."

**Root Cause**
The persistent development MySQL database was empty. The bootstrap administrator, roles, categories, and products had never been seeded into the container's database volume.

**Why automated tests did not detect it**
The test suite was passing, but it was using the same MySQL database as the development environment. Each test run with `RefreshDatabase` truncated the shared database, so after any test run the manual environment appeared broken again.

**Why the fix is correct**
- Seeding populated the data required for the application to function.
- Forcing tests to use an isolated in-memory SQLite database prevents the test suite from destroying development data and ensures repeatable, isolated test runs.

**Regression risk**
Low. The change is confined to `tests/TestCase.php` and the development database seed state. Production code paths are untouched.

**Lessons learned**
- Always verify that automated tests are running against an isolated database, especially in containerized environments where Docker env vars can override `phpunit.xml`.
- A passing test suite does not guarantee a working manual environment if both share the same database.

## 10. Remediation Applied

1. Cleared application, config, route, and permission caches.
2. Ran database migrations (`migrate --force` — already up to date).
3. Seeded the development database:
   ```bash
   docker compose -f docker-compose.dev.yml exec backend php artisan db:seed --force
   ```
4. Fixed test isolation by updating `backend/tests/TestCase.php`:
   - Override `createApplication()`.
   - Force `database.default` to `sqlite` with `:memory:` database.
   - Force `session.driver` to `array`.
   - Force `cache.default` to `array`.

This ensures Docker environment variables cannot redirect the test suite back to the shared MySQL database.

## 11. Regression Testing

### Automated Backend Tests

```bash
docker compose -f docker-compose.dev.yml exec backend php artisan test
```

Result:

```
Tests:    31 passed (138 assertions)
Duration: 100.59s
```

### Database Persistence After Tests

```
admin exists   => true
hash_check     => true
products       => 6
users          => 1
```

The development database now survives test execution.

### Manual API Verification

| Scenario | Request | Result |
|----------|---------|--------|
| Admin login | `POST /api/v1/auth/login` | PASS — returns `super-administrator`, `exchange_token`, `redirect_to: /admin` |
| Exchange token | `POST /api/v1/auth/exchange` | PASS — HTTP 302 to `/admin/force-password-change` |
| Customer registration | `POST /api/v1/auth/register` | PASS — creates customer with `customer` role |
| Customer login | `POST /api/v1/auth/login` | PASS — returns `customer`, `exchange_token: null`, `redirect_to: /account` |

### Frontend Build

```bash
cd frontend && set NODE_OPTIONS=--max-old-space-size=8192 && npx next build
```

The build completed successfully on a subsequent run with increased Node memory. The default `npm run build` (Turbopack) and a standard run without increased memory were unstable in this environment due to memory pressure.

## 12. Files Modified

- `backend/tests/TestCase.php`
- `frontend/AUTHENTICATION_LOGIN_FAILURE_AUDIT_REPORT.md`

No application code was changed.

## 13. Commands Executed

```bash
# Audit evidence
php artisan tinker --execute="..."
php artisan tinker --execute="..."
tail -n 50 storage/logs/laravel.log

# Cache clearing
docker compose -f docker-compose.dev.yml exec backend php artisan optimize:clear
docker compose -f docker-compose.dev.yml exec backend php artisan config:clear
docker compose -f docker-compose.dev.yml exec backend php artisan cache:clear
docker compose -f docker-compose.dev.yml exec backend php artisan route:clear
docker compose -f docker-compose.dev.yml exec backend php artisan permission:cache-reset

# Migrations and seeding
docker compose -f docker-compose.dev.yml exec backend php artisan migrate --force
docker compose -f docker-compose.dev.yml exec backend php artisan db:seed --force

# Verification
docker compose -f docker-compose.dev.yml exec backend php artisan test
cd frontend && set NODE_OPTIONS=--max-old-space-size=8192 && npx next build

# Manual API checks
curl -X POST http://localhost:8000/api/v1/auth/login ...
curl -X POST http://localhost:8000/api/v1/auth/exchange ...
curl -X POST http://localhost:8000/api/v1/auth/register ...
```

## 14. Final Recommendation

**PASS**

The administrator login failure has been fully investigated and remediated:

- Root cause identified with evidence: empty development database.
- Administrator account integrity verified.
- Password behaviour matches the Stage 7.2.1 design.
- Unified authentication successfully authenticates administrators.
- Administrators are redirected through the exchange-token flow into Filament.
- First-login password change flow remains intact.
- Customer authentication is unaffected.
- RBAC permissions remain intact.
- Exchange-token security remains intact.
- Test isolation fixed so Stages 7.2–7.3.1 cannot be regressed by a shared database.
- All automated tests pass.
- Manual testing confirms successful administrator and customer login.
