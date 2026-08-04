# Phase 13.19 — Permissions

## ProductWarehouseStockPolicy

| Ability | Rule |
|---|---|
| `viewAny` | `$user->isAdmin()` |
| `view` | `$user->isAdmin()` |
| `update` | `$user->isAdmin()` (required for Adjust Stock) |
| `export` | `$user->isAdmin()` |

## Page access

`InventoryPage::mount()` authorizes `viewAny` on `ProductWarehouseStock`.

## Resource access (legacy)

`WarehouseResource`, `StockMovementResource`, and `ProductWarehouseStockResource` continue to use `canAccess(): isAdmin()` for any remaining deep-link routes. Navigation is permanently hidden.

## Mutations

Stock quantity changes from this workspace always go through `InventoryService::adjustStock` (creates an `adjustment` `StockMovement`). No transfer UI is exposed.
