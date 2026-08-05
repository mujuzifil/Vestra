# Phase 13.26 — Global Search Fix

## Implementation

| Piece | Role |
|-------|------|
| `WorkspaceSearchService` | Permission-aware search across Companies, Quotes, Applications, Partners, Products, Categories, Blog, Media, Staff, Roles, Tasks, Activities, Support, Enquiries |
| `GlobalSearchCommandPalette` | Livewire UI; opens from header; empty state for short/no matches |
| CRM layout `@livewire` | Mounts palette on every CRM page |
| Header search input | Focus/click/Enter → `open-command-palette` |
| Panel `globalSearch(false)` | Disables Filament resource global search |

## Behaviour

- Minimum 2 characters.
- Results link into workspace pages with `?search=` where supported.
- Empty state: “No results found”.
- Non-admin receives no groups (gates deny).
- Provider exceptions are logged and skipped — palette still returns 200.

## Tests

`WorkspaceSearchTest` covers matches, short queries, empty results, Livewire palette success, and non-admin empty results.
