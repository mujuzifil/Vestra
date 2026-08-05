# Product Edit Workflow

## Entry points

1. Table row action **Edit Product**
2. Detail drawer **Edit Product**
3. After create, details open for the new product (edit remains available)

## Flow

1. `openEditModal($id)` authorizes update and hydrates `$form` from the product
2. Modal shows existing images with remove controls
3. `saveProduct()` validates, persists via `ProductAdminService::updateProduct()`, stores new uploads
4. Form closes; if details were open (or create), detail drawer refreshes for the same product
5. Table + KPI cards re-render from Livewire computed properties (no full page reload)

## Persistence

New catalog columns:

- `cost_price`, `currency`, `cost_currency`
- `low_stock_threshold`, `stock_status`
- `unit`, `weight`, `barcode`, `tax_rate`
- `created_by`, `updated_by`

Migration: `2026_08_05_100000_add_catalog_fields_to_products_table.php`

## Validation

Existing uniqueness/required rules retained; new fields use nullable/required rules aligned with the redesign.
