# Phase 13.19 — Inventory Workspace (Frontend / Blade)

## Blade Page View

`resources/views/filament/pages/products/inventory.blade.php`

CRM workspace using `filament.layouts.crm`, composed from `components/inventory/`.

## Component Tree

```
<x-inventory.page-header />
<x-inventory.kpi-cards />
<x-inventory.filter-bar />
<x-inventory.stock-table />
  <x-inventory.stock-row />
    <x-inventory.status-badge />
<x-inventory.pagination />
<x-inventory.empty-state />
<x-inventory.detail-drawer />
  <x-inventory.status-badge />
```

## Components Reference

| Component | Props |
|---|---|
| `inventory.page-header` | `title`, `description`, `csvUrl`, `excelUrl`, `pdfUrl` |
| `inventory.kpi-cards` | `cards` |
| `inventory.filter-bar` | `warehouseOptions`, `categoryOptions`, `stockStatusOptions` |
| `inventory.stock-table` | `stocks`, `sortField`, `sortDirection` |
| `inventory.stock-row` | `stock` (ProductWarehouseStock) |
| `inventory.status-badge` | `status`, `label`, `color` |
| `inventory.detail-drawer` | `show`, `stock` (array\|null) |
| `inventory.pagination` | `paginator` |
| `inventory.empty-state` | `hasFilters` |

## CSS

File: `resources/css/filament/admin/components/inventory.css`  
Imported from `theme.css`. Namespaced `.vestra-inventory__*` / `.vestra-inventory-detail__*`.

## Design decisions

- No Incoming column; no Transfer button
- Warehouse shown as name + code in table/drawer only
- Adjust Stock form in drawer (quantity + reason)
- Recent movements list in drawer (live StockMovement rows)
- No right-side analytics panel
