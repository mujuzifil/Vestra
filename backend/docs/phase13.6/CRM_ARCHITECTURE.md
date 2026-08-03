# Phase 13.6 — Sales → Companies CRM Architecture

## Overview

The Sales → Companies workspace is a fully custom Filament/Livewire page that provides an enterprise CRM view over the existing `CompanyProfile` entity. It reuses the Workspace Design System established by Dashboard, Tasks, Notifications and Activity.

## Components

| Layer | Responsibility |
|-------|----------------|
| `App\Filament\Pages\Sales\CompaniesPage` | Livewire page, URL filters, drawers, export/import actions |
| `App\Services\Admin\CompanyService` | Pagination, KPIs, CRUD, export rows, CSV import |
| `App\Http\Controllers\Admin\CompanyExportController` | Dedicated authenticated export route for CSV/Excel/PDF |
| `App\Models\CompanyProfile` | Extended with `status`, `account_manager_id`, `region`, `notes` and direct relations |
| `App\Enums\CompanyStatus` | Prospect / Active / Inactive / Suspended with labels, icons and colours |
| Blade components under `resources/views/components/companies/` | Page header, KPI cards, filters, table, rows, drawers, form, pagination, empty state |

## Data Flow

1. Admin opens `/admin/sales/companies`.
2. `CompaniesPage` authorises `viewAny` via `CompanyProfilePolicy`.
3. `CompanyService::paginateCompanies` applies search, filters, sorting and eager loads `user` and `accountManager`.
4. KPI cards are computed from live counts and compared against the previous month where data exists.
5. Drawers are toggled via Livewire state and rendered by dedicated Blade components.
6. Exports stream through `CompanyExportController` using `ReportExportService`.
7. CSV imports match rows by `primary_contact_email` to existing users; unknown emails are skipped and reported.
