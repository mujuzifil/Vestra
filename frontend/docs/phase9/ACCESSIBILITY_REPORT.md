# Phase 9 — Accessibility Report

## Keyboard Navigation

- All form inputs and selects are focusable with visible focus rings.
- Social cards and resource cards are reachable links/buttons.
- File upload is triggered via a visible button with keyboard support.

## Screen Readers

- Section headings use `aria-labelledby`.
- Form labels are explicitly associated.
- Error messages use `role="alert"` and `aria-describedby`.
- Decorative icons are hidden with `aria-hidden="true"`.

## Contrast

- Hero text uses white on a dark gradient with sufficient contrast.
- Form inputs use `--text-heading` and `--text-muted` tokens.
- CTA buttons maintain brand contrast.

## Motion

- Hover transitions are CSS-only and subtle.
- Reduced-motion preferences are respected through shared animation components.
