# Phase 13.18 — Categories: Design Report

## Visual Direction

Matches the established Workspace Design System (Companies / Feedback / Applications):

- CRM shell layout (`filament.layouts.crm`)
- KPI strip above a single table card
- Slide-in detail drawer (no right analytics column)
- Design tokens from admin theme (`--surface-*`, `--text-*`, `--primary-*`)

## Layout

| Region | Contents |
|--------|----------|
| Hero | Title, short description, search, Refresh, Export, Add Category (gated) |
| KPIs | Total / Active / With products / Empty |
| Table card | Filter bar + sortable table + pagination |
| Drawer | Category info, description, assigned products |

## Omissions (integrity)

- No parent/child tree chrome
- No donut / trend / activity side panel
- No fabricated “↑ % vs last 30 days” KPI trends

## Responsive Behavior

- Hero and filter bar wrap on narrow viewports
- Table scrolls horizontally inside `.vestra-categories__table-wrap`
- Drawer is full-width up to `max-width: 520px`
