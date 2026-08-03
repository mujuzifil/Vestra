# Phase 13.6 — Permissions

`App\Policies\CompanyProfilePolicy` gates all access to the Companies workspace.

## Policy Matrix

| Ability | Admin | Owner (customer) |
|---------|-------|------------------|
| `viewAny` | ✅ | ❌ |
| `view` | ✅ | ✅ (own profile only) |
| `create` | ✅ | ❌ |
| `update` | ✅ | ✅ (own profile only) |
| `delete` | ✅ | ❌ |
| `export` | ✅ | ❌ |
| `import` | ✅ | ❌ |

## Notes

- The workspace page itself checks `Gate::authorize('viewAny', CompanyProfile::class)` on mount.
- Create, update and delete actions authorise the specific ability before calling `CompanyService`.
- The export route authorises `export` before streaming the file.
