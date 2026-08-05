# Phase 13.35 — Staff Management Dynamic RBAC

## Summary

Staff administration is now a dynamic RBAC workspace: permissions are discovered from live Filament admin modules, roles come from Roles Management, create/edit uses a redesigned form (`new_staff.png` reference), staff view exposes full detail + actions, and first-login password change is enforced with reuse prevention.

## Delivered

### UI
- Create/Edit Staff page: `StaffFormPage` + `staff-form.blade.php`
- Sections: Personal, Account, Role & Permissions, Additional
- Sticky Cancel / Create|Save Staff footer
- Staff list KPI grid centered with equal card widths
- Staff View drawer: profile, roles, permissions, overrides, audit, actions

### Permission engine
- `PermissionDiscoveryService` inspects Filament admin pages with navigation
- Generates only actions that exist (view/create/edit/delete/export/approve/reject/publish/archive)
- Searchable grouped permission tree
- Syncs discovered permissions into Spatie `permissions`

### Roles
- Role dropdown + list filter populated from DB Roles (no hardcoded arrays)
- Sorted alphabetically, no customer role

### Create flow
- Creates auth user + admin profile + role + permission overrides
- Generates temporary password, forces change on first login
- Sends `StaffWelcomeNotification` (password never returned via API)
- Transactional; rate-limited create

### Password
- Filament `ForcePasswordChange` + middleware `EnsureAdminPasswordChanged`
- Rules: length, mixed case, number, symbol, confirm, no temp reuse
- Sets `password_changed_at` and clears `force_password_change_at`

### API (admin)
- `GET /api/v1/admin/roles`
- `GET /api/v1/admin/permissions` / `permission-tree`
- `GET|POST /api/v1/admin/staff`
- `GET|PUT|DELETE /api/v1/admin/staff/{staff}`
- `PATCH .../status`, `PATCH .../password-reset`
- `GET .../audit`

## Non-goals
- No production/staging deployment
- No feature-flag changes

## Key files
- `app/Services/Admin/PermissionDiscoveryService.php`
- `app/Services/Admin/StaffAdminService.php`
- `app/Filament/Pages/Administration/StaffFormPage.php`
- `app/Filament/Pages/Administration/StaffPage.php`
- `app/Http/Controllers/Api/V1/Admin/StaffController.php`
- `tests/Feature/Admin/StaffPageTest.php`
