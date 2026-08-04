# Phase 13.15 — Enquiries Workspace: Overview

## Summary

Phase 13.15 delivers an enterprise CRM workspace for managing `ContactMessage` records within the **Customer Success** navigation group. The workspace replaces the legacy Filament resource list with a dedicated Livewire-powered page that mirrors the architecture of other workspaces (Applications, Companies, Partners).

## Scope

| Area | Deliverable |
|------|-------------|
| Filament Page | `EnquiriesPage` (slug: `customer-success/enquiries`, sort: 2) |
| Admin Service | `EnquiryAdminService` — paginate, KPI, filter, export |
| Policy | `ContactMessagePolicy::export()` added |
| Legacy redirect | `ListContactMessages` redirects to `EnquiriesPage` |
| Export | `EnquiryExportController` (CSV / Excel / PDF) |
| Provider | `AdminPanelProvider` registers page + export route |
| Blade views | 10 components under `components/enquiries/` |
| CSS | `components/enquiries.css` imported in `theme.css` |
| Tests | `EnquiriesPageTest` (19 test methods) |
| Docs | 4 backend + 4 frontend markdown files |

## Architecture

```
EnquiriesPage (Filament\Pages\Page + WithPagination)
    │
    ├── EnquiryAdminService
    │     ├── paginateEnquiries()
    │     ├── getKpiCards()        → 5 cards (Total, New, In Progress, Resolved, Unassigned)
    │     ├── getDetail()
    │     ├── getFilterOptions()
    │     └── exportRows()
    │
    └── Blade view: filament/pages/customer-success/enquiries.blade.php
          ├── x-enquiries.page-header
          ├── x-enquiries.kpi-cards
          ├── x-enquiries.filter-bar
          ├── x-enquiries.enquiry-table  → x-enquiries.enquiry-row
          ├── x-enquiries.pagination
          └── x-enquiries.detail-drawer
```

## Navigation

- **Group**: Customer Success
- **Label**: Enquiries
- **Sort**: 2 (between any item with sort 1 and SupportPage sort 3)
- **Icon**: `heroicon-o-envelope`

## KPI Cards

| Card | Query |
|------|-------|
| Total | `ContactMessage::count()` |
| New | `status = new` |
| In Progress | `status = in_progress` |
| Resolved | `status = resolved` |
| Unassigned | `assigned_to IS NULL` |

No "Closed" or "Converted" KPIs — only the three statuses from `ContactStatus`.

## Design Principles

- Live DB only — no fake/seeded data in production views
- No "+ New Enquiry" button — `canCreate()` returns `false` on the resource
- No right analytics panel
- Drawer auto-marks enquiry as read on open
- Gate-protected at every mutation point
