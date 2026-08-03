# Phase 13.6 — API / Route Mapping

## Filament Page

| Route | Name |
|-------|------|
| `/admin/sales/companies` | `filament.admin.pages.sales.companies` |

## Export Route

| Route | Name | Handler |
|-------|------|---------|
| `/admin/sales/companies/export` | `filament.admin.sales.companies.export` | `CompanyExportController` |

## Livewire Actions

| Action | Purpose |
|--------|---------|
| `openDetailDrawer(int $id)` | Load company relationships and activity |
| `openCreateDrawer()` | Open blank company form |
| `openEditDrawer(int $id)` | Open pre-filled company form |
| `saveCompany()` | Create or update via `CompanyService` |
| `deleteCompany(int $id)` | Delete profile and log audit event |
| `createSupportTicket()` | Create a `SupportTicket` for the company user |
| `export(string $format)` | Redirect to export route with current filters |
| `import()` | Process uploaded CSV via `CompanyService` |
| `sortBy(string $field)` | Toggle sort field/direction |
| `resetFilters()` | Clear all filters and sorting |

## Quick Action Links

- Create Quote → `/quote-requests/create?user_id={userId}`
- View Activity → `/workspace/activity?user={userId}`
