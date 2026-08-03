# Phase 13.11 — Component Library

## Backend Blade Components (`resources/views/components/partners/`)

| Component | Purpose |
|-----------|---------|
| `page-header` | Title, subtitle, search, refresh, export |
| `kpi-cards` | 5-column KPI grid wrapping `x-admin.kpi-card` |
| `filter-bar` | Status, country, region, sales rep |
| `partner-table` | Sortable table of active partners |
| `partner-row` | Partner name/code, territory, country, type, account manager, credit limit, utilization bar, outstanding, status, actions |
| `status-badge` | Colour-coded status pill (Active/Suspended) |
| `detail-drawer` | Live read-only detail panel (company, contact, account manager, credit, branches, documents, recent orders, activity) |
| `pagination` | Results summary + page controls |
| `empty-state` | Filtered / unfiltered empty messaging |

## Page

`resources/views/filament/pages/distributors/active-partners.blade.php` composes the components above inside `x-filament-panels::page`, following the exact structure used by `sales/quotes.blade.php` and `sales/companies.blade.php`.

## Shared primitives

- `x-admin.kpi-card`
- `x-filament::icon`
- `x-filament-panels::page`
- CSS: `resources/css/filament/admin/components/partners.css` (BEM `.vestra-partners__*` / `.vestra-partners-detail__*`), imported by `resources/css/filament/admin/theme.css`.
