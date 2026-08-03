# Performance Report — Phase 13.4

## Backend

### Query Optimisation

- `NotificationService::baseQuery()` scopes to `Auth::user()->notifications()`.
- Eager loading of `notifiable` relation is declared.
- Pagination limits result sets.
- Filters execute as JSON path queries on the indexed `data` column.
- KPI queries are separate but each uses a single aggregate query.

### Caching

No additional caching layer was added. Future optimisation could cache unread count for the header badge. For now, the query is fast because it uses the existing `(notifiable_type, notifiable_id, read_at)` index.

## Frontend

### CSS

Notification styles are bundled into `theme.css`. No additional JavaScript is loaded for this page beyond the shared app bundle.

### Alpine.js

- Detail panel uses Alpine.js transitions.
- Filter dropdowns use local `x-data` state.
- No polling or expensive re-renders.

### Recommendations

1. Add unread-count caching if header badge queries become slow.
2. Consider adding database columns for `category`, `priority`, `type` if notification volume grows and JSON queries become a bottleneck.
3. Lazy-load the detail panel content if payloads grow large.
