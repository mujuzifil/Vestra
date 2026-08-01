# Phase 5 — Accessibility Report

## Keyboard Navigation
- All interactive elements (links, buttons, form fields, FAQ accordion) are keyboard accessible.
- Form inputs use semantic `<label>` associations and focus rings.

## ARIA
- Hero, section headings, and FAQ use `aria-labelledby`.
- FAQ accordion buttons use `aria-expanded`.
- Form errors use `role="alert"` and `aria-describedby`.

## Heading Hierarchy
- Single `<h1>` in hero.
- Section titles use `<h2>`.
- Card titles use `<h3>`.

## Focus States
- Focus rings use `focus:ring-secondary-500`.
- Buttons and links have visible hover/focus transitions.

## Contrast
- White text on dark gradient hero meets WCAG contrast.
- Body text uses `text-text-heading` and `text-text-muted` from the design-token system.
- CTA buttons maintain high contrast.

## Motion
- Framer-motion animations respect the existing motion preferences defined in `styles/motion.css`.
- No auto-playing carousels or flashing content.
