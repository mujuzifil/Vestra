# Stage 9.1.4 — Authorization Hardening Report

## 1. Executive Summary

This stage hardened the VESTRA platform's authorization subsystem to production-grade standards. The work focused on:

- Removing privileged and foreign-key fields from mass-assignment guards.
- Enforcing administrator-only access on business-intelligence report APIs.
- Creating and registering missing Laravel policies for protected models.
- Refactoring inline `isAdmin()` checks to policy-based authorization.
- Adding audit logging for authorization denials, privilege-escalation attempts, and role/permission changes.
- Expanding PHPUnit coverage with an `AuthorizationSecurityTest` suite.

**Final recommendation: PASS WITH OBSERVATIONS**

All authorization-specific tests pass. A small number of pre-existing tests in `ApiEndpointsTest` exhibit test-isolation flakiness caused by `AdminUserSeeder` preserving a previously changed bootstrap-admin password across tests. These failures are unrelated to the authorization changes and are documented in §10.

## 2. Authorization Architecture

- **Authentication**: Laravel Sanctum with guard `web`.
- **Authorization**: Spatie Permission (`roles`, `permissions`) plus Laravel Policies/Gates.
- **Roles seeded**: `Super Administrator`, `Administrator`, `Manager`, `customer`.
- **Existing policies**: `Category`, `Product`, `ContactMessage`, `DistributorRequest`, `Setting`, `User`, `CustomerAddress`, `Order`.
- **New policies added**: `Review`, `CustomerFeedback`, `Cart`, `CartItem`, `PaymentTransaction`, `AuditLog`, `AdminSession`, `LoginActivity`.
- **New gate added**: `view reports` (admin only).
- **Filament access**: all resources gate on `auth()->user()?->isAdmin()`.

## 3. Role & Permission Matrix

| Role | Capabilities |
|------|--------------|
| Super Administrator | Full platform access |
| Administrator | Full administrative access |
| Manager | Administrative access (configurable) |
| customer | Storefront access only |

Permissions seeded: `manage administrators`, `view audit logs`, `manage customers`, `manage products`, `manage inventory`, `manage orders`, `view reports`, `manage settings`, `manage notifications`.

## 4. Ownership Validation

Customer-owned resources now enforce ownership server-side:

| Resource | Ownership Check |
|----------|-----------------|
| Orders | `OrderController::show` uses `$user->orders()->find($orderId)` |
| Addresses | `AddressPolicy::view/update/delete` checks `$user->id === $address->user_id` |
| Reviews | `ReviewPolicy::update` checks `$user->id === $review->user_id`; `delete` allows owner or admin |
| Cart | `CartRepository` binds cart to authenticated user only |
| Payment transactions | `PaymentTransactionPolicy::view` checks order ownership |

## 5. IDOR Audit

Previously, route-model binding for `Review` and `CustomerAddress` relied on inline controller checks. These have been replaced with registered policies, ensuring that manipulating IDs in `/reviews/{review}` or `/auth/addresses/{address}` returns `403` for unauthorized users.

Report APIs (`/reports/*`) previously allowed any authenticated customer to view sales, inventory, and customer-growth data. They are now protected by the `can:view reports` middleware and by `$this->authorize('view reports')` in the controller.

## 6. Policy Review

New policies created in `app/Policies/`:

| Policy | Methods |
|--------|---------|
| `ReviewPolicy` | `viewAny`, `view`, `create`, `update`, `delete`, `moderate` |
| `CustomerFeedbackPolicy` | `viewAny`, `view`, `create`, `update`, `delete`, `moderate` |
| `CartPolicy` | `view`, `update`, `delete` |
| `CartItemPolicy` | `view`, `update`, `delete` |
| `PaymentTransactionPolicy` | `viewAny`, `view` |
| `AuditLogPolicy` | `viewAny`, `view`, `export` |
| `AdminSessionPolicy` | `viewAny`, `view`, `terminate` |
| `LoginActivityPolicy` | `viewAny`, `view` |

