# Phase 13.3 — Tasks Accessibility Report

## Keyboard Navigation

- All filter triggers, sort buttons, and action menus are reachable via Tab.
- Drawer can be closed with the Escape key.
- Pagination buttons are focusable and actionable via keyboard.

## ARIA

- Task table is wrapped in a scrollable region with `aria-label="Tasks"`.
- Table headers use `scope="col"`.
- Drawer has `role="dialog"`, `aria-modal="true"`, and `aria-labelledby` pointing to the title.
- Sort buttons include `aria-label` describing the sort action.
- Action trigger has `aria-label="Task actions"`.
- Empty state CTAs are real buttons, not links styled as buttons.

## Focus States

- All interactive elements use visible focus outlines consistent with the design system.
- Filter triggers, buttons, and form inputs have `:focus-visible` styles.

## Screen Reader Support

- Status and priority badges include both icon and text labels.
- Assignee is presented with initials avatar and full name.
- Due dates and creation times are plain text.
- Required form fields are marked with an asterisk and `aria-label="required"`.

## Reduced Motion

- CSS transitions are disabled when `prefers-reduced-motion: reduce` is active.
- Alpine transitions are handled by the browser's reduced-motion preference via `x-transition` defaults.

## Color Contrast

- Semantic badge colors use the same palette as the dashboard, which meets WCAG AA for normal text.
- Text colors use `--text-heading` and `--text-muted` against `--surface-card` backgrounds.
