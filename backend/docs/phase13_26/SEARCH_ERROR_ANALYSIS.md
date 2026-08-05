# Phase 13.26 — Search Error Analysis

## Symptom

Workspace header search produced a **500 Server Error**.

## Root cause

1. Filament’s built-in global search remained enabled while many discovered Resources still expose `getGloballySearchableAttributes()` against modules that are no longer the CRM presentation layer (Customers/Orders/etc.). Searching those paths could throw (missing routes, awkward queries, or obsolete assumptions).
2. The custom header input was not wired to a safe CRM search path. The leftover `GlobalSearchCommandPalette` still built **demo** result URLs with `route('filament.admin.resources.orders.edit', …)` and similar — those routes/resources are not part of the redesigned workspace and throw `RouteNotFoundException` when evaluated.

## Approach

Do not swallow exceptions at the HTTP boundary without fixing the cause:

- Disable Filament panel global search: `->globalSearch(false)`.
- Replace placeholder search with `WorkspaceSearchService` over live CRM entities only.
- Catch provider-level failures inside the service (log + skip group) so one broken module cannot take down the whole palette.
- Wire the header search field to open the Livewire command palette mounted in the CRM layout.
