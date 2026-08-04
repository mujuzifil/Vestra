# Phase 13.25 — UI Refinement Report

## Changes (no redesign)

1. **Create Task selects** — single chevron via `appearance: none` + CSS background SVG; padding aligned.
2. **Import removed** — header button and any Import affordance gone from Tasks views/CSS actions.
3. **Export** — functional dropdown wired to `TaskExportController` with filter-aware URLs.
4. **Empty state** — copy:
   - Title: `No tasks found`
   - Body: `Tasks will appear here once they are created. Create your first task to begin managing work.`
   - Primary: `Create Task` → `openCreateDrawer`
5. **Export menu CSS** — `.vestra-tasks__export-dropdown` / `__menu` / `__option` mirroring Staff workspace.

## Preserved

Workspace Design System tokens, hero header layout, KPI cards, filter bar, table, drawer structure.
