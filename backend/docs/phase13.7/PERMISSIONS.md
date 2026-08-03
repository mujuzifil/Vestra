# Phase 13.7 — Permissions

`App\Policies\QuoteRequestPolicy` gates access to the Quotes workspace.

## Policy Matrix

| Ability | Admin | Customer |
|---------|-------|----------|
| `viewAny` | Yes | No |
| `view` | Yes | No (admin workspace) |
| `create` | No | No |
| `update` | Yes | No |
| `delete` | Yes | No |
| `export` | Yes | No |
| `viewAsCustomer` | — | Own `user_id` only |
| `downloadAsCustomer` | — | Own `user_id` only |

## Notes

- Workspace page checks `Gate::authorize('viewAny', QuoteRequest::class)` on mount.
- Detail, edit and status actions authorise `view` / `update` per record.
- Export route authorises `export` before streaming.
- **Create Quote** CTA is omitted because `create` is always false (quotes originate from website/intake).
- Legacy `QuoteRequestResource` remains available by URL but navigation is hidden so Sales → Quotes points at the workspace page.
