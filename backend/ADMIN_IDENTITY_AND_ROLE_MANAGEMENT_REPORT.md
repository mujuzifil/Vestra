# VESTRA Website
# Stage 7.2 — Administrator Identity & Role Management

## 1. Administrator Bootstrap

The bootstrap Super Administrator is created/reset by `database/seeders/AdminUserSeeder.php`.

Development credentials:

| Field | Value |
|-------|-------|
| Name | VESTRA Administrator |
| Email | admin@vestra.com |
| Password | Admin@12345 |
| Role | Super Administrator |
| Status | active |

The password is hashed via Laravel's `Hash::make`. The seeder uses `updateOrCreate` so running `php artisan db:seed` will reset the bootstrap account if it already exists.

**Status: PASS**

## 2. Roles Implemented

Roles are created by `database/seeders/RolePermissionSeeder.php` using `spatie/laravel-permission`:

- Super Administrator
- Administrator
- Manager (created with no permissions; reserved for future operational permissions)
- customer (preserved for public registrations)

The legacy `super-admin` role string is still recognised by `User::isAdmin()` for backward compatibility.

**Status: PASS**

## 3. Permission Matrix

| Permission | Super Administrator | Administrator | Manager |
|------------|---------------------|---------------|---------|
| manage administrators | yes | no | no |
| manage customers | yes | yes | no |
| manage products | yes | yes | no |
| manage inventory | yes | no | no |
| manage orders | yes | yes | no |
| view reports | yes | yes | no |
| manage settings | yes | no | no |
| manage notifications | yes | no | no |
| view audit logs | yes | no | no |

**Status: PASS**

## 4. Administrator Management

The existing `app/Filament/Resources/UserResource.php` has been repurposed as the administrator management resource under:

**Administration → Administrators**

Functions added/verified:

- Create Administrator
- Edit Administrator
- Deactivate / Activate (row action, hidden for the current user)
- Reset Password (row action with optional "force password change on next login")
- Assign Role (restricted to Super Administrator, Administrator, Manager)
- Status and force-password-change columns added to the list table

The resource query is scoped to `is_admin = true`, keeping customer management in the existing `CustomerResource`.

**Status: PASS**

## 5. Public Registration Validation

The public registration controller (`app/Http/Controllers/Api/V1/Auth/RegisterController.php`) and form request (`app/Http/Requests/Api/V1/RegisterRequest.php`) now:

- Explicitly set `is_admin = false` and `status = active` on every registration.
- Prohibit `is_admin`, `role`, `roles`, and `status` fields in the request.
- Assign only the `customer` role.

Verified via automated test and curl:

```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Hacker","email":"hacker@example.com","password":"Password123","password_confirmation":"Password123","is_admin":true,"role":"Super Administrator"}'
```

Response: `422 Unprocessable Entity` with prohibited-field errors.

**Status: PASS**

## 6. Security Validation

| Control | Implementation | Status |
|---------|----------------|--------|
| Password hashing | Laravel `hashed` cast + `Hash::make` | PASS |
| Login rate limiting | `throttle:login` middleware on `/api/v1/admin/login` and `/api/v1/auth/login` | PASS |
| Session timeout | Handled by Laravel session configuration | PASS |
| CSRF protection | Filament session middleware stack | PASS |
| Authorization policies | `UserPolicy` requires `isAdmin()`; Filament resources use `canAccess()` | PASS |
| Role enforcement | `spatie/laravel-permission` roles; registration blocked from assigning admin roles | PASS |
| Disabled account enforcement | Admin/customer logins reject `status = inactive` with 403 | PASS |

Attempted privilege escalation via registration payload was denied.

**Status: PASS**

## 7. Audit Logging

Created:

- `app/Models/AuditLog.php`
- `app/Services/AuditService.php`
- `database/migrations/2026_07_19_100400_create_audit_logs_table.php`

Logged actions include:

- `admin.login`
- `customer.login`
- `logout`
- `administrator.created`
- `administrator.updated`
- `administrator.deleted`
- `administrator.password_reset`
- `administrator.activated`
- `administrator.deactivated`
- `product.created`
- `product.updated`
- `product.deleted`
- `category.created`
- `category.updated`
- `category.deleted`
- `order.updated`
- `order.marked_paid`
- `order.marked_processing`
- `order.marked_packed`
- `order.marked_shipped`
- `order.marked_delivered`
- `order.cancelled`
- `order.refunded`
- `setting.updated`

