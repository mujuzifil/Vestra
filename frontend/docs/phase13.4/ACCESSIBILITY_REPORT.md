# Accessibility Report — Phase 13.4

## Keyboard Navigation

- Notification cards are focusable and actionable via keyboard.
- Detail panel can be closed with the Escape key (Alpine.js `x-on:keydown.escape`).
- Quick action buttons have visible focus states.
- Filter dropdowns can be toggled and closed via click-outside.

## ARIA

- Feed region: `aria-label="Notifications"`.
- Sort buttons: `aria-label="Sort by ..."`.
- Pagination controls: `aria-label` on each button.
- Detail panel: `role="dialog"`, `aria-label="Notification details"`.
- Empty states: semantic headings.

## Screen Readers

- Notification title and message are plain text inside semantic elements.
- Unread state is communicated via `aria-label` and a visual dot.
- Badges use simple text content.

## Colour Contrast

All colours are drawn from the shared design system, which was already validated for WCAG AA contrast. Priority/category badges use the same palette as KPI cards and status badges.

## Focus States

- Search input: `box-shadow: var(--shadow-focus)`.
- Filter triggers: `focus-visible` outline.
- Cards: background colour change on focus.
- Action buttons: focus ring via shared utility.
