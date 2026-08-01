# Phase 7 — Performance Report

## Build Metrics
- `npm run lint` passed (0 errors).
- `npx tsc --noEmit` passed.
- `npm run build` passed.
- `/where-to-buy` route: 16.4 kB / 227 kB First Load JS.

## Static Generation
- `/where-to-buy` is statically prerendered.

## Optimisation Notes
- Directory and coverage data fetch client-side, avoiding build-time dependencies.
- Settings are fetched once via shared `useSettings` hook.
- Icons imported individually for tree shaking.
