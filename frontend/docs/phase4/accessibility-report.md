# Phase 4 — Accessibility Report

## Heading Hierarchy
- Listing page: single `h1` from `PageHero`; sections use `h2`.
- Detail page: `h1` from `PageHero`; sections use `h2`.

## ARIA
- Filters wrapped in `aria-label` section.
- Search input has associated `<label>` (sr-only).
- Selects have visible labels and `id`/`htmlFor` association.
- Compare toggle uses `aria-pressed`.
- Pagination buttons use `aria-label` and `aria-current`.

## Keyboard Navigation
- All filters are native focusable controls.
- Product cards contain focusable links.
- Focus-visible ring defined globally.

## Motion
- Listing page respects `prefers-reduced-motion`.
- Detail page uses no motion (reduces complexity).

## Contrast
- Text on navy backgrounds uses high-contrast white.
- Green CTAs use white text.
- Body text uses `--text-body` on white surfaces.
