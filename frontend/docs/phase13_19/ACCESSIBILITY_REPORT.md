# Phase 13.19 — Accessibility Report

## Keyboard / focus

- Detail drawer closes on Escape (`@keydown.escape.window`)
- Filter dropdowns use Alpine `@click.outside`
- Sort controls are native `<button>` elements with `aria-label`
- Export menu uses `aria-haspopup` / `aria-expanded`

## Semantics

- Stock table wrapped in `role="region"` with label
- Drawer uses `role="dialog"` + `aria-modal="true"`
- Overlay click dismisses drawer
- Empty states provide reset affordance when filters are active

## Contrast / content

- Status badges use semantic colour tokens from the design system
- Numeric columns use tabular nums for readability
- No colour-only status: badges include text labels (In Stock / Low Stock / Out of Stock)

## Known constraints

- Product thumbnails are decorative when missing (icon fallback)
- Export links open download routes (admin-gated)
