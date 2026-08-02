# Responsive Report — Phase 13.2

## Breakpoints Tested
- Mobile: `< 640px`
- Tablet: `640px` – `1023px`
- Laptop: `1024px` – `1279px`
- Desktop: `1280px+`

## Layout Behaviour

### KPI Cards
- 1 column on mobile
- 2 columns on small tablets
- 3 columns on laptops
- 5 columns on desktop

### Sales Overview + Recent Activity
- Stacked on mobile/tablet
- 2:1 split on laptop and desktop

### Tasks / Notifications / Calendar
- 1 column on mobile
- 2 columns on tablet
- 3 columns on desktop

### Sidebar
- Full width drawer on mobile/tablet (`< 1024px`).
- Collapsible sidebar on desktop.

### Topbar
- Global search hidden on mobile.
- User name/role hidden on mobile; avatar remains.

## Notes
- All grids use CSS `grid` with `gap` tokens.
- Reduced-motion media query disables transitions for accessibility.
