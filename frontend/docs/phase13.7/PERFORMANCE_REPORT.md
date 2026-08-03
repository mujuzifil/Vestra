# Phase 13.7 — Performance Report

## Query strategy

- Eager load: `items.product`, `assignedUser`, `user.companyProfile`
- `withCount('items')` for product overflow labels without N+1
- Pagination default 15 rows with query-string persistence
- Filters/search applied in SQL via model scopes
- KPI cards use aggregate queries (count/sum), not loaded collections

## Avoided anti-patterns

- No Filament table hydration overhead
- No per-row relationship queries in table (company industry uses eager path)
- Export reuses filtered query builder instead of loading unrelated relations

## Front-end assets

- Quotes CSS imported once via Filament Vite theme
- Alpine used only for lightweight dropdowns/drawers
- No additional chart or heavy JS dependencies for this page
