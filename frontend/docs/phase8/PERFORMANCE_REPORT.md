# Phase 8 — Performance Report

## Build Output

- `npm run build` completed successfully.
- `/blog` static page: 6.75 kB first load.
- `/blog/[slug]` dynamic page: 6.46 kB first load.

## Optimisations

- Article images use `next/image` with responsive `sizes` and lazy loading.
- Featured image is prioritised.
- API calls are debounced (300 ms) to avoid excessive requests while typing.
- Category cards use CSS-only hover states.
- Latest articles on the homepage fetch only three posts.

## Bundle

- No new heavy dependencies introduced.
- Lucide icons use the existing optimised import.
