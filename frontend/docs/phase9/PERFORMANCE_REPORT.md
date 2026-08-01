# Phase 9 — Performance Report

## Build Output

- `npm run build` completed successfully.
- `/contact` static page: 18.8 kB first load.

## Optimisations

- Google Maps iframe uses `loading="lazy"` and is placed below the fold.
- Social icons use the existing optimised Lucide import.
- Form file uploads are client-side only and sent once on submission.
- No new heavy dependencies introduced.

## Bundle

- Contact page shares the existing component and utility chunks.
