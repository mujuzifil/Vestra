# Phase 13.3 — Tasks Workspace Architecture

## Overview

The Tasks Workspace replaces the placeholder `Workspace → Tasks` page with a fully functional enterprise task management module built on the custom VESTRA Admin CRM shell.

It is implemented as a custom Livewire page (`App\Filament\Pages\Workspace\TasksPage`) backed by a new `Task` model, `TaskService`, `TaskPolicy`, and reusable Blade components.

## Components

### Backend

| Component | Path | Responsibility |
|-----------|------|----------------|
| `Task` model | `app/Models/Task.php` | Domain model with enums, scopes, relationships, and state helpers |
| `TaskStatus` enum | `app/Enums/TaskStatus.php` | NEW, ASSIGNED, IN_PROGRESS, WAITING, BLOCKED, COMPLETED, CANCELLED, ARCHIVED |
| `TaskPriority` enum | `app/Enums/TaskPriority.php` | LOW, MEDIUM, HIGH, CRITICAL |
| `TaskService` | `app/Services/Admin/TaskService.php` | Querying, filtering, sorting, pagination, KPI calculations, CRUD, activity logging |
| `TaskPolicy` | `app/Policies/TaskPolicy.php` | Authorization gates for task actions |
| `TasksPage` | `app/Filament/Pages/Workspace/TasksPage.php` | Livewire page hosting search, filters, sort, pagination, and CRUD modals |
| Migration | `database/migrations/2026_08_02_105000_create_tasks_table.php` | Database schema with indexes and soft deletes |
| Factory | `database/factories/TaskFactory.php` | Test factory with overdue and completed states |

### Frontend

| Component | Path |
|-----------|------|
| Page view | `resources/views/filament/pages/workspace/tasks.blade.php` |
| Page header | `resources/views/components/tasks/page-header.blade.php` |
| KPI cards | `resources/views/components/tasks/kpi-cards.blade.php` |
| Filter bar | `resources/views/components/tasks/filter-bar.blade.php` |
| Task table | `resources/views/components/tasks/task-table.blade.php` |
| Task row | `resources/views/components/tasks/task-row.blade.php` |
| Task form drawer | `resources/views/components/tasks/task-form.blade.php` |
| Empty state | `resources/views/components/tasks/empty-state.blade.php` |
| Pagination | `resources/views/components/tasks/pagination.blade.php` |
| Styles | `resources/css/filament/admin/components/tasks.css` |

## Data Flow

1. User navigates to `/admin/tasks`.
2. `TasksPage::mount()` authorizes access via `TaskPolicy`.
3. Livewire computes `tasks`, `kpiCards`, and `assignees` properties.
4. `TaskService::paginateTasks()` builds an eager-loaded, filtered, sorted query.
5. Filters are bound to URL query parameters and persisted across navigation.
6. Create/edit actions open a slide-out drawer; form submission calls `TaskService`.
7. `TaskService` persists changes and logs activity via `AuditService`.
8. KPI cache is invalidated on every mutating operation.

## Activity Logging

All task lifecycle events are written to `audit_logs`:

- `task.created`
- `task.updated`
- `task.assigned`
- `task.completed`
- `task.archived`
- `task.deleted`

## Caching

KPI aggregates are cached for 5 minutes under `admin.tasks.kpi` and cleared on create, update, delete, complete, and archive operations.
