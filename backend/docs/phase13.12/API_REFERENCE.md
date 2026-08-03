# Phase 13.12 — API / Service Reference

## Routes

| Name | Method | Path | Handler |
|------|--------|------|---------|
| `filament.admin.pages.distributors.territories` | GET | `/distributors/territories` | `App\Filament\Pages\Distributors\TerritoriesPage` |
| `filament.admin.distributors.territories.export` | GET | `/distributors/territories/export` | `App\Http\Controllers\Admin\TerritoryExportController` |

Export accepts `format` (`csv`\|`excel`\|`pdf`) plus the same filter query parameters as the page (`search`, `country[]`, `district[]`, `status[]`, `distributor`).

## `App\Services\Admin\TerritoryAdminService`

| Method | Description |
|--------|--------------|
| `queryBranches(array $filters, string $sort, string $direction): Builder` | Base query with `distributor` + `serviceAreas` eager-loaded and filters applied |
| `paginateBranches(array $filters, string $sort, string $direction, int $perPage): LengthAwarePaginator` | Paginated branch list for the table view |
| `getKpiCards(): array` | Total Branches, Active, Inactive, Distinct Distributors, Distinct Countries (with month-over-month trend where meaningful) |
| `getDetail(DistributorBranch $branch): array` | Branch fields + `has_coordinates` flag + `distributor` + `service_areas` for the detail drawer |
| `getMappableBranches(array $filters): Collection` | Branches with **both** `latitude` and `longitude` non-null — the only records ever plotted on the map |
| `countUnmappedBranches(array $filters): int` | Count of branches matching filters but missing one or both coordinates |
| `exportBranches(array $filters): array` | Flat row export used by `TerritoryExportController` |
| `getFilterOptions(): array` | Distinct `countries`, `districts`, static `statuses` map, and `distributors` list for filter UIs |

## Filters

| Filter | Type | Applied to |
|--------|------|------------|
| `search` | string | `name`, `manager_name`, `city`, `district`, `country`, distributor `company_name` (LIKE) |
| `country` | array | `whereIn('country', ...)` |
| `district` | array | `whereIn('district', ...)` |
| `status` | array | `whereIn('status', ...)` (`active`\|`inactive`) |
| `distributor_id` | int | `where('distributor_id', ...)` |

## `TerritoriesPage` Public API (Livewire)

| Method | Description |
|--------|--------------|
| `setViewMode(string $mode)` | Switch between `table` and `map`; invalid values fall back to `table` |
| `openDetailDrawer(int $id)` / `closeDetailDrawer()` | Toggle the branch detail drawer (authorises `view`) |
| `sortBy(string $field)` | Toggle sort field/direction for the table view |
| `applyFilters()` / `resetFilters()` / `clearStatusFilter()` | Filter panel actions |
| `export(string $format)` | Authorises `export`, redirects to the export route |
| `canCreateBranch(): bool` | `Gate::allows('create', DistributorBranch::class)` — used to conditionally show an "Add Branch" action |
