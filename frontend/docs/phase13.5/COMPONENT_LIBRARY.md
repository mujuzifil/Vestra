# Component Library — Phase 13.5

## Backend Blade Components

All components are located in `backend/resources/views/components/activity/`.

### `page-header`

Props: `title`, `description`.

Renders the Workspace hero section with a Refresh button and an Export dropdown (CSV, Excel, PDF).

### `kpi-cards`

Props: `cards`.

Wraps `x-admin.kpi-card` in a five-column grid. Cards display icon, title, value, and subtitle; no fabricated trend data.

### `filter-bar`

Props: `categoryOptions`, `statusOptions`, `moduleOptions`, `userOptions`, `selectedIds`.

Provides:

- Search input with debounced `wire:model.live`.
- Category, status, and module checkbox dropdowns.
- User single-select radio dropdown.
- Date-from / date-until inputs.
- Reset filters button.
- Bulk selection bar when rows are selected.

### `activity-feed`

Props: `activities`, `selectedIds`.

Container with desktop column headers (Activity, Module, User, Time, Details) and a timeline wrapper around activity cards.

### `activity-card`

Props: `activity`, `selected`.

Single timeline row containing:

- Selection checkbox.
- Timeline marker and connecting line.
- Icon wrapper coloured by activity status.
- Title, description, category badge.
- Module, actor (avatar + name), and relative timestamp.
- Status badge and View details button.

### `detail-drawer`

Props: `show`, `activity`.

Right-hand overlay panel showing:

- Title, source, and relative time.
- Category, status, and module badges.
- Description.
- Related record with link when available.
- Actor block (avatar, name, email).
- Technical details list (date, IP, user agent, device, browser, OS, location).
- JSON metadata block when present.

### `pagination`

Props: `paginator`.

Custom pagination using `previousPage`, `gotoPage`, and `nextPage` Livewire methods.

### `empty-state`

Props: `hasFilters`.

Contextual empty message: "No activity yet" or "No activities found" with supporting description.

## CSS

`backend/resources/css/filament/admin/components/activity.css` defines all component styles using the shared design-token variables.
