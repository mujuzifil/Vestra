# Phase 13.19 — Backend Architecture

## InventoryAdminService

Location: `app/Services/Admin/InventoryAdminService.php`

### Methods

| Method | Description |
|---|---|
| `paginateStock(filters, sort, direction, perPage)` | Paginated `ProductWarehouseStock` with product.category, product.images, warehouse |
| `queryStock(filters, sort, direction)` | Filtered `Builder` |
| `getKpiCards()` | Inventory Value, Total Units, Low Stock, Out of Stock, Movements |
| `getDetail(ProductWarehouseStock)` | Drawer payload including warehouse + recent movements |
| `exportRows(filters)` | Export rows |
| `getFilterOptions()` | Warehouses, categories, stock statuses |
| `resolveStockStatus(ProductWarehouseStock)` | `in` / `low` / `out` |

### KPI computation (SQLite-safe)

Aggregations use PHP collections after loading stock rows with `product:id,price`:

- **Inventory Value** = `sum(quantity * product.price)`
- **Total Units** = `sum(quantity)`
- **Low Stock** = available > 0 AND available ≤ reorder_level
- **Out of Stock** = available ≤ 0
- **Movements** = `StockMovement::count()`

### Filter Keys

```php
[
    'search'       => string|null,  // product name / sku
    'warehouse'    => int[],
    'category'     => int[],
    'stock_status' => string[],     // in|low|out
    'date_from'    => string|null,  // stock updated_at
    'date_until'   => string|null,
]
```

## InventoryPage

Location: `app/Filament/Pages/Products/InventoryPage.php`

Livewire Filament Page with `WithPagination` and `#[Url]` filter state.

### Actions

| Action | Gate | Behaviour |
|---|---|---|
| `openDetailDrawer` / `closeDetailDrawer` | `view` | Drawer state |
| `adjustStock` | `update` | Calls `InventoryService::adjustStock` + audit log |
| `getExportUrl` | (export route) | CSV / Excel / PDF |

## Policy

`ProductWarehouseStockPolicy`: `viewAny`, `view`, `update`, `export` — admin only.

Registered in `AuthServiceProvider`.

## Export

Route: `filament.admin.products.inventory.export`  
Path: `products/inventory/export`  
Controller: `InventoryExportController`  
Authorization: `Gate::authorize('export', ProductWarehouseStock::class)`