Each log entry records user, action, subject (polymorphic), details (JSON), IP address, and timestamp.

**Status: PASS**

## 8. Regression Results

### Automated Tests

```bash
docker compose -f docker-compose.dev.yml exec backend php artisan test
```

Result:

```
Tests:    16 passed (91 assertions)
Duration: 66.70s
```

### Manual API Verification

| Scenario | Result |
|----------|--------|
| Admin login with `admin@vestra.com` / `Admin@12345` | PASS |
| Customer registration creates customer-only account | PASS |
| Registration with `is_admin`/`role` fields | Rejected 422 — PASS |
| Disabled admin login | Rejected 403 — PASS |
| `/admin/login` page loads | HTTP 200 — PASS |

**Status: PASS**

## 9. Files Created

- `backend/database/migrations/2026_07_19_100359_add_status_and_force_password_change_to_users_table.php`
- `backend/database/migrations/2026_07_19_100400_create_audit_logs_table.php`
- `backend/app/Models/AuditLog.php`
- `backend/app/Services/AuditService.php`
- `backend/database/seeders/RolePermissionSeeder.php`
- `backend/ADMIN_IDENTITY_AND_ROLE_MANAGEMENT_REPORT.md`

## 10. Files Modified

- `backend/database/seeders/DatabaseSeeder.php`
- `backend/database/seeders/AdminUserSeeder.php`
- `backend/app/Models/User.php`
- `backend/app/Filament/Resources/UserResource.php`
- `backend/app/Filament/Resources/UserResource/Pages/CreateUser.php`
- `backend/app/Filament/Resources/ProductResource.php`
- `backend/app/Filament/Resources/ProductResource/Pages/CreateProduct.php`
- `backend/app/Filament/Resources/CategoryResource.php`
- `backend/app/Filament/Resources/CategoryResource/Pages/CreateCategory.php`
- `backend/app/Filament/Resources/OrderResource.php`
- `backend/app/Filament/Resources/SettingResource.php`
- `backend/app/Http/Controllers/Api/V1/Auth/LoginController.php`
- `backend/app/Http/Controllers/Api/V1/Auth/CustomerLoginController.php`
- `backend/app/Http/Controllers/Api/V1/Auth/LogoutController.php`
- `backend/app/Http/Controllers/Api/V1/Auth/RegisterController.php`
- `backend/app/Http/Requests/Api/V1/RegisterRequest.php`
- `backend/tests/Feature/Api/V1/ApiEndpointsTest.php`

## 11. Bootstrap Security Hardening (Stage 7.2.1)

### 11.1 Mandatory First-Login Password Change

The bootstrap administrator is seeded with `force_password_change_at` set to the current timestamp. On first login:

- Filament redirects to `/admin/force-password-change`.
- API admin routes return `403` until the password is changed.
- The flag is cleared after a successful password change.

### 11.2 Password Policy

Password changes must meet:

- Minimum 12 characters
- At least one uppercase letter
- At least one lowercase letter
- At least one number
- At least one special character

### 11.3 Seeder Behaviour

`AdminUserSeeder` respects the `RESET_BOOTSTRAP_ADMIN` environment variable:

- `RESET_BOOTSTRAP_ADMIN=true`: resets the bootstrap password and forces a new password change.
- Missing or `false`: preserves an existing non-default password and does not reset `force_password_change_at`.

### 11.4 Production Protection

In `APP_ENV=production`, if the bootstrap administrator still uses the default password `Admin@12345`, the application throws a `RuntimeException` on boot and refuses to start until the password is changed.

## 12. Production Readiness Notes

- Change the bootstrap credentials (`admin@vestra.com` / `Admin@12345`) immediately after deployment.
- The `Manager` role is created with no permissions; assign permissions when operational requirements are defined.
- Audit logs are stored in `audit_logs`; implement log rotation/archiving before high-volume production use.
- Consider adding a dedicated Artisan command to prune old audit logs.
- In production, ensure `RESET_BOOTSTRAP_ADMIN` is only used during intentional credential recovery.

## 13. Final Status

**PASS**

- Customers can self-register.
- Administrators cannot self-register.
- The bootstrap Super Administrator exists with the required credentials.
- Roles and permissions are implemented and seeded.
- Public registration rejects privilege-escalation fields.
- Disabled administrators cannot log in.
- Administrator actions are audited.
- All automated tests pass.
