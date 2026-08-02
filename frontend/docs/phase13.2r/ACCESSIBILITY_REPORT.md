# Accessibility Report — Phase 13.2R

## Keyboard
- Sidebar links are native `<a>` elements with visible focus states.
- Header buttons have focus-visible rings.
- Search input has a focus ring.

## ARIA
- Sidebar nav uses `aria-label="Main"`.
- Active page uses `aria-current="page"`.
- Sidebar group collapse button uses `aria-expanded`.
- Dashboard sections use `aria-label`.

## Focus
- Focus ring uses `--shadow-focus` (green glow).
- Header search focus transitions to card background.

## Contrast
- Sidebar white text on dark navy exceeds 4.5:1.
- KPI values use dark heading text on white cards.
- Trend badges use darker text on light backgrounds.

## Motion
- `prefers-reduced-motion` disables hover lifts and sidebar transitions.
