# Phase 13.10 — Applications Workspace: Frontend Data Mapping

> This mirrors `backend/docs/phase13_10/DATABASE_MAPPING.md` from the perspective of what data
> reaches each UI element. The workspace is server-rendered (Livewire), so there is no separate
> client-side API/data-fetching layer — Blade components receive fully-hydrated PHP arrays/models
> from `ApplicationAdminService` and `ApplicationsPage`.

## Data flow

```
DistributorRequest (Eloquent model, table: distributor_requests)
        │
        ▼
ApplicationAdminService::queryApplications() / paginateApplications()
        │  (filters: search, status, priority, country, region, assigned_to, date range)
        │  (eager load: assignedAdministrator)
        ▼
ApplicationsPage (Livewire component — public properties bound via #[Url])
        │
        ▼
components/applications/* Blade components (KPI cards, table, drawer, filters)
```

## UI element → data source

| UI element | Data source |
|---|---|
| KPI cards | `ApplicationAdminService::getKpiCards()` → live `count()` queries per `DistributorStatus` |
| Table rows | `ApplicationsPage::getApplicationsProperty()` → paginated, filtered, sorted `DistributorRequest` collection |
| Status badge | `DistributorRequest::statusColor()` / `statusLabel()`, driven by `DistributorStatus` enum |
| Priority badge | `DistributorRequest::priorityColor()` / `priorityLabel()`, driven by `Priority` enum |
| Assignee column | `assignedAdministrator` relation (`users.name`), eager-loaded to avoid N+1 |
| Filter option lists | `ApplicationAdminService::getFilterOptions()` — distinct `country`/`region` values, `Priority::cases()`, admin/assignee list |
| Detail drawer | `ApplicationAdminService::getDetail()` — full field set + `assignee` + resulting `distributor` lookup |
| Export rows | `ApplicationAdminService::exportRows()` — same filtered query, flattened for CSV/Excel/PDF |

## No client-side state duplication

All filter/sort/pagination state lives in Livewire public properties on `ApplicationsPage`
(synced to the URL via `#[Url]`), and every render re-queries the database through
`ApplicationAdminService`. There is no separate frontend cache/store that could drift from the
database.
