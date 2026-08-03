# Performance Report — Phase 13.5

## Backend

### Query Strategy

- `AuditLog` rows are fetched with `with(['user', 'subject'])`.
- `LoginActivity` rows are fetched with `with('user')`.
- Date and user filters are applied at the SQL level.
- Search filters use SQL `LIKE` on indexed or low-cardinality columns.
- Category, status, and module filters are applied in PHP after normalisation because they depend on derived mappings.

### Pagination

The merged collection is sliced in PHP and wrapped in a `LengthAwarePaginator`. This keeps the implementation simple across the heterogeneous tables while still providing real pagination metadata.

### KPI Cards

KPIs are computed from the same filtered collection used for the feed, so no additional database queries are required.

### Exports

`forExport()` returns arrays only; streaming is handled by `ReportExportService`, keeping memory usage proportional to the filtered result set.

## Frontend

- CSS is bundled with Vite and tree-shaken via Tailwind CSS v4.
- No new JavaScript dependencies were added.
- The timeline uses CSS-only connector lines (no JS animation).
- Detail drawer visibility is controlled by Alpine.js through `x-show`/`x-transition`.

## Build Sizes

Backend Vite build produced a `theme-*.css` file of ~217 kB (gzipped ~32 kB). The Activity Centre CSS contribution is a small incremental addition to this bundle.
