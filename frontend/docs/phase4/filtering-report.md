# Phase 4 — Filtering Report

## Filter Bar

| Filter | Type | Source |
|--------|------|--------|
| Search | Text input | Client-side match on name/description |
| Category | Select | `useCategories` API |
| Package Size | Select | Derived from all products' `specifications` |
| Industry | Select | Derived from static category-to-industries map |
| Availability | Select | `All` / `In Stock` (status or stock_quantity) |
| Sort | Select | Featured, Newest, Name A–Z, Name Z–A |

## Sort Options
- **Featured** — featured products first.
- **Newest** — `created_at` descending.
- **Name A–Z** — alphabetical ascending.
- **Name Z–A** — alphabetical descending.

## Memoization
- Filtered product list memoized with `useMemo`.
- Pagination derived from filtered list.
- Filter options for package sizes and industries memoized.

## URL Synchronisation
- Filter state synced to query params via `router.replace`.
- Pagination resets when filters change.

## Performance
- Filtering runs against the in-memory full product list.
- No price widgets or heavy UI components.
