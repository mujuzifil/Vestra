# Phase 7 — Accessibility Report

## Keyboard Navigation
- All links, buttons, search inputs, and filter inputs are keyboard accessible.
- Directory result cards contain focusable contact links.

## ARIA
- Sections use `aria-labelledby`.
- FAQ accordion buttons use `aria-expanded`.
- Directory search inputs have visible labels/placeholders.

## Heading Hierarchy
- Single `<h1>` in hero.
- Section titles use `<h2>`.
- Card titles use `<h3>`.

## Contrast & Focus
- High-contrast hero and CTA sections.
- Focus rings on interactive elements.

## Motion
- Scroll animations use `viewport={{ once: true }}`.
- No auto-playing media.
