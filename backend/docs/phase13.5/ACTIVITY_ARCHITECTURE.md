# Activity Centre Architecture

## Overview

Phase 13.5 replaces the placeholder `Workspace → Activity` page with a fully custom, enterprise-grade Activity Centre. The page renders a unified operational timeline built exclusively from two existing backend sources:

- `App\Models\AuditLog` — user actions, subject records, and metadata.
- `App\Models\LoginActivity` — authentication success/failure events with device and location context.

No new activity tables, migrations, or fabricated metrics are introduced.

## Domain Enums

Two typed enums centralise classification:

- `App\Enums\ActivityCategory` — Authentication, Sales, CRM, Distributors, Support, Products, Marketing, Administration, System, Security.
- `App\Enums\ActivityStatus` — Success, Information, Warning, Error.

Each enum exposes `label()`, `icon()`, and `color()` methods and follows the same convention as the existing notification enums.

## Service Layer

`App\Services\Admin\ActivityService` owns all read-side activity logic:

- `getActivities(array $filters, int $perPage, int $page)` — queries both tables, normalises every row into a single DTO, applies filters, sorts by `created_at DESC`, and returns a manually constructed `LengthAwarePaginator`.
- `getKpiCards(array $filters)` — derives five KPI cards from the same filtered dataset:
  - Total Activities
  - User Activities (activities with an associated user)
  - Security Events (category = Security)
  - Module Activities (activities linked to a subject)
  - System Events (activities without a user)
- `forExport(array $filters)` — returns the full filtered result set as plain arrays for CSV/Excel/PDF export.
- `getFilterOptions()` — supplies distinct module labels and recently active users for the filter dropdowns.
- `findActivity(string $compositeId)` — resolves an `audit-{id}` or `login-{id}` identifier back into a detail DTO.

## Livewire Page

`App\Filament\Pages\Workspace\ActivityPage` extends `Filament\Pages\Page` and keeps the CRM layout (`filament.layouts.crm`). It supports:

- URL-backed filters: `search`, `category`, `status`, `module`, `user`, `date_from`, `date_until`.
- Pagination via `Livewire\WithPagination`.
- Detail side panel driven by `showDetailPanel` and `selectedActivityId`.
- Export actions for CSV, Excel, and PDF gated by `AuditLogPolicy::export()`.
- Selection state (`selectedIds`) for future bulk actions.

Authorisation is enforced in `mount()` (`viewAny` on `AuditLog`) and in `authorizeActivity()` (`view` on the underlying `AuditLog` for audit rows; `viewAny` for login rows).

## Blade Components

All UI components live under `resources/views/components/activity/`:

- `page-header` — title, refresh, export dropdown.
- `kpi-cards` — five KPI cards reusing `x-admin.kpi-card`.
- `filter-bar` — search, category/status/module/user selects, date range, reset.
- `activity-feed` — feed container with desktop column headers.
- `activity-card` — single timeline row with icon, title, module, actor, timestamp, status, and detail trigger.
- `detail-drawer` — side panel with badges, description, related record, actor, technical details, and metadata.
- `pagination` — custom page controls.
- `empty-state` — contextual empty message.

## Styling

`resources/css/filament/admin/components/activity.css` provides the Activity Centre styles and is imported by `resources/css/filament/admin/theme.css`. It reuses the existing design-token variables and follows the Workspace Dashboard/Tasks/Notifications visual language.

## Data Flow

1. `AuditLog` and `LoginActivity` records are created by existing application events.
2. `ActivityPage` renders and calls `ActivityService` with the active filter set.
3. `ActivityService` fetches rows from both tables, normalises them, merges, sorts, filters, and paginates.
4. Blade components render the feed, KPIs, filters, pagination, and detail drawer.
5. Export actions stream data through `ReportExportService`.
