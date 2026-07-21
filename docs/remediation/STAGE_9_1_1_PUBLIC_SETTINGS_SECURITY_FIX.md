# Stage 9.1.1 — Eliminate Public Settings Secret Exposure

## 1. Executive Summary

The Stage 8.11 Production Readiness Audit identified a **CRITICAL** vulnerability: the public `/api/v1/settings` endpoint returned every row in the `settings` table, including SMTP credentials, payment gateway keys, and security policy values. This represented complete credential disclosure to any unauthenticated caller.

This remediation introduces an explicit **public/private visibility model** for settings. A new boolean `is_public` column controls whether a setting may be returned by the public API. The repository, seeder, and API test suite were updated to enforce this boundary, and a migration backfills visibility for all existing settings.

**Result:** Secrets can no longer be retrieved through the public settings endpoint. Legitimate public configuration required by the storefront (branding, contact info, commerce defaults, localization, etc.) continues to be returned with the same response schema.

**Final recommendation: PASS.**

---

## 2. Root Cause

- `SettingRepository::getPublicSettings()` returned `ordered()->get()` without filtering.
- `SettingService::publicList()` cached and returned the full settings collection.
- `SettingController::index()` exposed this collection via `SettingResource` to `GET /api/v1/settings`.
- The `settings` table mixed public branding/operational values with private secrets (SMTP, Flutterwave, security policies, system flags).
- No visibility metadata existed to distinguish public from private settings.

---

## 3. Architecture Changes

### 3.1 Visibility Model

A simple, explicit boolean flag was chosen to align with the existing strongly-typed Setting model:

- `is_public = true` — setting may be returned by the public API and used by the storefront.
- `is_public = false` — setting is internal-only and accessible only through the administration panel or backend services.

This is enforced at the repository layer, so all callers of `getPublicSettings()` benefit from the restriction regardless of cache state.

### 3.2 Caching

`SettingService::publicList()` still caches the public list under `settings.public_list`. Cache invalidation via `flushCache()` clears the public list key, so visibility changes made in the admin panel are reflected on the next request.

### 3.3 Backwards Compatibility

The `SettingResource` schema (`key`, `value`, `type`, `group`, `label`) is unchanged. Public consumers receive a subset of the previous payload, not a new shape.

---

## 4. Migration

**File:** `backend/database/migrations/2026_07_21_185649_add_is_public_to_settings_table.php`

- Adds `is_public` boolean column, default `false`, after `group`.
- Backfills existing rows using an explicit allow-list of public keys.
- Any key not in the allow-list is set to private.
- Rollback drops the column.

Public keys include: branding, contact details, business identifiers, commerce/order/payment/inventory operational defaults, notification flags, localization, social links, content blocks, and the Google Analytics ID.

Private keys include: all SMTP/email settings, security policy settings, Flutterwave keys, and system flags (`maintenance_mode`, `debug_mode`).

**Executed:** `docker exec vestra-backend-dev php artisan migrate --force` — completed successfully.

---

## 5. Seeder Updates

**File:** `backend/database/seeders/SettingSeeder.php`

- All 85 seeded settings now explicitly declare `is_public`.
- No implicit defaults; every setting record is self-documenting.
- Public/private classification matches the migration allow-list.

This ensures fresh installations and `php artisan db:seed` produce the same secure boundary as migrated environments.

---

## 6. Repository Changes

**File:** `backend/app/Repositories/SettingRepository.php`

```php
public function getPublicSettings(): Collection
{
    return $this->model->newQuery()
        ->where('is_public', true)
        ->ordered()
        ->get();
}
```

Other repository methods (`allByGroup`, `findByGroup`, `search`, `findByKey`) remain unchanged and continue to return all settings for administrative use.

---

## 7. Model Changes

**File:** `backend/app/Models/Setting.php`

- Added `is_public` to `$fillable`.
- Added `is_public` cast to `boolean`.

No other model behaviour changed.

---

## 8. API Changes

**File:** `backend/app/Http/Controllers/Api/V1/SettingController.php`

No controller code changes were required. The controller already calls `SettingService::publicList()`, which now delegates to the filtered `SettingRepository::getPublicSettings()`.

The endpoint remains:

```
GET /api/v1/settings
```

Response schema remains stable:

```json
{
  "success": true,
  "data": [
    { "key": "...", "value": "...", "type": "...", "group": "...", "label": "..." }
  ],
  "message": "Request completed successfully."
}
```

Only the number of records in `data` has decreased (69 public settings vs. 85 total after seeding).

---

## 9. Security Validation

### 9.1 Endpoint Scan

A repository-wide search confirmed only one public route exposes settings:

