# Accessibility Report — Phase 13.2

## Keyboard Navigation
- Sidebar items and groups are native buttons/links and remain keyboard focusable.
- Global search input has visible focus ring.
- User menu and notification triggers have focus-visible outlines.

## Focus Indicators
- Focus ring uses `--shadow-focus` (green glow) across inputs, buttons, and triggers.
- Sidebar focus states use background colour change.

## ARIA
- Dashboard sections use `aria-labelledby` with visually hidden headings.
- Recent activity list uses `role="list"` and `role="listitem"`.
- Date range selector has a visible label and `aria-label`.

## Colour Contrast
- Sidebar text uses white/white-muted on dark navy background; contrast ratios exceed 4.5:1.
- KPI values use dark heading text on white cards.
- Trend badges use darker shades on light backgrounds.

## Motion
- `prefers-reduced-motion` disables widget fade-in and sidebar transitions.

## Remaining Notes
- The visual redesign did not introduce new interactive controls; existing Filament accessibility semantics are preserved.
