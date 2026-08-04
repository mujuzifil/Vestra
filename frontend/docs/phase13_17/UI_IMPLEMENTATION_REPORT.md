# Phase 13.17 — Products Workspace Frontend Notes

## Admin Panel (Filament/Livewire)
Products catalog workspace is server-rendered in Filament. No separate Vue/React surface for this phase.

## Blade Component Hierarchy
```
filament/pages/products/catalog.blade.php
  └─ x-products.page-header
  └─ x-products.kpi-cards
  └─ x-products.filter-bar
  └─ x-products.product-table
       └─ x-products.product-row
            └─ x-products.status-badge
            └─ x-products.stock-badge
  └─ x-products.pagination
  └─ x-products.empty-state
  └─ x-products.detail-drawer
```

## CSS Architecture
File: `resources/css/filament/admin/components/products-workspace.css`  
Namespace: `.vestra-products__*` / `.vestra-products-detail__*`

Legacy Filament resource styles remain in `products.css`.

## Interactions
- Search debounce 300ms
- Multi-select status/category; radio stock/featured
- Sortable columns; detail drawer on row action
- Export CSV/Excel/PDF; Add Product when create allowed

## Accessibility
- Interactive controls use `aria-label`
- Drawer is `role="dialog"` with Escape to close
- Table region is keyboard-focusable
