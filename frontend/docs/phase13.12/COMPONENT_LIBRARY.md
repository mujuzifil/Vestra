# Phase 13.12 — Component Library

## Backend Blade Components (`resources/views/components/territories/`)

| Component | Purpose |
|-----------|---------|
| `page-header` | Title, subtitle, table/map view toggle, export dropdown, conditional "Add Branch" action |
| `kpi-cards` | 5-column grid of KPI cards |
| `filter-bar` | Search input plus quick country/district/distributor dropdowns and the filter-panel toggle |
| `filter-panel` | Full filter panel: status, country, district, distributor |
| `branch-table` | Sortable enterprise table for the table view |
| `branch-row` | Single branch row with avatar, distributor, manager, location, coordinate badge, service-area count and status |
| `map-panel` | Bounded coordinate-plot canvas with pins, legend and unmapped-branch counter |
| `map-empty-state` | Dashed placeholder shown when zero in-view branches have both coordinates |
| `detail-drawer` | Branch info, address/coordinates, parent distributor, service areas |
| `pagination` | Custom pagination controls |
| `empty-state` | Table-view empty state (no branches / no results) |

## Reusable Admin Components

- `x-admin.kpi-card` — metric card used across workspaces
- `x-filament::icon` — Heroicon support
- `x-filament-panels::page` — page wrapper
- `x-filament-panels::page` layout `filament.layouts.crm` — shared CRM shell used by Companies, Quotes and Territories
