# Phase 13.11 — Permissions

## `DistributorPolicy`

| Ability | Rule | Used by |
|---------|------|---------|
| `viewAny` | `$user->isAdmin()` | `ActivePartnersPage::mount()` |
| `view` | `$user->isAdmin() \|\| $user->id === $distributor->user_id` | `ActivePartnersPage::openDetailDrawer()`, `getSelectedPartnerProperty()` (existing ability, unchanged) |
| `update` | `$user->id === $distributor->user_id \|\| $user->isAdmin()` | Existing, unchanged — not used by this workspace (read-only) |
| `manage` | `$user->id === $distributor->user_id \|\| $user->isAdmin()` | Existing, unchanged |
| `export` | `$user->isAdmin()` | `ActivePartnersPage::export()`, `PartnerExportController` |

`viewAny` and `export` were added in this phase; all other abilities were pre-existing.

## Access control flow

1. **Route/page mount** — `Gate::authorize('viewAny', Distributor::class)` in `ActivePartnersPage::mount()`. Non-admins receive a 403 (Livewire `assertForbidden()`), guests are redirected to login by Filament's `Authenticate` middleware.
2. **Detail drawer** — `Gate::authorize('view', $distributor)` before loading detail data for a specific record.
3. **Export** — `Gate::authorize('export', Distributor::class)` in both the Livewire `export()` method and the `PartnerExportController` (defense in depth, matching the Quotes/Companies export pattern).

## Navigation visibility

`DistributorResource::$shouldRegisterNavigation = false` and `getNavigationItems()` returns `[]`, so the legacy resource never appears in the sidebar. Its routes remain registered (so `/distributors/{id}` deep links used by relation managers, notifications, etc. continue to resolve), but `ListDistributors::mount()` immediately redirects to `ActivePartnersPage::getUrl()`.

This mirrors the exact pattern used for `CustomerResource` → `CompaniesPage` in Phase 13.6r.
