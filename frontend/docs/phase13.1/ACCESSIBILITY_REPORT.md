# Accessibility Report — Phase 13.1

## WCAG 2.2 AA Measures

- **Semantic headings:** `h1` for dashboard title, `h2` for section labels, `h3` for card titles.
- **ARIA labels:** Date-range selector has an associated `<label>` and `aria-label`.
- **Screen-reader sections:** KPI and content sections use `aria-labelledby` pointing to visually hidden headings.
- **Focus states:** All interactive elements use visible focus indicators via existing `--shadow-focus` token.
- **Color contrast:** Text uses VESTRA design tokens verified for contrast against card and sidebar surfaces.
- **Motion:** Widget fade-in animation respects `prefers-reduced-motion: reduce`.

## Keyboard Navigation

- Sidebar items are focusable and navigable via Tab/Arrow keys.
- Activity list items with URLs are focusable anchors.
- Date-range selector is reachable and operable via keyboard.

## Known Limitations

- The My Tasks and Calendar widgets are empty-state placeholders; full keyboard flows will be added when those modules are implemented.
