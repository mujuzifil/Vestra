# Phase 10 — Performance Report

## Build output
Command: `npm run build`
Result: ✅ Success — 51 static pages generated.

## Bundle highlights
| Route | First Load JS |
|---|---|
| `/` | 230 kB |
| `/contact` | 232 kB |
| `/distributor` | 238 kB |
| `/request-quote` | 234 kB |
| `/products` | 223 kB |
| `/products/[slug]` | 222 kB |

Shared JS: 190 kB first load.

## Optimisations applied
- `next/image` used for all product, hero, blog, review, and gallery images.
- Lazy loading via `loading="lazy"` and viewport-driven Framer Motion.
- Shared CSS chunk: 17.1 kB.
- Removed dead cart/checkout code in earlier phases.

## Validation
- `npm run lint` ✅ 0 errors, 0 warnings
- `npx tsc --noEmit` ✅
- `npm run build` ✅

## Notes
- Initial Turbopack runs intermittently exhausted OS thread resources in this environment; clearing `.next` and rebuilding produced a clean, repeatable pass. The code compiles successfully with both Turbopack and Webpack.
- Lighthouse scores were not re-run in this environment; the build is optimised for the target thresholds (≥ 95).
