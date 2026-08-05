# Products UI Redesign

Phase 13.32 replaces the Products Workspace Add/Edit flows with modal UIs matched to `add_products.png` and `edit_products.png`.

## Scope

- Add Product modal on `ProductsPage` (no Filament resource create redirect)
- Edit Product modal loading live product values
- Shared form layout, spacing, typography, and footer actions
- Workspace CSS in `products-workspace.css`

## Layout mapping

| Row | Fields |
|-----|--------|
| 1 | Product Name *, SKU * |
| 2 | Short Description |
| 3 | Full Description |
| 4 | Category *, Price * (+ currency), Cost Price (+ currency) |
| 5 | Stock Quantity *, Low Stock Threshold *, Stock Status * |
| 6 | Unit, Weight (kg), Barcode |
| 7 | Featured toggle, Status *, Tax Rate (%) |
| Images | Dropzone (add) / gallery + Add Image + dropzone (edit) |

## Data sources

Dropdowns and defaults come from `ProductAdminService::getFormOptions()`:

- Categories from `categories`
- Status from `ProductStatus`
- Stock status from `ProductStockStatus`
- Currencies from settings + distinct payment currencies (never hardcoded)
- Units from distinct `products.unit` values
- Default tax / low-stock threshold from settings when present

## Key files

- `app/Filament/Pages/Products/ProductsPage.php`
- `resources/views/components/products/product-form.blade.php`
- `resources/css/filament/admin/components/products-workspace.css`
