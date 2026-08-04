# Phase 13.15 — Enquiries Workspace: Permissions

## Policy: ContactMessagePolicy

| Method | Signature | Rule |
|--------|-----------|------|
| `viewAny` | `(User $user)` | `$user->isAdmin()` |
| `view` | `(User $user, ContactMessage $msg)` | `$user->isAdmin()` |
| `create` | `(User $user)` | `false` — creation disabled by policy |
| `update` | `(User $user, ContactMessage $msg)` | `$user->isAdmin()` |
| `delete` | `(User $user, ContactMessage $msg)` | `$user->isAdmin()` |
| `export` | `(User $user)` | `$user->isAdmin()` ← **added in Phase 13.15** |

## Gate Calls in EnquiriesPage

| Action | Gate call |
|--------|-----------|
| Page mount | `Gate::authorize('viewAny', ContactMessage::class)` |
| Open drawer | `Gate::authorize('view', $enquiry)` |
| Get detail | `Gate::authorize('view', $enquiry)` |
| Save reply draft | `Gate::authorize('update', $enquiry)` |
| Send reply | `Gate::authorize('update', $enquiry)` |
| Assign | `Gate::authorize('update', $enquiry)` |
| Update status | `Gate::authorize('update', $enquiry)` |
| Save internal notes | `Gate::authorize('update', $enquiry)` |

## Gate Call in EnquiryExportController

```php
Gate::authorize('export', ContactMessage::class);
```

This is a class-level (policy-without-model) gate. The `export` method on the policy receives only `User $user`.

## ContactMessageResource Access

```php
public static function canAccess(): bool
{
    return auth()->user()?->isAdmin() ?? false;
}
```

The resource's deep-link pages (`view`, `edit`) continue to work for admins through this gate. The `canCreate()` method returns `false`.

## Navigation Hiding

```php
protected static bool $shouldRegisterNavigation = false;

public static function getNavigationItems(): array
{
    return [];
}
```

These prevent the legacy resource from appearing in the sidebar; `EnquiriesPage` is the sole navigation entry.

## Non-Admin Behaviour

- Unauthenticated users are redirected to the login page by Filament middleware.
- Authenticated non-admin users receive a `403 Forbidden` response from `Gate::authorize('viewAny', ...)` in `mount()`.
- The export route returns `403` via the same policy gate.
