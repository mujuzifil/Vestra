# Phase 13.19 — Component Library

## Inventory workspace components

All under `backend/resources/views/components/inventory/`.

| File | Purpose |
|---|---|
| `page-header.blade.php` | Title, search, refresh, export dropdown |
| `kpi-cards.blade.php` | 5 KPI cards via `x-admin.kpi-card` |
| `filter-bar.blade.php` | Warehouse, category, stock status, updated date |
| `stock-table.blade.php` | Sortable table shell |
| `stock-row.blade.php` | Product thumb, SKU, warehouse, qty, available, reserved, value, status |
| `status-badge.blade.php` | In / Low / Out badges |
| `pagination.blade.php` | Livewire pagination controls |
| `empty-state.blade.php` | Empty + filtered empty |
| `detail-drawer.blade.php` | Stock, product, warehouse, adjust form, movements |

## Shared dependencies

- `x-admin.kpi-card`
- `x-filament::icon`
- Alpine.js for dropdowns / drawer transitions
- Livewire `wire:model` / `wire:click`
