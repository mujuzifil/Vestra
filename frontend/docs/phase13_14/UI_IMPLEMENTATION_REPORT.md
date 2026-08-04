# Phase 13.14 — Support Workspace Frontend Notes

## Admin Panel (Filament/Livewire)
The Support workspace is rendered inside the Filament admin panel using server-side Livewire components. There is no separate Vue/React frontend involvement for this phase.

## Blade Component Hierarchy
```
filament/pages/customer-success/support.blade.php
  └─ x-support.page-header          (title, search, export)
  └─ x-support.kpi-cards            (metric cards)
  └─ x-support.filter-bar           (status/priority/type/assignee/date)
  └─ x-support.ticket-table
       └─ x-support.ticket-row      (per row)
  └─ x-support.pagination
  └─ x-support.empty-state
  └─ x-support.detail-drawer
       └─ x-support.status-badge
       └─ x-support.priority-badge
```

## CSS Architecture
File: `resources/css/filament/admin/components/support.css`
Namespace: `.vestra-support__*` (BEM)

Key classes:
- `.vestra-support__badge--open/in-progress/resolved/closed` — status colours
- `.vestra-support__badge--priority-low/medium/high/urgent` — priority colours
- `.vestra-support-detail__reply--staff` — blue-left-border for staff replies
- `.vestra-support-detail__reply--internal` — amber for internal notes

## User Interactions
- Search: `wire:model.live.debounce.300ms` on search input
- Filters: multi-checkbox (status, priority, type); radio (assignee); date pickers
- Sort: click column headers → `wire:click="sortBy('field')"` with direction toggle
- Row click / View Details → opens slide-in drawer
- Drawer: status dropdown + Save button, reply textarea with internal-note checkbox, Send Reply
- Export: CSV / Excel / PDF via authenticated GET routes

## Accessibility
- All interactive elements have `aria-label`
- Table uses `scope="col"` on headers
- Drawer has `role="dialog"` and `aria-modal="true"`, Escape key closes
- Export menu uses `role="menu"` / `role="menuitem"`
