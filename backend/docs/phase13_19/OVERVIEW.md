# Phase 13.19 — Inventory Workspace

## Overview

Enterprise Inventory CRM workspace under the **Products** navigation group. Primary model is `ProductWarehouseStock` (stock levels). Warehouse information is absorbed into Inventory filters and the detail drawer. The previous Warehouses nav item and StockMovement-as-Inventory nav item are permanently hidden.

## Architecture

| Layer | Component |
|---|---|
| Filament Page | `App\Filament\Pages\Products\InventoryPage` |
| Admin Service | `App\Services\Admin\InventoryAdminService` |
| Mutation Service | `App\Services\InventoryService::adjustStock` |
| Export Controller | `App\Http\Controllers\Admin\InventoryExportController` |
| Policy | `App\Policies\ProductWarehouseStockPolicy` |
| Blade Page View | `filament/pages/products/inventory.blade.php` |
| Blade Components | `components/inventory/*` |
| CSS | `resources/css/filament/admin/components/inventory.css` |
| Tests | `tests/Feature/Admin/InventoryPageTest.php` |

## Navigation

- Group: **Products**
- Label: **Inventory**
- Icon: `heroicon-o-cube-transparent`
- Sort: **3**
- Slug: `products/inventory`
- Layout: `filament.layouts.crm`

## Models

| Model | Role |
|---|---|
| `ProductWarehouseStock` | Primary list rows — quantity, reserved, reorder_level, availableQuantity() |
| `Warehouse` | Filter + drawer only (name, code, address, is_active) |
| `StockMovement` | Recent movements in drawer (in\|out\|adjustment\|transfer_in\|transfer_out) |
| `Product` | Name, SKU, price, category, images |

## Integrity locks

- No **Incoming** column
- No **Stock Transfer** button / no `transferStock` service
- Adjust Stock only via `InventoryService::adjustStock`
- No right analytics panel
- No fake trend percentages — KPI cards use `trend_available: false`
- Warehouse tables / API / PO FKs retained

## Nav changes

- `WarehouseResource`: `shouldRegisterNavigation = false`, empty `getNavigationItems()`
- `StockMovementResource`: hidden + `ListStockMovements` redirects to `InventoryPage`
- `ProductWarehouseStockResource`: remains hidden; list redirects to `InventoryPage`

## Validation

Run: `php artisan test --filter=InventoryPageTest`
