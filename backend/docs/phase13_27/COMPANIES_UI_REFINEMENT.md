# Phase 13.27 — Companies UI Refinement

## Heading

Removed `<x-filament-panels::page>` wrapper (Tasks/Activity pattern) and emptied `getHeading()` so only the custom hero title remains:

- **Companies**
- Manage and grow your company relationships.

## Import removed

Deleted Import button, import drawer Blade, `CompaniesPage::import()`, `CompanyService::importCompanies()`, and `CompanyProfilePolicy::import()`. Export (CSV/Excel/PDF) unchanged.

## Filter panel default

`showFilterPanel` defaults to `false`. Drawer opens only via `toggleFilterPanel`. Filters button uses `--active` when the panel is open **or** active filters exist.
