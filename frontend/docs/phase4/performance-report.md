# Phase 4 — Performance Report

## Build Metrics
- Command: `NODE_OPTIONS=--max-old-space-size=10240 npm run build`
- `/products` route: ~9.3 kB first load JS.
- `/products/[slug]` route: ~7.8 kB first load JS.
- Build succeeded; all 51 pages prerendered.

## Image Optimisation
- Product images use `next/image` with `object-contain` and explicit `sizes`.
- Lazy loading for below-fold images.

## Filtering Performance
- Full product list fetched once via `useProducts`.
- Filtering, sorting, and pagination computed via `useMemo`.
- No repeated API calls on filter changes.

## Bundle
- No new dependencies added.
- Removed reviews-related imports from product detail page, reducing detail bundle.
