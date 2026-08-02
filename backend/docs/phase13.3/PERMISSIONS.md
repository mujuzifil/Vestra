# Phase 13.3 — Tasks Permissions

## Policy: `App\Policies\TaskPolicy`

All task actions are restricted to administrative users.

| Ability | Requirement |
|---------|-------------|
| `viewAny` | User is an admin (`isAdmin()`) |
| `view` | User is an admin |
| `create` | User is an admin |
| `update` | User is an admin |
| `delete` | User is an admin |
| `restore` | User is an admin |
| `forceDelete` | User is an admin |
| `assign` | User is an admin |
| `complete` | User is an admin |
| `archive` | User is an admin |

## Admin Definition

A user is considered an admin when any of the following is true:

- `is_admin` column is `true`
- Has the `Super Administrator` role
- Has the `super-admin` role

## Enforcement

- `TasksPage::mount()` calls `Gate::authorize('viewAny', Task::class)`.
- Every mutating action (create, update, delete, complete, archive) calls `Gate::authorize(...)` before executing.
- Tests in `tests/Feature/Policy/TaskPolicyTest.php` verify both positive and negative authorization cases.
