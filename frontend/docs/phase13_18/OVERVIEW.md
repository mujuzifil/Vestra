# Phase 13.18 — Categories Workspace (Frontend / Blade)

## Blade Page View

`resources/views/filament/pages/products/categories.blade.php`

Renders the CRM workspace using `filament.layouts.crm`. Composed from `components/categories/`.

## Component Tree

```
<x-categories.page-header />      — Search + export + conditional Add Category
<x-categories.kpi-cards />        — 4 KPI metric cards
<x-categories.filter-bar />       — Status / Created date filters
<x-categories.category-table />   — Sortable table
  <x-categories.category-row />
    <x-categories.status-badge />
<x-categories.pagination />
<x-categories.empty-state />
<x-categories.detail-drawer />    — Info, description, assigned products
```

## CSS

File: `resources/css/filament/admin/components/categories.css`

Imported via: `resources/css/filament/admin/theme.css`

Namespaced under `.vestra-categories__*` and `.vestra-categories-detail__*`.

## Integrity Locks (UI)

- No tree / nested category view
- No right-side analytics panel
- No parent category fields
- Add Category only when create gate allows