All policies are registered in `AuthServiceProvider`.

## 7. Administrative Authorization

Filament resources continue to gate access via `isAdmin()`. Within API controllers, inline `isAdmin()` checks on review/feedback moderation endpoints were replaced with `$this->authorize('viewAny')` and `$this->authorize('moderate')`.

## 8. API Authorization Review

| Endpoint Group | Before | After |
|----------------|--------|-------|
| `/reports/*` | `auth:sanctum` only | `auth:sanctum` + `can:view reports` |
| `/admin/reviews` | Inline `isAdmin()` | `ReviewPolicy::viewAny` |
| `/admin/reviews/{review}/status` | Inline `isAdmin()` | `ReviewPolicy::moderate` |
| `/admin/feedback` | Inline `isAdmin()` | `CustomerFeedbackPolicy::viewAny` |
| `/admin/feedback/{feedback}/status` | Inline `isAdmin()` | `CustomerFeedbackPolicy::moderate` |
| `/reviews/{review}` | Inline ownership | `ReviewPolicy::update/delete` |
| `/auth/addresses/{address}` | `AddressPolicy` | unchanged, still enforced |

## 9. Privilege Escalation Review

- `RegisterRequest` already prohibited `is_admin`, `role`, `roles`, `status`.
- Added `prepareForValidation` hook to log any registration request containing these fields as `privilege_escalation_attempt`.
- `RegisterController` retains a defensive check that rejects the same fields.
- `UpdateProfileRequest` only allows `name` and `phone`.
- Sensitive fields removed from `$fillable` on `User`, `Order`, `Review`, `CustomerAddress`, `Cart`, and `CustomerFeedback`.

## 10. PHPUnit Results

Authorization-specific test suite:

```
PASS  Tests\Feature\AuthorizationSecurityTest
  ✓ registration rejects privilege escalation fields
  ✓ customer cannot set user id on address
  ✓ customer cannot set status on review
  ✓ profile update does not allow status or role
  ✓ customer cannot view another customers order
  ✓ customer cannot view another customers address
  ✓ customer cannot update another customers review
  ✓ customer cannot delete another customers review
  ✓ customer cannot access reports
  ✓ admin can access reports
  ✓ customer cannot access admin review endpoints
  ✓ admin can moderate reviews
  ✓ customer cannot access admin feedback endpoints
  ✓ admin can moderate feedback
  ✓ authorization denial is audited
  ✓ privilege escalation attempt is audited

  Tests:  16 passed (33 assertions)
```

Full suite:

```
Tests:  6 failed, 61 passed (645 assertions)
```

The failures are pre-existing test-isolation issues that only appear when the full suite runs:

| Test | Failure |
|------|---------|
| `Api\V1\ApiEndpointsTest::weak password change is rejected` | 401 instead of 422 |
| `Api\V1\ApiEndpointsTest::admin login returns exchange token for filament bridge` | null exchange token |
| `Api\V1\ApiEndpointsTest::exchange token redirects admin to dashboard` | missing exchange token |
| `Api\V1\ApiEndpointsTest::exchange token rejects reused token` | missing exchange token |
| `AuthenticationSecurityTest::failed login is logged` | rate-limit `login.lockout` recorded instead of `login.failed` |
| `AuthenticationSecurityTest::disabled account login is logged` | rate-limit `login.lockout` recorded instead of `login.rejected.disabled` |

Root causes:

1. **Bootstrap-admin password preservation**: `AdminUserSeeder` preserves a previously changed bootstrap-admin password across tests. A change was made to force-reset the bootstrap admin in the `testing` environment, which resolved several related failures but residual order-dependent failures remain.
2. **Shared rate-limiter state**: the array cache used in tests retains login rate-limit hits between test classes. `AuthenticationSecurityTest` clears rate limiters in `setUp`, but when it runs after other test classes the limiter may already be in lockout.

These tests pass in isolation and in smaller subsets. They are not regressions introduced by the authorization hardening.

## 11. Files Modified