- `backend/routes/api.php:36` — `Route::get('/settings', [SettingController::class, 'index']);`

No other controllers, resources, or routes return `SettingResource` or call `getPublicSettings()`/`publicList()`.

### 9.2 Smoke Test

Executed against the running container:

```bash
docker exec vestra-backend-dev curl -s http://127.0.0.1:8000/api/v1/settings
```

Result:

- 69 public settings returned.
- No sensitive keys present (`smtp_password`, `smtp_username`, `smtp_host`, `smtp_encryption`, `sender_email`, `flutterwave_secret_key`, `flutterwave_public_key`, `password_min_length`, `max_login_attempts`, `session_timeout_minutes`, `debug_mode`, `maintenance_mode`).

### 9.3 PHPUnit Test Coverage

**File:** `backend/tests/Feature/Api/V1/ApiEndpointsTest.php`

The existing `test_settings_endpoint_returns_settings` was replaced with `test_settings_endpoint_returns_only_public_settings`.

Assertions added:

- Response is `200 OK` with `success = true`.
- Response structure matches the stable schema.
- `data` count equals `Setting::query()->where('is_public', true)->count()`.
- Public keys are present: `app_name`, `company_name`, `company_logo`, `currency`, `timezone`.
- Secret keys are absent: `smtp_password`, `smtp_username`, `smtp_host`, `smtp_encryption`, `sender_email`, `flutterwave_secret_key`, `flutterwave_public_key`, `password_min_length`, `password_requires_symbols`, `max_login_attempts`, `session_timeout_minutes`, `debug_mode`, `maintenance_mode`.

---

## 10. PHPUnit Results

```
PASS  Tests\Feature\Api\V1\ApiEndpointsTest
  ✓ settings endpoint returns only public settings

Tests:    31 passed (505 assertions)
Duration: 70.17s
```

Full suite executed with:

```bash
docker exec vestra-backend-dev php artisan test
```

All existing tests continue to pass; no regressions detected.

---

## 11. Regression Review

| Area | Status | Notes |
|------|--------|-------|
| Administration Settings pages | PASS | Filament `SettingResource` pages use repository methods that still return all settings. |
| Grouped Settings | PASS | `allByGroup()` and `findByGroup()` unchanged. |
| Settings search | PASS | `search()` unchanged. |
| System Information page | PASS | Reads individual settings via `SettingService::get()`, unaffected. |
| Caching | PASS | Public list cached under `settings.public_list`; flushed on save. |
| API response schema | PASS | Same `key/value/type/group/label` shape. |
| Test suite | PASS | 31 passed, 505 assertions. |

---

## 12. Files Modified

| File | Change |
|------|--------|
| `backend/database/migrations/2026_07_21_185649_add_is_public_to_settings_table.php` | New migration adding `is_public` column and backfilling visibility. |
| `backend/app/Models/Setting.php` | Added `is_public` to `$fillable` and casts. |
| `backend/app/Repositories/SettingRepository.php` | Filtered `getPublicSettings()` by `is_public = true`. |
| `backend/database/seeders/SettingSeeder.php` | Explicit `is_public` flag on all 85 settings. |
| `backend/tests/Feature/Api/V1/ApiEndpointsTest.php` | Replaced settings test with public/private assertions. |
| `docs/remediation/STAGE_9_1_1_PUBLIC_SETTINGS_SECURITY_FIX.md` | This report. |

---

## 13. Known Limitations

This remediation intentionally addresses only the credential-disclosure surface identified in Stage 8.11. The following were explicitly left out of scope and should be planned for future security hardening stages:

- Encryption of secrets at rest.
- External vault/secret-manager integration.
- Environment-level secret rotation.
- Key-derivation or encryption of SMTP/Flutterwave credentials.
- Row-level audit logging of setting visibility changes.

Even with these future improvements, the current fix eliminates the public API leak completely.

---

## 14. Commands Executed

```bash
# Syntax checks
docker exec vestra-backend-dev php -l database/seeders/SettingSeeder.php
docker exec vestra-backend-dev php -l tests/Feature/Api/V1/ApiEndpointsTest.php

# Database migration
docker exec vestra-backend-dev php artisan migrate --force

# Full test suite
docker exec vestra-backend-dev php artisan test

# Public endpoint smoke test
docker exec vestra-backend-dev curl -s http://127.0.0.1:8000/api/v1/settings
```

---

## 15. Recommendation

**PASS.**

The public settings endpoint now returns only explicitly approved public configuration. Sensitive credentials, security policy values, and system flags are no longer exposed to unauthenticated callers. The fix preserves the existing API schema, passes the full test suite, and introduces no regressions in the administration settings workflow.

The platform can proceed to the next remediation stage with this critical vulnerability resolved.
