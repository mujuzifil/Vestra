# Stage 9.1.2 — Secret Management Hardening

## 1. Executive Summary

The Stage 8.11 Production Readiness Audit identified secret-management risks beyond the public Settings API leak resolved in Stage 9.1.1. This remediation addresses the remaining findings:

- Laravel Debugbar was a production dependency.
- Sensitive settings (SMTP credentials, Flutterwave keys) were stored in plaintext.
- Filament forms and tables displayed plaintext secrets.
- The bootstrap administrator password was hardcoded.

This stage introduced an explicit `is_sensitive` flag, conditional database encryption using Laravel's Crypt facade, masked administration fields, and moved Debugbar to `require-dev`. The bootstrap password can now be overridden via environment for production deployments.

**Final recommendation: PASS with observations.**

---

## 2. Root Cause

- `barryvdh/laravel-debugbar` was listed in `composer.json` `require`, making it available in production builds.
- The `settings` table mixed public, private, and secret values with no classification beyond `is_public`.
- Filament rendered setting values directly, exposing secrets to anyone with admin access.
- `AdminUserSeeder` used a hardcoded default password and only `Admin@12345` could be detected at boot time.
- No encryption at rest existed for database-stored secrets.

---

## 3. Repository Audit

A targeted search across PHP, env, and Docker files found:

- **Env/config lookups** — acceptable (`.env.example`, `config/services.php`, `config/mail.php`, `config/database.php`).
- **Setting model access** — now encrypted/masked for sensitive keys.
- **Test fixtures** — `AdminUserSeederTest` and `ProductionBootstrapPasswordTest` use the default password intentionally for local/test validation.
- **Hardcoded dev credentials** — `docker-compose.dev.yml` contains placeholder dev passwords (`vestrasecret`, `rootsecret`). These are clearly local-only and are not used by production Compose or deployment pipelines.
- **No production secrets committed** — only `backend/.env.example` is tracked; real `.env` files are ignored.

No API endpoint other than the public `/api/v1/settings` (now filtered) returns settings or payment secrets.

---

## 4. Debugbar Review

- `barryvdh/laravel-debugbar` moved from `require` to `require-dev` in `backend/composer.json`.
- `backend/composer.lock` refreshed with `composer update --lock`.
- `DEBUGBAR_ENABLED=false` added to `backend/.env.example` and `docker-compose.prod.yml`.
- `backend/Dockerfile.prod` updated to remove stale `bootstrap/cache/packages.php` and `bootstrap/cache/services.php` before `composer dump-autoload --no-dev`.
- A PHPUnit test asserts Debugbar is listed only under `require-dev` and that `.env.example` disables it.

Production Docker builds (`composer install --no-dev`) will no longer install or discover Debugbar.

---

## 5. Encryption Design

### 5.1 Visibility Model

A new boolean `is_sensitive` column classifies settings that must be encrypted at rest and masked in the UI.

Sensitive keys:

- `smtp_password`
- `smtp_username`
- `sender_email`
- `flutterwave_secret_key`
- `flutterwave_encryption_key`
- `flutterwave_webhook_secret`

### 5.2 Transparent Encryption

File: `backend/app/Models/Setting.php`

- `getValueAttribute` automatically decrypts sensitive values when read.
- `setValueAttribute` automatically encrypts sensitive values when written.
- `typedValue()` decrypts before casting to the correct PHP type.
- A `saving` model event acts as a safety net for mass-assignment ordering (when `value` is assigned before `is_sensitive`).
- `ENCRYPTED_PLACEHOLDER = '__encrypted__'` is used by masked Filament fields and is never persisted.

Encryption uses `Illuminate\Support\Facades\Crypt` (AES-256-CBC via `APP_KEY`).

### 5.3 Migration

File: `backend/database/migrations/2026_07_21_191854_add_is_sensitive_to_settings_table.php`

- Adds `is_sensitive` boolean default `false`.
- Backfills the sensitive keys listed above.
- Encrypts any existing plaintext values for those keys.
- Skips null/empty values and avoids double-encryption.

**Executed:** `docker exec vestra-backend-dev php artisan migrate --force` — completed successfully.

---

## 6. Administration Changes

### 6.1 Settings List Table

File: `backend/app/Filament/Resources/SettingResource.php`

- Sensitive values display `••••••••` instead of plaintext.
- Tooltips are disabled for sensitive records.

### 6.2 Single-Record Edit Form

- Non-sensitive fields are hidden when a setting is sensitive.
- A password-style input (`->password()->revealable()`) is shown for sensitive values.
- Existing sensitive values are loaded as the `ENCRYPTED_PLACEHOLDER`.
- The placeholder is never saved; unchanged secrets are preserved.
- Audit log entries for sensitive settings record `[redacted]` instead of the value.

### 6.3 Group Settings Pages

File: `backend/app/Filament/Resources/SettingResource/Pages/EditGroupSettings.php`

- Sensitive fields render as masked password inputs.
- Save logic skips the placeholder and redacts audit values.

### 6.4 Test Email Action

File: `backend/app/Filament/Resources/SettingResource/Pages/EditEmailSettings.php`

- Reads actual decrypted SMTP credentials from the loaded settings collection when the form still contains the placeholder.
- Continues to use updated values when the administrator has just typed a new secret.

---

## 7. Environment & Default Credentials

- `backend/.env.example`:
  - Added `DEBUGBAR_ENABLED=false`.
  - Added `BOOTSTRAP_ADMIN_PASSWORD=` with a comment requiring it to be set in production.
  - Added comments marking secret blocks (database, mail, Flutterwave).