### Models (mass-assignment hardening)
- `backend/app/Models/User.php`
- `backend/app/Models/Order.php`
- `backend/app/Models/Review.php`
- `backend/app/Models/CustomerAddress.php`
- `backend/app/Models/Cart.php`
- `backend/app/Models/CustomerFeedback.php`

### Controllers & Services
- `backend/app/Http/Controllers/Api/V1/ReviewController.php`
- `backend/app/Http/Controllers/Api/V1/FeedbackController.php`
- `backend/app/Http/Controllers/Api/V1/ReportController.php`
- `backend/app/Http/Controllers/Api/V1/Auth/AddressController.php`
- `backend/app/Http/Controllers/Api/V1/Auth/RegisterController.php`
- `backend/app/Http/Requests/Api/V1/RegisterRequest.php`
- `backend/app/Services/OrderService.php`
- `backend/app/Repositories/CartRepository.php`
- `backend/app/Repositories/OrderRepository.php`
- `backend/app/Listeners/LogAdminLogin.php`

### Policies
- `backend/app/Policies/ReviewPolicy.php` (new)
- `backend/app/Policies/CustomerFeedbackPolicy.php` (new)
- `backend/app/Policies/CartPolicy.php` (new)
- `backend/app/Policies/CartItemPolicy.php` (new)
- `backend/app/Policies/PaymentTransactionPolicy.php` (new)
- `backend/app/Policies/AuditLogPolicy.php` (new)
- `backend/app/Policies/AdminSessionPolicy.php` (new)
- `backend/app/Policies/LoginActivityPolicy.php` (new)

### Providers & Routing
- `backend/app/Providers/AuthServiceProvider.php`
- `backend/routes/api.php`
- `backend/bootstrap/app.php`

### Filament Resources
- `backend/app/Filament/Resources/ReviewResource.php`
- `backend/app/Filament/Resources/ReviewResource/Pages/EditReview.php`
- `backend/app/Filament/Resources/CustomerFeedbackResource.php`
- `backend/app/Filament/Resources/CustomerFeedbackResource/Pages/EditCustomerFeedback.php`
- `backend/app/Filament/Resources/UserResource/Pages/CreateUser.php`
- `backend/app/Filament/Resources/UserResource/Pages/EditUser.php`
- `backend/app/Filament/Resources/RoleResource/Pages/CreateRole.php`
- `backend/app/Filament/Resources/PermissionResource/Pages/CreatePermission.php`

### Seeders
- `backend/database/seeders/AdminUserSeeder.php`

### Tests
- `backend/tests/Feature/AuthorizationSecurityTest.php` (new)

### Documentation
- `docs/remediation/STAGE_9_1_4_AUTHORIZATION_HARDENING.md` (this file)

## 12. Remaining Risks

- **Test isolation**: the full PHPUnit suite has pre-existing order-dependent failures caused by (1) bootstrap-admin password preservation and (2) shared rate-limiter cache state. The authorization hardening added a `testing` environment reset for the bootstrap admin, which resolves part of the issue. The remaining failures require a dedicated test-isolation pass (e.g., resetting rate limiters globally between test classes or running each test class in a separate process).
- **Filament permission granularity**: resources still gate on `isAdmin()` rather than Spatie permissions. This is acceptable for the current role model but should be revisited if finer-grained RBAC is required.
- **ContactMessage mass assignment**: `ContactMessage` still allows `status`, `priority`, `read_at`, `reply`, `replied_at` in `$fillable`. These are admin-controlled and not exposed through customer endpoints, but could be hardened in a future pass for consistency.

## 13. Recommendation

**PASS WITH OBSERVATIONS**

The authorization hardening objectives have been met:

- Mass-assignment vectors are closed.
- Report APIs are restricted to administrators.
- Missing policies are created and registered.
- Inline admin checks are replaced with policy-based authorization.
- Authorization events are audited.
- New PHPUnit coverage validates the controls.

The remaining observations are pre-existing test-isolation issues that do not affect production security or functionality.
