# Phase 13.3 — Tasks Workspace Design Report

## Design Authority

The Tasks page is inspired by the `Tasks.png` reference in the project root. It adapts the reference into the existing VESTRA Admin CRM design system (Phase 10/13) using the same typography, spacing, colors, shadows, and card language established by the Workspace Dashboard.

## Layout

The page follows the standard Workspace shell:

1. **Page Header** — Title, description, and primary actions (Import, Export, New Task).
2. **KPI Cards** — Four metric cards: Total Tasks, Completed, In Progress, Overdue.
3. **Task List Card** — Filter bar and custom data grid.
4. **Create/Edit Drawer** — Slide-out form for task management.

## Visual Hierarchy

- Page title uses `vestra-workspace__title` (H1).
- KPI cards match the dashboard KPI component with icon, value, and trend pill.
- Filter bar uses secondary buttons and compact dropdowns.
- Data grid uses a clean table with generous row padding and hover states.
- Status and priority use rounded badge components with semantic colors.

## Empty States

When no tasks exist or filters return no results, a premium empty state is displayed with:

- Branded icon container
- Clear heading
- Helpful description
- Contextual "Create task" CTA

## Responsive Behaviour

- Desktop: full table, inline filters, side drawer.
- Tablet: filters wrap, table scrolls horizontally.
- Mobile: header actions stack, filter bar stacks, drawer becomes full-width.
