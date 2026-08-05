# Phase 13.36 — Roles Management Dynamic RBAC

## Summary

Roles administration is a dynamic RBAC system: permissions and modules are discovered from the live Filament admin panel, Create/Edit Role matches `new_role.png` (without Role Color / Role Summary), View Role exposes full detail + actions, and permission checks enforce access on both frontend (nav/actions) and backend (policies/API).

## Delivered

- `RoleFormPage` — Role Information, Status & Notes, searchable/collapsible permission tree
- `PermissionDiscoveryService` reused for Roles (no hardcoded module lists)
- Expanded `RoleAdminService` — CRUD, status, duplicate, assign/remove users, audit
- Role View drawer — comparison, assigned users, audit, gated actions
- KPI cards centered equal-width grid
- Policies use discovered permissions (`products.view`, `blog.publish`, `staff.delete`, …) with Super Admin bypass and legacy admin fallback
- Unauthorized page at `/unauthorized`
- Admin API: roles CRUD, status, users, audit, permission tree
- Docs + tests under `backend/docs/phase13_36/`

## Non-goals

- No production/staging deployment
- No feature-flag changes
