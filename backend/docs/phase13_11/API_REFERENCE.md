# Phase 13.11 — API Reference

## Workspace routes (Filament admin panel)

| Method | Path | Name | Description |
|--------|------|------|-------------|
| GET | `/distributors/active-partners` | `filament.admin.pages.distributors.active-partners` | Active Partners workspace page |
| GET | `/distributors/active-partners/export` | `filament.admin.distributors.active-partners.export` | Export filtered partners |
| GET | `/distributors` | `filament.admin.resources.distributors.index` | Legacy list — redirects (302) to Active Partners workspace |
| GET | `/distributors/{record}` | `filament.admin.resources.distributors.view` | Legacy deep-link view (kept for record view/edit access) |

### Export query parameters

`format` (`csv`\|`excel`\|`pdf`), `search`, `status[]`, `country[]`, `region[]`, `sales_rep`

### Export columns

Company Name, Trading Name, Status, Partner Type, Country, District, City, Email, Phone, Sales Rep, Credit Limit, Credit Balance, Registration Number, Created At.

## Livewire component surface (`ActivePartnersPage`)

| Property | Type | Notes |
|----------|------|-------|
| `search` | `string` | Debounced 300ms, URL-bound (`?search=`) |
| `statusFilter` | `array` | Values from `DistributorAccountStatus` (`active`, `suspended`) |
| `countryFilter` | `array` | Distinct `distributors.country` values |
| `regionFilter` | `array` | Distinct `distributor_service_areas.region` values |
| `salesRepFilter` | `?int` | `sales_representatives.id` |
| `sortField` / `sortDirection` | `string` | `company_name`, `status`, `country`, `created_at`, `updated_at`, `sales_rep` |
| `showDetailDrawer` / `selectedPartnerId` | `bool` / `?int` | Drives the read-only detail drawer |

### Public methods

- `openDetailDrawer(int $id)` / `closeDetailDrawer()`
- `sortBy(string $field)`
- `resetFilters()`
- `export(string $format)` — redirects to the export route
- `getExportUrl(string $format): string`
- `hasActiveFilters(): bool`

## Service surface (`PartnerAdminService`)

- `paginatePartners(array $filters, string $sort, string $direction, int $perPage): LengthAwarePaginator`
- `getKpiCards(): array` — Total Partners, Active Partners, Suspended Partners, Revenue (This Month, only if `orders.distributor_id` + `orders.payment_status` exist), Credit Outstanding.
- `getDetail(Distributor $distributor): array` — company, primary contact, sales rep, branches, service areas, credit, documents, recent orders, recent activity.
- `exportPartners(array $filters): array`
- `getFilterOptions(): array` — `countries`, `regions`, `sales_reps`.

## Existing APIs (unchanged)

No public/customer-facing API endpoints were modified in this phase. `DistributorResource`, `DistributorRequestResource`, and all distributor-related relation managers continue to function unchanged aside from the navigation visibility change described in `OVERVIEW.md`.
