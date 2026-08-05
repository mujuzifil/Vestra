# Product Details Implementation

View Details opens the Products Workspace detail drawer with live database values only.

## Behaviour

- Row action **View Details** → `openDetailDrawer($id)`
- Alpine `@entangle('showDetailDrawer')` keeps drawer visibility in sync with Livewire
- Null/empty fields render as `Not provided`

## Sections

1. **General** — name, SKU, category, status, featured
2. **Pricing** — selling price + currency, cost price + currency, tax rate
3. **Inventory** — stock quantity, low stock threshold, stock status, unit, weight, barcode
4. **Product Information** — short + full description
5. **Images** — all `product_images` with preview links
6. **Audit** — created by, created date, last updated, last updated by

## Edit entry point

When the admin can update the product, the drawer shows **Edit Product**, which opens the redesigned edit modal for the same record.

## Key files

- `resources/views/components/products/detail-drawer.blade.php`
- `ProductAdminService::getDetail()`
- `ProductsPage::openDetailDrawer()` / `getSelectedProductProperty()`
