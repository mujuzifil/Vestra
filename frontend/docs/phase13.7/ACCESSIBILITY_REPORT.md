# Phase 13.7 — Accessibility Report

## Supported

- Semantic headings and section `aria-label`s
- Dialog roles / `aria-modal` on drawers
- Escape key closes detail and form drawers
- Sort buttons expose `aria-label`
- Checkboxes expose select labels including quote reference
- Focus-visible styles via shared focus utilities
- Colour is not the only status signal (icons + text labels)
- Print stylesheet hides chrome for detail content

## Keyboard

- Tab through search, filters, table controls and actions
- Menu triggers use Alpine open/close with outside-click
- Pagination buttons labelled previous / next / page N

## Known follow-ups

- Full screen-reader pass on production data recommended after deploy
- High-contrast verification against OS forced-colours mode
