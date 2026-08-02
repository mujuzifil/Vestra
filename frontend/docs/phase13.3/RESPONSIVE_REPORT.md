# Phase 13.3 — Tasks Responsive Report

## Breakpoints

| Breakpoint | Width | Behaviour |
|------------|-------|-----------|
| Mobile | < 640px | Single column, stacked header actions, full-width drawer |
| Tablet | 640px – 1023px | Two-column KPI grid, wrapped filters, horizontal table scroll |
| Desktop | 1024px – 1279px | Four-column KPI grid, inline filters, side drawer |
| Large desktop | ≥ 1280px | Max content width 1600px, generous whitespace |

## Mobile Adaptations

- Header actions (Import, Export, New Task) become full-width stacked buttons.
- Filter bar becomes a vertical stack with full-width triggers.
- Task table is wrapped in a horizontally scrolling container.
- Pagination controls center below the table.
- Drawer occupies the full viewport width.

## Tablet Adaptations

- KPI grid shows two cards per row.
- Filters wrap to multiple rows.
- Table remains in a scrollable container to preserve columns.

## Desktop Adaptations

- KPI grid shows four cards per row.
- Filters display inline with the search input.
- Drawer is fixed to the right at 480px width.
- Sidebar collapse state is respected.

## Testing Notes

Manual responsive testing should verify:

- No horizontal overflow on any breakpoint.
- Drawer can be opened and closed on mobile.
- Filter dropdowns are accessible without clipping.
- Table rows remain readable at all widths.
