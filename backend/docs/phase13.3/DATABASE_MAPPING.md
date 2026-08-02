# Phase 13.3 — Tasks Database Mapping

## Table: `tasks`

| Column | Type | Nullable | Notes |
|--------|------|----------|-------|
| `id` | bigInteger unsigned | no | Primary key |
| `title` | string(255) | no | Task title |
| `description` | text | yes | Long-form description |
| `status` | string | no | Indexed; casts to `TaskStatus` enum |
| `priority` | string | no | Indexed; casts to `TaskPriority` enum |
| `assignee_id` | foreignId → users | yes | Nullable FK, `nullOnDelete` |
| `created_by_id` | foreignId → users | no | Required FK, `cascadeOnDelete` |
| `related_type` | string | yes | Polymorphic type |
| `related_id` | unsigned bigInteger | yes | Polymorphic id |
| `due_date` | datetime | yes | Indexed |
| `completed_at` | datetime | yes | Set automatically on completion |
| `internal_notes` | text | yes | Staff-only notes |
| `attachment_paths` | json | yes | Array of file paths |
| `deleted_at` | timestamp | yes | Soft delete timestamp |
| `created_at` | timestamp | no | |
| `updated_at` | timestamp | no | |

## Indexes

- `status`
- `priority`
- `assignee_id`
- `due_date`
- `tasks_related_type_related_id_index` (morphs index)
- Composite `(status, priority, assignee_id)`
- Composite `(status, due_date)`

## Relationships

- `assignee()` → `User` (belongsTo)
- `creator()` → `User` (belongsTo)
- `related()` → polymorphic

## Enums

### TaskStatus

| Value | Label | Color | Icon |
|-------|-------|-------|------|
| `new` | New | info | sparkles |
| `assigned` | Assigned | primary | user-circle |
| `in_progress` | In Progress | warning | arrow-path |
| `waiting` | Waiting | gray | clock |
| `blocked` | Blocked | danger | no-symbol |
| `completed` | Completed | success | check-circle |
| `cancelled` | Cancelled | gray | x-circle |
| `archived` | Archived | gray | archive-box |

### TaskPriority

| Value | Label | Color | Icon |
|-------|-------|-------|------|
| `low` | Low | info | arrow-down |
| `medium` | Medium | primary | minus |
| `high` | High | warning | arrow-up |
| `critical` | Critical | danger | exclamation-triangle |
