# Phase 13.18 — Categories Workspace: Overview

## Summary

Phase 13.18 delivers an enterprise CRM workspace for managing flat `Category` records within the **Products** navigation group. The workspace replaces the legacy Filament resource list with a dedicated Livewire-powered page that mirrors Applications / Feedback architecture.

## Scope

| Area | Deliverable |
|------|-------------|
| Filament Page | `CategoriesPage` (slug: `products/categories`, sort: 2) |
| Admin Service | `CategoryAdminService` — paginate, KPI, filter, detail, export |
| Policy | `CategoryPolicy::export()` added |
| Legacy redirect | `ListCategories` redirects to `CategoriesPage` |
| Export | `CategoryExportController` (CSV / Excel / PDF) |
| Provider | `AdminPanelProvider` registers page + export route |
| Blade views | Components under `components/categories/` |
| CSS | `components/categories.css` imported in `theme.css` |
| Tests | `CategoriesPageTest` |
| Docs | 4 backend + 4 frontend markdown files |

## Architecture

```
CategoriesPage (Filament\Pages\Page + WithPagination)
    │
    ├── CategoryAdminService
    │     ├── paginateCategories()   → withCount('products')
    │     ├── getKpiCards()          → Total / Active / With products / Empty
    │     ├── getDetail()            → info + assigned products
    │     └── exportRows()
    │
    └── Blade view: filament/pages/products/categories.blade.php
          ├── x-categories.page-header
          ├── x-categories.kpi-cards
          ├── x-categories.filter-bar
          ├── x-categories.category-table  → x-categories.category-row
          ├── x-categories.pagination
          └── x-categories.detail-drawer
```

## Navigation

- **Group**: Products
- **Label**: Categories
- **Sort**: 2
- **Icon**: `heroicon-o-tag`
- **Slug**: `products/categories`

## Design Principles

- Live DB only — no fake/seeded analytics
- Flat schema only — no parent category / no tree view
- No right analytics panel
- Add Category button only when `Gate::allows('create', Category::class)`
- Mutations for create/edit remain on `CategoryResource` deep-link forms
