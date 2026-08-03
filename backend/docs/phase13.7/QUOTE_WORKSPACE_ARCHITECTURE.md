# Phase 13.7 — Quote Workspace Architecture

## Overview

Sales → Quotes is a custom Filament/Livewire workspace page that provides an enterprise quote management view over existing `QuoteRequest` records. It reuses the Workspace Design System established by Dashboard, Tasks, Notifications, Activity and Companies.

Filament provides authentication, routing, authorization and CRUD backends only. The page does **not** use Filament tables, widgets or statistics.

## Components

| Layer | Responsibility |
|-------|----------------|
| `App\Filament\Pages\Sales\QuotesPage` | Livewire page, URL filters, drawers, status actions, bulk selection |
| `App\Services\Admin\QuoteAdminService` | Pagination, KPIs, detail payload, export rows, status/update helpers |
| `App\Http\Controllers\Admin\QuoteExportController` | Authenticated CSV/Excel/PDF export |
| `App\Models\QuoteRequest` | Search/filter scopes, attachment helpers, priority helpers |
| `App\Enums\QuoteRequestStatus` | pending / contacted / quoted / approved / declined / closed |
| `App\Enums\QuoteRequestPriority` | low / medium / high (presentation labels/colours) |
| Blade components under `resources/views/components/quotes/` | Header, KPIs, filters, table, row, badges, drawers, empty state, pagination |

## Data Flow

1. Admin opens `/sales/quotes`.
2. `QuotesPage` authorises `viewAny` via `QuoteRequestPolicy`.
3. `QuoteAdminService::paginateQuotes` applies search, filters, sorting and eager-loads `items.product`, `assignedUser`, `user.companyProfile`.
4. KPI cards are computed from live counts/sums; trends only when prior-period data exists.
5. Drawers are toggled via Livewire state and render live relationships or empty states.
6. Exports stream through `QuoteExportController` using `ReportExportService`.

## Future CRM Readiness

The service/page split and drawer detail payload are structured so future multi-stage approvals, revisions and opportunity conversion can plug in without rewriting the list workspace. Those features are **not** implemented in this phase.
