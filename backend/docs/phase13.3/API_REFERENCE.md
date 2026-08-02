# Phase 13.3 — Tasks API Reference

## Internal Service API

The Tasks module is implemented as an internal Filament/Livewire workspace. There are no public REST endpoints. All interaction is through the admin portal.

### Service: `App\Services\Admin\TaskService`

#### `queryTasks(array $filters, string $sort, string $direction): Builder`

Builds an eager-loaded task query with filters and sorting applied.

**Filters:**

| Key | Type | Description |
|-----|------|-------------|
| `search` | string | Searches title, description, internal notes, assignee name, creator name |
| `status` | array | List of status values |
| `priority` | array | List of priority values |
| `assignee` | int | Assignee user ID |
| `due_from` | string (date) | Minimum due date |
| `due_until` | string (date) | Maximum due date |
| `related_type` | string | Polymorphic related model class |

#### `paginateTasks(array $filters, string $sort, string $direction, int $perPage): LengthAwarePaginator`

Returns a paginated result set with query string preserved.

#### `getKpiCards(): array`

Returns four KPI cards:

- Total Tasks
- Completed
- In Progress
- Overdue

Each card includes a value, trend, and trend availability flag.

#### `createTask(User $creator, array $data): Task`

Creates a new task, transitions status to `assigned` when an assignee is provided, and logs activity.

#### `updateTask(Task $task, array $data): Task`

Updates a task and handles automatic `completed_at` timestamps.

#### `deleteTask(Task $task): void`

Soft deletes a task and logs activity.

#### `completeTask(Task $task): void`

Marks a task completed and logs activity.

#### `archiveTask(Task $task): void`

Marks a task archived and logs activity.

## Future Public API (Not Implemented)

The following endpoints are reserved for future customer/staff API expansion:

- `GET /api/v1/account/tasks`
- `GET /api/v1/account/tasks/{id}`
- `POST /api/v1/account/tasks`
- `PATCH /api/v1/account/tasks/{id}`
- `DELETE /api/v1/account/tasks/{id}`

These are not in scope for Phase 13.3.
