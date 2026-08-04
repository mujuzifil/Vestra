# Phase 13.18 — Categories Workspace: Permissions

## Policy

`App\Policies\CategoryPolicy` (registered in `AuthServiceProvider`)

| Ability | Rule |
|---------|------|
| `viewAny` | `$user->isAdmin()` |
| `view` | `$user->isAdmin()` |
| `create` | `$user->isAdmin()` |
| `update` | `$user->isAdmin()` |
| `delete` | `$user->isAdmin()` |
| `export` | `$user->isAdmin()` **(new in 13.18)** |

## Gate Usage

| Surface | Ability |
|---------|---------|
| `CategoriesPage::mount` | `viewAny` |
| Drawer open / selected detail | `view` |
| Add Category CTA | `create` (button hidden when denied) |
| Row / drawer Edit link | `update` |
| Export route / `export()` | `export` |

## Route Names

| Name | Path |
|------|------|
| `filament.admin.pages.products.categories` | `/products/categories` |
| `filament.admin.products.categories.export` | `/products/categories/export` |

## Access Expectations

- Guests → redirect to login
- Non-admin authenticated users → `403` on page and export
- Admins → full workspace + CSV/Excel/PDF export
