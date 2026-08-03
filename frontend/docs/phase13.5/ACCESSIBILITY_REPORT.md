# Accessibility Report — Phase 13.5

## Keyboard & Focus

- All interactive filter triggers, reset, refresh, export, and detail buttons are real `<button>` elements.
- Search and date inputs use proper labels and focus states.
- Checkboxes and radio buttons inside dropdowns use native controls with visible focus.

## ARIA

- The feed container has `role="region"` and `aria-label="Activity timeline"`.
- The detail drawer has `role="dialog"` and `aria-label="Activity details"`.
- Pagination buttons include descriptive `aria-label` attributes.
- The export dropdown uses `role="menu"` / `role="menuitem"`.

## Screen Readers

- Empty state provides contextual headings and descriptions.
- Activity card titles are semantic headings.
- Status and category labels are rendered as text, not colour alone.
- Actor avatars include empty `alt` attributes because the adjacent text provides the name.

## Motion

- The detail drawer uses CSS transitions for open/close.
- Reduced-motion preferences are respected through the shared design tokens.

## Colour

- All status/category badge colours meet contrast requirements against their backgrounds using the established token palette.
