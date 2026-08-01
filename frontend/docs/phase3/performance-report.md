# Phase 3 — Performance Report

## Build Metrics
- Command: `NODE_OPTIONS=--max-old-space-size=10240 npm run build`
- About route: `/about` — ~10 kB first load JS, ~220 kB total first load JS.
- Build completed successfully; all 51 pages prerendered.

## Image Optimisation
- Our Story image uses `next/image` with WebP source and explicit `sizes`.
- Below-fold images lazy loaded.

## Bundle Observations
- No new dependencies introduced.
- Reuses existing shared components and Framer Motion.

## Notes
- The standard `npm run build` with Turbopack requires increased memory in this environment (10 GB) due to project size. The build succeeds under that configuration.
