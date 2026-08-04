# Phase 13.18 — Categories Workspace: Architecture

## Request Flow

1. Admin opens `/products/categories` (`CategoriesPage`).
2. `mount()` authorizes `viewAny` on `Category`.
3. Livewire properties drive filters (`search`, `status`, `date_from`, `date_until`) and sort.
4. `CategoryAdminService::paginateCategories()` builds the query with `withCount('products')`.
5. Drawer open loads `getDetail()` with live `products` relation ordered by `sort_order` / `name`.
6. Export hits `products/categories/export` → `CategoryExportController` → `Gate::authorize('export')`.

## Service Responsibilities

| Method | Responsibility |
|--------|----------------|
| `queryCategories` | Search / status / date filters + sorting |
| `paginateCategories` | Length-aware pagination with query string |
| `getKpiCards` | Four live-count KPI cards (no fabricated trends) |
| `getDetail` | Category info + assigned product list |
| `exportRows` | Flat export rows matching active filters |

## Legacy Resource

- `CategoryResource::$shouldRegisterNavigation = false`
- `getNavigationItems()` returns `[]`
- `ListCategories::mount()` redirects to `CategoriesPage::getUrl()`
- Create / Edit pages remain reachable for deep links and the Add Category CTA

## Shared Files Touched

Minimal hunks only in:

- `AdminPanelProvider.php` — page + export route
- `theme.css` — categories CSS import
- `CategoryResource.php` — hide nav
- `ListCategories.php` — redirect
- `CategoryPolicy.php` — `export`

## Out of Scope

- Parent / child category hierarchy
- Tree / nested UI
- Right-side analytics / donut panels
- Fake period-over-period trends
