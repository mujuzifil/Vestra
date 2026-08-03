# Phase 13.10 — Applications Workspace: UI Implementation Report

## Overview
Replaced the default Filament `DistributorRequestResource` table/pages presentation with a
custom CRM workspace page (`ApplicationsPage`) under **Distributors → Applications**, mirroring
the structure and interaction patterns already established by `QuotesPage` (Sales) and
`CompaniesPage` (Sales).

## Page

`backend/app/Filament/Pages/Distributors/ApplicationsPage.php`

- Layout: `filament.layouts.crm`
- Navigation: group `Distributors`, label `Applications`, icon `heroicon-o-clipboard-document-list`, sort `1`
- Slug: `distributors/applications`
- View: `filament.pages.distributors.applications`
- Authorization: `Gate::authorize('viewAny', DistributorRequest::class)` on `mount()`
- No "+ New Application" action — `DistributorRequestPolicy::create()` returns `false`, and applications
  can only be created via the public distributor onboarding form.

### Livewire state

| Property | Purpose |
|---|---|
| `search` | Free-text search (company, contact, email, phone, address, description) |
| `statusFilter[]` | Filter by one or more `DistributorStatus` values |
| `priorityFilter[]` | Filter by one or more `Priority` values |
| `countryFilter[]` / `regionFilter[]` | Geography filters |
| `assignedToFilter` | Filter by assigned administrator |
| `dateFrom` / `dateUntil` | Submission date range |
| `sortField` / `sortDirection` | Column sorting |
| `perPage` | Pagination page size |
| `selectedIds[]` | Row selection for bulk actions |
| `showDetailDrawer` / `selectedApplicationId` | Detail drawer state |

All filter/sort/pagination state is bound to the URL via `#[Url]` attributes so views are
shareable/bookmarkable, matching the Quotes/Companies pattern.

### Actions

- `openDetailDrawer($id)` / `closeDetailDrawer()` — slide-over detail view
- `approve($id)` / `reject($id)` — single-record actions, delegate to `DistributorOnboardingService`
- `bulkApprove()` / `bulkReject()` — operate over `selectedIds`, delegate to the same service
- `toggleSelectAll()`, `sortBy($field)`, `resetFilters()`
- `getExportUrl($format)` — builds a signed-in export URL (CSV/Excel/PDF) preserving active filters

No approve/reject/onboarding business logic is reimplemented in the page — it strictly calls
`DistributorOnboardingService::approve()` / `::reject()`, the same service used elsewhere in the
admin panel, so distributor account creation side effects remain centralized.

## Service

`backend/app/Services/Admin/ApplicationAdminService.php`

- `paginateApplications()` / `queryApplications()` — builds the filtered/sorted Eloquent query with
  `->with(['assignedAdministrator'])` eager loading to avoid N+1 queries when rendering assignee names.
- `getKpiCards()` — six live KPI cards (Total, Pending, Under Review, Information Requested, Approved,
  Rejected), each with a live month-over-month trend computed from actual row counts. No fabricated
  "average processing time" metric was added (data does not exist to support it truthfully).
- `getDetail()` — full detail payload for the drawer, including a lookup of the resulting `Distributor`
  record if the application was approved.
- `getFilterOptions()` — distinct countries/regions in use, `Priority` cases, and admin/assignee list.
- `exportRows()` — flattened rows for CSV/Excel/PDF export, filter-aware.

## Blade components (`backend/resources/views/components/applications/`)

`page-header`, `kpi-cards`, `status-badge`, `priority-badge`, `filter-bar`, `application-table`,
`application-row`, `pagination`, `empty-state`, `detail-drawer` — all follow the same BEM class
naming and markup structure as the Quotes/Companies component sets, adapted for distributor
application fields (business type, country/region, estimated volume, existing customer, etc).

Main view: `backend/resources/views/filament/pages/distributors/applications.blade.php`

## Styling

`backend/resources/css/filament/admin/components/applications.css` — BEM-scoped
`.vestra-applications__*` classes, imported once from `theme.css`. Uses a 6-column KPI grid
(`vestra-kpi-grid--6`) to accommodate the six status-based cards.

## Export

`backend/app/Http/Controllers/Admin/ApplicationExportController.php` streams CSV/Excel/PDF via the
shared `ReportExportService`, gated by `DistributorRequestPolicy::export()`. Registered at
`distributors/applications/export` in `AdminPanelProvider::authenticatedRoutes()`.

## Legacy resource

`DistributorRequestResource` navigation is disabled (`$shouldRegisterNavigation = false`,
`getNavigationItems()` returns `[]`), and its `ListDistributorRequests` page now redirects to
`ApplicationsPage::getUrl()` so any existing bookmarks/links continue to work.

## Tests

`backend/tests/Feature/Admin/ApplicationsPageTest.php` — 19 tests covering routing, guest/non-admin
access, KPI live counts, empty state, search/status/country filtering, detail drawer contents,
single and bulk approve/reject (including resulting `Distributor` creation), export authorization
and CSV output, and pagination reset behavior. All 19 tests pass (50 assertions).
