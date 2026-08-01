# Phase 6 — Accessibility Report

## Keyboard Navigation
- All form inputs, buttons, FAQ accordion, and resource links are keyboard accessible.
- Product rows can be added/removed via keyboard-triggered buttons.

## ARIA
- Sections use `aria-labelledby`.
- FAQ accordion buttons use `aria-expanded`.
- Form errors use `role="alert"`.

## Heading Hierarchy
- Single `<h1>` in hero.
- Section titles use `<h2>`.
- Card and subsection titles use `<h3>` and `<h4>`.

## Focus & Contrast
- Focus rings and hover states use existing design tokens.
- Hero and CTA sections maintain high contrast.

## Motion
- Scroll animations use `viewport={{ once: true }}`.
- No auto-playing media or flashing content.
