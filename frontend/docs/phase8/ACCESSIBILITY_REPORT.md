# Phase 8 — Accessibility Report

## keyboard Navigation

- All category filter buttons are focusable and toggle with `Enter`.
- Search input and dropdowns use semantic labels.
- FAQ accordion buttons expose `aria-expanded`.
- Article cards are reachable as link elements.

## Screen Readers

- Section headings use descriptive `id`s referenced by `aria-labelledby`.
- Hidden decorative icons use `aria-hidden="true"`.
- Live result count announces the number of articles found.

## Contrast

- Gradient hero text uses white on dark blue/green gradient with sufficient contrast.
- Body text uses `--text-muted` and `--text-heading` tokens that meet WCAG ratios on white surfaces.

## Motion

- `prefers-reduced-motion` is respected through the shared `AnimatedItem` component.
- Hover elevations are CSS-only and subtle.
