# Phase 13.7 — Component Library

## Backend Blade Components (`resources/views/components/quotes/`)

| Component | Purpose |
|-----------|---------|
| `page-header` | Title, subtitle, search, refresh, export |
| `kpi-cards` | 5-column KPI grid wrapping `x-admin.kpi-card` |
| `filter-bar` | Status, priority, sales rep, district, city, dates, value range |
| `quote-table` | Sortable table with bulk select |
| `quote-row` | Quote number, company, contact, products, value, badges, actions |
| `status-badge` | Colour-coded status pill |
| `priority-badge` | Colour-coded priority pill |
| `detail-drawer` | Live detail panel with relationships |
| `quote-form` | Edit drawer for admin-updatable fields |
| `pagination` | Results summary + page controls |
| `empty-state` | Filtered / unfiltered empty messaging |

## Shared primitives

- `x-admin.kpi-card`
- `x-filament::icon`
- `x-filament-panels::page`
- CSS: `resources/css/filament/admin/components/quotes.css`
