# Phase 13.18 — Categories Workspace: Data Mapping

## Category Model Fields

| Field | Type | Notes |
|-------|------|-------|
| `id` | int | Primary key |
| `name` | string | Display name |
| `slug` | string | URL slug (`HasSlug`) |
| `description` | text\|null | Optional storefront copy |
| `sort_order` | int | Lower numbers first |
| `status` | string | `active` \| `inactive` |
| `created_at` | datetime | Used by date filter |
| `updated_at` | datetime | Table column |

## Relations

| Relation | Type | Notes |
|----------|------|-------|
| `products()` | `HasMany` Product | Ordered by `sort_order` |

## KPI Queries

| Card | Query |
|------|-------|
| Total | `Category::count()` |
| Active | `status = active` |
| With products | `has('products')` |
| Empty | `doesntHave('products')` |

Trends are intentionally omitted (`trend_available = false`).

## Filters

| Filter | Column / logic |
|--------|----------------|
| Search | `name`, `slug`, `description` LIKE |
| Status | `whereIn(status, …)` |
| Date from / until | `whereDate(created_at, …)` |

## Detail Drawer Payload

- Identity: name, slug, status, sort_order, timestamps
- Description text
- `products[]`: id, name, sku, status label, stock_quantity, price

## Export Columns

`name`, `slug`, `status`, `sort_order`, `products_count`, `description`, `created_at`, `updated_at`
