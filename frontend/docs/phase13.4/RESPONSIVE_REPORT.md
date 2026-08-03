# Responsive Report — Phase 13.4

## Breakpoints

- Mobile: < 640px
- Tablet: 640px–1023px
- Desktop: ≥ 1024px

## Layout Behaviour

### Mobile (< 640px)

- Filter bar stacks vertically; search fills width.
- Filter dropdowns remain accessible.
- Notification feed hides column headers.
- Cards display icon, content, and actions; category/priority/time are hidden to reduce clutter.
- Detail panel opens full-width.

### Tablet (640px–1023px)

- KPI grid shows 2 columns.
- Filter bar wraps naturally.
- Cards show additional metadata columns where space allows.

### Desktop (≥ 1024px)

- KPI grid shows 4 columns.
- Feed displays full column headers.
- All metadata columns visible.
- Detail panel opens as a 460px side drawer.

## Touch Targets

- Action buttons: 32px × 32px minimum.
- Filter triggers: 40px height.
- Cards: full-row clickable.

## No Horizontal Scrolling

The feed uses a responsive grid and hides secondary columns on narrow screens, preventing horizontal overflow.
