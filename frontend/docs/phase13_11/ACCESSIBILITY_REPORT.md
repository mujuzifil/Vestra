# Phase 13.11 — Accessibility Report

## Semantics

- Table uses `<table>`/`<thead>`/`<tbody>` with `scope="col"` headers and an `aria-label="Active partners"` region wrapper.
- Sort buttons expose `aria-label="Sort by {Column}"` and reflect direction via a chevron icon (visual only — see below for the text-based fallback consideration).
- Filter triggers use `aria-haspopup="listbox"` and dropdown containers use `role="listbox"`.
- Pagination nav uses `aria-label="Partner pagination"` with per-button `aria-label="Go to page N"`, `"Previous page"`, `"Next page"`, and `aria-current="page"` on the active page.
- The detail drawer uses `role="dialog"`, `aria-modal="true"`, `aria-label="Partner details"`, and closes on `Escape` (`@keydown.escape.window`).

## Keyboard interaction

- All actionable elements (search input, filter triggers, sort buttons, row action menu, pagination controls, drawer close) are native `<button>`/`<input>`/`<a>` elements, so they are reachable and operable via keyboard by default (no custom `tabindex` traps introduced).
- Dropdowns (filters, row action menu, export menu) close on outside click (`@click.outside`) and the detail drawer additionally closes on `Escape`.

## Colour contrast

Status badges (Active = success green, Suspended = danger red) and the credit-utilization bar (success/warning/danger) reuse the existing Filament admin colour tokens already validated for contrast in Phase 13.6/13.7.

## Screen reader considerations

- Empty states and loading states render descriptive text (`No active partners yet`, `No partners found`) rather than relying on icon-only cues.
- Avatar-style initials (account manager, partner avatar in the drawer) are decorative; the adjacent text label carries the actual name, so no additional `alt`/`aria-hidden` handling was required beyond what the existing Quotes/Companies patterns already do.
