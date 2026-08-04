# Phase 13.16 — Feedback Workspace (Frontend / Blade)

## Blade Page View

`resources/views/filament/pages/customer-success/feedback.blade.php`

Renders the full CRM workspace using the `filament.layouts.crm` layout. Composed entirely from sub-components in `components/feedback/`.

## Component Tree

```
<x-feedback.page-header />           — Search bar + export dropdown
<x-feedback.kpi-cards />             — 6 KPI metric cards
<x-feedback.filter-bar />            — Status / Category / Priority / Date filters
<x-feedback.feedback-table />        — Sortable table header
  <x-feedback.feedback-row />        — One row per feedback item
    <x-feedback.status-badge />
    <x-feedback.category-badge />
    <x-feedback.priority-badge />
<x-feedback.pagination />            — Page controls with result count
<x-feedback.empty-state />           — Shown when no results
<x-feedback.detail-drawer />         — Slide-in detail panel
  <x-feedback.status-badge />
  <x-feedback.category-badge />
  <x-feedback.priority-badge />
```

## Components Reference

| Component | Props |
|---|---|
| `feedback.page-header` | `title`, `description`, `csvUrl`, `excelUrl`, `pdfUrl` |
| `feedback.kpi-cards` | `cards` (array) |
| `feedback.filter-bar` | `statusOptions`, `categoryOptions`, `priorityOptions` |
| `feedback.feedback-table` | `feedback` (paginator), `sortField`, `sortDirection` |
| `feedback.feedback-row` | `feedback` (model instance) |
| `feedback.detail-drawer` | `show` (bool), `feedback` (array\|null) |
| `feedback.status-badge` | `status` (string) |
| `feedback.category-badge` | `category` (string) |
| `feedback.priority-badge` | `priority` (string) |
| `feedback.pagination` | `paginator` |
| `feedback.empty-state` | `hasFilters` (bool) |

## CSS

File: `resources/css/filament/admin/components/feedback.css`

Imported via: `resources/css/filament/admin/theme.css` (one added line).

Namespaced under `.vestra-feedback__*` and `.vestra-feedback-detail__*`.

### Key CSS Blocks

- `.vestra-feedback__hero` — page header layout
- `.vestra-feedback__filter-bar` — horizontal filter strip
- `.vestra-feedback__table` — table with sortable columns
- `.vestra-feedback__row--unread` — highlighted row for unread items
- `.vestra-feedback__badge--{color}` — status/category/priority badges
- `.vestra-feedback-detail__*` — slide-in drawer panel

## Interactivity

All interactions use Livewire wire directives:

| Action | Trigger |
|---|---|
| Open drawer | `wire:click="openDetailDrawer(id)"` |
| Close drawer | `wire:click="closeDetailDrawer"` or Escape key |
| Mark In Progress | `wire:click="markInProgress(id)"` |
| Resolve | `wire:click="markResolved(id)"` with `wire:confirm` |
| Mark Read/Unread | `wire:click="markRead(id)"` / `wire:click="markUnread(id)"` |
| Filter changes | `wire:model.live` on all filter inputs |
| Sort | `wire:click="sortBy('field')"` |
| Export dropdown | Alpine.js `x-data="{ open: false }"` |

## Design Decisions

- Unread rows are visually highlighted with a light primary-blue background
- Category badges use semantically meaningful colours (Praise = green, Complaint = amber, Bug = red, Feature = blue, General = gray)
- No rating columns, no sentiment labels — categories only
- No "+ New Feedback" button — workspace is read/triage-only
