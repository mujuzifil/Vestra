# Phase 13.12 — Territories CRM Architecture

## Overview

The Distributors → Territories workspace is a fully custom Filament/Livewire page that provides an enterprise CRM view over the existing `DistributorBranch` entity — it does **not** introduce a new "Territory" model. It reuses the Workspace Design System established by Dashboard, Tasks, Notifications, Activity and the Companies/Quotes workspaces.

## Components

| Layer | Responsibility |
|-------|----------------|
| `App\Filament\Pages\Distributors\TerritoriesPage` | Livewire page: URL-bound filters, table/map view toggle, detail drawer, export action |
| `App\Services\Admin\TerritoryAdminService` | Pagination, KPIs, filter options, detail assembly, mappable-branch filtering, export rows |
| `App\Http\Controllers\Admin\TerritoryExportController` | Dedicated authenticated export route for CSV/Excel/PDF |
| `App\Models\DistributorBranch` | Existing model — `distributor()` (BelongsTo) and `serviceAreas()` (HasMany) relations reused as-is |
| `App\Models\DistributorServiceArea` | Existing model, eager-loaded for the detail drawer |
| Blade components under `resources/views/components/territories/` | Page header, KPI cards, filter bar/panel, branch table/row, map panel + empty state, detail drawer, pagination, empty state |

## Data Flow

1. Admin opens `/distributors/territories`.
2. `TerritoriesPage` authorises `viewAny` via `DistributorBranchPolicy`.
3. `TerritoryAdminService::queryBranches()` applies search (`name`, `manager_name`, `city`, `district`, `country`, and the parent distributor's `company_name`), plus `country`, `district`, `status` and `distributor_id` filters, and eager-loads `distributor` and `serviceAreas`.
4. **Table view** paginates the query and renders `x-territories.branch-table`.
5. **Map view** calls `TerritoryAdminService::getMappableBranches()`, which applies the same filters plus `whereNotNull('latitude')->whereNotNull('longitude')`. Only branches with real coordinates are ever plotted; `countUnmappedBranches()` reports how many matching branches were excluded so admins understand why.
6. KPI cards are computed live: Total Branches, Active, Inactive, Distinct Distributors (`distinct('distributor_id')->count()`), Distinct Countries (`distinct('country')->count()`).
7. The detail drawer is populated by `TerritoryAdminService::getDetail()`, which returns the branch, its formatted address, coordinate presence flag, the parent `distributor` (company name, trading name, status, contact info) and all `service_areas`.
8. Exports stream through `TerritoryExportController` using the shared `ReportExportService`, applying the same filters as the current view.

## Map Rendering Strategy

There is no map tiling/geocoding provider (Leaflet, Mapbox, Google Maps) wired into this codebase. Rather than adding a new external dependency out of scope for this phase, or fabricating a geographic backdrop, the map view (`x-territories.map-panel`) renders a **proportional coordinate plot**:

- The bounding box (`min`/`max` latitude and longitude) of the currently mappable branches is computed in the view.
- Each branch pin is positioned as a percentage offset within that bounding box — a direct, traceable function of its real `latitude`/`longitude`, never an estimate.
- Hovering or focusing a pin reveals the branch name and its exact coordinates.
- When zero branches in the current filter set have both coordinates, `x-territories.map-empty-state` is rendered instead — a dashed placeholder canvas with a clear explanation and, if applicable, a count of branches that matched the filters but lack coordinates.

## Navigation & Legacy Resource

- `DistributorBranchResource` navigation is disabled (`$shouldRegisterNavigation = false`, `getNavigationItems()` returns `[]`), matching the pattern used for `CustomerResource` → Companies.
- `ListDistributorBranches::mount()` redirects to `TerritoriesPage::getUrl()`.
- The resource's `create`/`edit` pages remain reachable directly (e.g. via a future "Add Branch" action, gated on the `create` ability) for record-level operations that are out of scope for the read-oriented workspace.
