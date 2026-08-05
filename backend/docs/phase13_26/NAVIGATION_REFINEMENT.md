# Phase 13.26 — Navigation Refinement

## Changes

1. **Removed hamburger (`heroicon-o-bars-3`)** from the CRM header.
2. **Removed header collapse control** — desktop collapse no longer lives in the page header.
3. **Sidebar owns collapse/expand** — footer control with double-chevron; rotates when collapsed; label switches Expand/Collapse.
4. **Collapsed sidebar** shows a compact logo mark; expanded shows full brand lockup.
5. **Mobile open** uses a chevron control (not a hamburger) so the sidebar can still open on small screens.
6. **Notifications** workspace page removed from navigation and codebase registration.
7. Collapse state continues to persist via `localStorage` key `vestra-sidebar-collapsed`.

## Files

- `resources/views/components/admin/header.blade.php`
- `resources/views/components/admin/sidebar.blade.php`
- `resources/views/filament/layouts/crm.blade.php`
- `resources/css/filament/admin/components/crm-shell.css`
- `app/Providers/Filament/AdminPanelProvider.php`
