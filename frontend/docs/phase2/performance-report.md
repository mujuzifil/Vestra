# Phase 2 — Performance Report

## Build Metrics
- Command: `npm run build` (Next.js 15 + Turbopack)
- Homepage static route: `/` — 13 kB first load JS, 223 kB total first load JS.
- Build completed successfully; all 51 pages prerendered.

## Image Optimisation
- Hero image uses `next/image` with WebP source (`home-page-image.webp`) and priority loading.
- Below-fold section images use lazy loading.
- All `next/image` instances include explicit `sizes` attributes.

## Font Loading
- Poppins is loaded via `next/font/google` with `display: swap` and limited weight subset.

## Motion
- Animations use transform and opacity only (GPU-friendly).
- `prefers-reduced-motion` is respected to avoid unnecessary work.

## Bundle Observations
- Framer Motion and React Query are already part of the bundle; new sections reuse these dependencies.
- No new heavy dependencies introduced.

## Recommendations
- Replace placeholder article images with optimised WebP thumbnails once content is ready.
- Implement `next/script` for analytics to load after interactive.
