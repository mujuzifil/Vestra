# Accessibility Report — Phase 12A.2

## Keyboard Navigation

- Sidebar links are focusable and visible.
- Mobile drawer toggle is a `<button>` with `aria-label`.
- All form inputs retain focus states and labels.
- Back links are keyboard-accessible `Link` components.

## ARIA

- Active sidebar links announce `aria-current="page"`.
- Mobile drawer backdrop uses `aria-hidden="true"`.
- Icon-only buttons include `aria-label` or are accompanied by visible text.

## Heading Hierarchy

- Each page uses `PageHero` which renders an `<h1>`.
- Section headings use `<h2>` and card titles use appropriate heading levels.
- No skipped heading levels observed.

## Contrast

- Text uses established tokens: `text-text-heading` on `bg-surface-card`.
- Primary actions use `bg-secondary-600 text-white`.
- Status badges use color combinations from the existing design system.

## Focus States

- Inputs use `focus:border-secondary-500 focus:ring-1 focus:ring-secondary-500`.
- Buttons and links use visible hover/focus backgrounds.

## Reduced Motion

- Animations rely on CSS transition utilities (`transition-colors-base`, `transition-all-base`).
- No auto-playing motion was introduced.

## Screen Reader Considerations

- Empty states provide descriptive text rather than silence.
- Activity timeline uses icons with no hidden text; descriptions are explicit.
- Notification counts are rendered as plain text values.

## Known Limitations

- The Security page still contains a placeholder session entry (`mockSessions`) with hardcoded local IP data. This is clearly marked as placeholder data.
- Some business activity summary cards display `0` because backend APIs do not yet exist; this is honest placeholder behavior.
