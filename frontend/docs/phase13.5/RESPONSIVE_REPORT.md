# Responsive Report — Phase 13.5

## Breakpoints

The Activity Centre CSS uses the same breakpoint strategy as the rest of the admin theme:

- `640px` — small tablets
- `768px` — tablets
- `1024px` — desktop
- `1280px` — large desktop

## KPI Grid

- Mobile: single column.
- ≥640px: two columns.
- ≥1024px: five columns.

This matches the reference image’s five-card top row while remaining readable on smaller screens.

## Filter Bar

- Search input takes available width up to `360px`.
- Filter dropdowns wrap onto multiple rows on narrow screens.
- Reset button aligns to the right when space allows.

## Feed

- The desktop column header is hidden below `768px`.
- Activity cards stack vertically with the timeline connector visible at all sizes.
- Card metadata (module, user, time) wraps on narrow screens.
- The View details button remains accessible at all sizes.

## Detail Drawer

- Panel width is `100%` with a `480px` maximum, so it fills the viewport on mobile and uses a fixed width on desktop.
- Overlay click closes the drawer.

## Pagination

- Info text and controls wrap with `space-between` alignment.
- Controls remain horizontally scrollable if they exceed the viewport.
