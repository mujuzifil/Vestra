# Phase 13.25 — Tasks Export Implementation

## Scope

Export remains on the Tasks workspace. Import was removed entirely (no button, route, modal, or dead code).

## Stack

| Piece | Location |
|-------|----------|
| Page URL builder | `TasksPage::getExportUrl($format)` |
| Route | `GET workspace/tasks/export` → `filament.admin.workspace.tasks.export` |
| Controller | `App\Http\Controllers\Admin\TaskExportController` |
| Rows | `TaskService::exportRows($filters, $sort, $direction)` |
| Formats | `ReportExportService` — CSV, Excel, PDF (existing) |
| Auth | `TaskPolicy::export` → admin only |

## Behaviour

`getExportUrl` forwards the Livewire filter/search/sort state as query params:

- `search`, `status[]`, `priority[]`, `assignee`, `due_from`, `due_until`
- `sort`, `direction`
- `format` ∈ `csv` | `excel` | `pdf`

The controller rebuilds the same query via `TaskService::queryTasks`, so the download matches the currently filtered list for the authenticated admin.

## UI

Header Export control is a dropdown (CSV / Excel / PDF), same pattern as Staff / Blog / Products workspaces. No Import control.
