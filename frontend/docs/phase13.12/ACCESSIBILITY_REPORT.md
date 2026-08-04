# Phase 13.12 — Accessibility Report

## Semantics & ARIA

- View toggle buttons use `role="group"` with `aria-pressed` on each Table/Map button.
- Filter dropdowns use `aria-haspopup="listbox"` / `role="listbox"` with labelled checkboxes and radios.
- The map canvas is exposed as `role="img"` with a descriptive `aria-label` ("Proportional plot of branch coordinates") since it is a custom visualisation rather than a native image or interactive map widget.
- Each map pin is a real `<button>` with an `aria-label` naming the branch, so keyboard and screen-reader users can reach and activate every plotted branch, not just mouse users.
- The detail drawer uses `role="dialog"` with a labelled close button and click-outside/overlay dismissal.
- Table headers expose sortable columns via `<button>` elements with `aria-label="Sort by …"`.

## Keyboard Navigation

- All interactive elements (filters, view toggle, table sort buttons, map pins, drawer close) are native `<button>`/`<a>`/`<input>` elements, preserving default tab order and activation via Enter/Space.
- Map pin tooltips are also revealed on `:focus-visible`, not just `:hover`, so keyboard users see the same branch name/coordinate detail as mouse users.

## Motion & Contrast

- A `@media (prefers-reduced-motion: reduce)` block disables transitions on interactive elements (rows, filter triggers, view toggle, pagination buttons, map pins).
- Status badges and map pin colours reuse the shared semantic colour tokens (success/gray/info) already validated for contrast elsewhere in the admin theme.

## Known Follow-ups

- If a licensed map tile provider is introduced in a future phase, the coordinate-plot canvas should be replaced with a fully accessible map widget (e.g. Leaflet with `leaflet.markercluster` and keyboard-navigable markers) rather than layered on top of the current implementation.
