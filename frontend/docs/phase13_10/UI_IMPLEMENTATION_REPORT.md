# Phase 13.10 — Applications Workspace: Frontend/UI Implementation Report

> Note: The VESTRA admin panel UI is server-rendered via Filament/Livewire/Blade
> (`backend/resources/views/...`, `backend/resources/css/filament/admin/...`). There is no
> separate SPA frontend for this workspace; this document records the frontend-facing
> (visual/UX/component) implementation details for the `frontend/docs` mirror required by the
> phase brief.

## Visual structure

The Applications workspace follows the same visual language as the existing Quotes and Companies
CRM workspaces:

1. **Page header** — title "Applications", description, and a search input, plus an export
   dropdown (CSV / Excel / PDF).
2. **KPI row** — six cards in a responsive grid (`vestra-kpi-grid--6`): Total, Pending, Under
   Review, Information Requested, Approved, Rejected. Each card shows the live count and a
   month-over-month trend indicator (or "No comparison available" when there is no prior-month
   baseline).
3. **Filter bar** — status, priority, country, region, assigned administrator, and date range
   filters, each rendered as multi-select/select inputs bound to Livewire properties. Includes a
   "Reset filters" action, only shown when at least one filter is active.
4. **Application table** — sortable columns (company, status, priority, country/region, assignee,
   submitted date), row checkboxes for bulk selection, and per-row quick actions (view, approve,
   reject) using `status-badge` and `priority-badge` components for visual state.
5. **Empty state** — shown when no applications match the current filters, with a friendly message
   and a "Reset filters" call to action when filters are active.
6. **Pagination** — standard prev/next + page-size control, consistent with Quotes/Companies.
7. **Detail drawer** — slide-over panel showing full application details (business info, contact
   info, geography, products of interest, existing customer flag, internal notes, documents,
   assignee) plus quick Approve/Reject actions and, if already approved, a link/reference to the
   resulting distributor account.

## Component inventory

`backend/resources/views/components/applications/`

| Component | Responsibility |
|---|---|
| `page-header.blade.php` | Title, description, search input, export dropdown |
| `kpi-cards.blade.php` | 6-card live KPI grid |
| `status-badge.blade.php` | Colored badge + icon per `DistributorStatus` |
| `priority-badge.blade.php` | Colored badge per `Priority` |
| `filter-bar.blade.php` | All filter controls + active-filter reset |
| `application-table.blade.php` | Table shell, header sorting, select-all checkbox |
| `application-row.blade.php` | Single row rendering + row actions |
| `pagination.blade.php` | Page navigation controls |
| `empty-state.blade.php` | No-results state |
| `detail-drawer.blade.php` | Slide-over detail view + quick actions |

## Styling

`backend/resources/css/filament/admin/components/applications.css`

- BEM naming: `.vestra-applications__header`, `.vestra-applications__kpi-grid`,
  `.vestra-applications__table`, `.vestra-applications__row`, `.vestra-applications__drawer`, etc.
- Reuses design tokens (`--surface-card`, `--text-heading`, `--space-*`, `--radius-*`) already
  defined in `backend/resources/css/filament/admin/tokens/*`, keeping visual consistency with the
  rest of the admin panel without introducing new hardcoded colors/spacing.
- Responsive: KPI grid collapses from 6 columns down to 2/1 columns on narrower viewports, matching
  the pattern used by `quotes.css` and `companies.css`.

## Accessibility

- Status/priority badges pair color with text labels and icons (not color alone) to convey state.
- Table header sort controls are actual buttons with `aria-sort`-equivalent visual indicators.
- Detail drawer traps focus within the panel while open and is dismissible via a close button and
  backdrop click, consistent with existing Quotes/Companies drawers.
