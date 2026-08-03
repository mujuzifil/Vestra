# Phase 13.7 — Responsive Report

## Breakpoints

| Viewport | Behaviour |
|----------|-----------|
| Desktop (≥1024px) | Full KPI grid (5), horizontal filters, wide table with horizontal scroll region |
| Laptop | Same composition; table scrolls within card |
| Tablet | KPI grid 2 columns; filters wrap; drawers remain full-height side panels |
| Mobile | KPI 1 column; stacked header actions; table scrolls horizontally; drawers overlay |

## Techniques

- Flex-wrap on hero and filter bar
- `vestra-quotes__table-wrap` as scroll region with `tabindex="0"`
- Drawer panels reuse Companies slide-over pattern
- Touch-friendly action hit targets (≥40px filter triggers)
