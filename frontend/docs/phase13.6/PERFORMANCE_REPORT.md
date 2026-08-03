# Phase 13.6 — Performance Report

## Optimisations

- `CompanyService::paginateCompanies` eager-loads `user` and `accountManager`
- KPI cards are computed with simple aggregate queries (no N+1)
- Detail drawer loads relationships on demand
- Recent quotes, tickets, documents and activity are limited to small subsets
- Export queries reuse the filtered builder to avoid duplicating logic

## Potential Future Improvements

- Add `withCount` for open quotes and active tickets if the table grows large
- Cache KPI cards for a short TTL (currently computed on each request)
- Lazy-load detail drawer data only when a company is selected

## Bundle

- CSS is bundled via Vite and the existing Filament admin theme
- No additional JavaScript dependencies introduced
