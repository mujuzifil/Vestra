# Responsive Report — Phase 13.2R

## Breakpoints
- Mobile: `< 768px`
- Tablet: `768px` – `1023px`
- Desktop: `1024px+`

## Behaviour

### Sidebar
- Hidden off-canvas on mobile; toggled via header menu button.
- Fixed visible sidebar on desktop.

### Header
- Search hidden on mobile.
- User name hidden on mobile; avatar only.
- Date selector hidden below laptop.

### KPI Grid
- 1 column mobile
- 2 columns small tablet
- 3 columns large tablet/laptop
- 5 columns desktop

### Chart + Activity
- Stacked mobile/tablet
- 2:1 split desktop

### Tasks / Notifications / Calendar
- 1 column mobile
- 2 columns tablet
- 3 columns desktop

## Notes
- All grids use CSS Grid with tokenised gaps.
- Reduced motion disables transitions.