- `docker-compose.prod.yml`:
  - Added `DEBUGBAR_ENABLED=false` to the backend environment.
  - Continues to inject all secrets via `${VAR}` interpolation; no hardcoded production credentials.
- `backend/database/seeders/AdminUserSeeder.php`:
  - Reads bootstrap password from `BOOTSTRAP_ADMIN_PASSWORD` env, falling back to the local/test default.
- `backend/app/Providers/AppServiceProvider.php`:
  - Production boot guard now compares against the env-configured bootstrap password instead of a hardcoded literal.

---

## 8. Secret Exposure Validation

- Public `/api/v1/settings` endpoint smoke-tested: **69 public settings returned, 0 sensitive keys, no ciphertext fragments.**
- Grep review confirmed no controller/resource returns `config('services.flutterwave')`, mail credentials, or Setting model secrets outside admin/services.
- Exception rendering in `bootstrap/app.php` only exposes file/line details when `config('app.debug')` is true.
- Audit log payloads for sensitive settings are redacted.

---

## 9. PHPUnit Results

```
Tests:    37 passed (591 assertions)
Duration: 60.39s
```

New/updated tests:

- `Tests\Unit\SettingEncryptionTest`
  - Sensitive values are encrypted at rest.
  - Non-sensitive values remain plaintext.
  - Placeholder is never persisted.
  - Updating a sensitive value re-encrypts it.
- `Tests\Unit\DebugbarProductionTest`
  - Debugbar is a development dependency only.
  - `.env.example` disables Debugbar.
- `Tests\Feature\Api\V1\ApiEndpointsTest`
  - Expanded secret-absence assertions and ciphertext-prefix check.

All existing tests continue to pass.

---

## 10. Build & Regression Validation

Commands executed:

```bash
docker exec vestra-backend-dev php artisan config:cache
docker exec vestra-backend-dev php artisan route:cache
docker exec vestra-backend-dev php artisan view:cache
docker exec vestra-backend-dev curl -s http://127.0.0.1:8000/api/v1/settings
```

Results:

- Configuration, route, and view caches generated successfully.
- Public settings endpoint returns only approved public settings.
- No runtime errors observed.

A production-autoloader check was attempted; it surfaced a stale `bootstrap/cache/packages.php` reference. The production Dockerfile was updated to delete those cache files before autoloader generation.

---

## 11. Files Modified

| File | Change |
|------|--------|
| `backend/composer.json` | Moved `barryvdh/laravel-debugbar` to `require-dev`. |
| `backend/composer.lock` | Refreshed via `composer update --lock`. |
| `backend/.env.example` | Added `DEBUGBAR_ENABLED=false`, `BOOTSTRAP_ADMIN_PASSWORD`, and secret comments. |
| `docker-compose.prod.yml` | Added `DEBUGBAR_ENABLED=false`; no hardcoded secrets. |
| `backend/Dockerfile.prod` | Remove stale package/service caches before production autoloader generation. |
| `backend/database/migrations/2026_07_21_191854_add_is_sensitive_to_settings_table.php` | New migration adding `is_sensitive`, classifying keys, encrypting existing values. |
| `backend/app/Models/Setting.php` | Added `is_sensitive`, conditional encryption accessor/mutator, placeholder, `saving` safety net. |
| `backend/database/seeders/SettingSeeder.php` | Explicit `is_sensitive` flag on all 85 settings. |
| `backend/app/Filament/Resources/SettingResource.php` | Masked sensitive values in table/form; redacted audit log. |
| `backend/app/Filament/Resources/SettingResource/Pages/EditGroupSettings.php` | Masked sensitive group fields; placeholder handling; audit redaction. |
| `backend/app/Filament/Resources/SettingResource/Pages/EditEmailSettings.php` | Read decrypted SMTP values when form contains placeholder. |
| `backend/database/seeders/AdminUserSeeder.php` | Read bootstrap password from env. |
| `backend/app/Providers/AppServiceProvider.php` | Production boot guard uses env bootstrap password. |
| `backend/tests/Unit/SettingEncryptionTest.php` | New unit tests for encryption behavior. |
| `backend/tests/Unit/DebugbarProductionTest.php` | New tests for Debugbar dependency and env guard. |
| `backend/tests/Feature/Api/V1/ApiEndpointsTest.php` | Expanded public API secret-absence assertions. |
| `docs/remediation/STAGE_9_1_2_SECRET_MANAGEMENT_HARDENING.md` | This report. |

---

## 12. Remaining Risks

The following are explicitly out of scope for this stage and should be planned for future infrastructure work:

- External secret manager integration (Azure Key Vault, AWS Secrets Manager, HashiCorp Vault).
- Automatic key rotation and credential rotation scheduling.
- Encryption of secrets in logs outside the audit redaction already added.
- HSM integration.
- The `docker-compose.dev.yml` file still contains local-only placeholder passwords; this is acceptable for development but should be reviewed if shared environments are introduced.

---

## 13. Recommendation

**PASS WITH OBSERVATIONS.**

The secret-management findings from Stage 8.11 have been remediated:

- Debugbar is no longer a production dependency.
- Sensitive settings are encrypted at rest and decrypted transparently.
- Filament masks secret values and never logs them in plaintext.
- Bootstrap password can be configured via environment.
- Public APIs expose no secrets.
- Test suite passes with expanded coverage.

The remaining observations are low-risk and relate to future infrastructure hardening, not production blocker.
