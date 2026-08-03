# Phase 13.6 — Accessibility Report

## Structure

- Page uses semantic `<section>` and `<table>` elements
- Table headers are `<th scope="col">`
- Table region has `role="region"` and `aria-label="Companies"`
- Search inputs have explicit `aria-label`
- Drawer panels use `role="dialog"`, `aria-modal="true"` and `aria-labelledby`
- Form labels are explicitly associated with inputs

## Keyboard / Focus

- Sort buttons are native `<button>` elements
- Action menus and filter dropdowns are keyboard-focusable
- Drawers close on `Escape` via Alpine.js
- Focus outlines use the design-system focus ring

## Motion

- `prefers-reduced-motion` media query disables transitions for users who request reduced motion
