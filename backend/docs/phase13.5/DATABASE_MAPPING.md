# Database Mapping

The Activity Centre reads from the existing `audit_logs` and `login_activities` tables. The columns used by each feature are listed below.

## `audit_logs`

| Column | Type | Used for |
|--------|------|----------|
| `id` | bigint | Composite identifier (`audit-{id}`) |
| `user_id` | foreign key (nullable) | Actor, user/system KPI split, user filter |
| `action` | string | Category, status, icon, title mapping |
| `subject_type` | string (nullable) | Module, related record type |
| `subject_id` | bigint (nullable) | Related record identifier, search |
| `details` | json (nullable) | Metadata rendered in detail drawer |
| `ip_address` | string (nullable) | Technical details and search |
| `user_agent` | text (nullable) | Technical details |
| `created_at` | timestamp | Timeline ordering, date filters |
| `updated_at` | timestamp | Detail metadata |

Indexes used implicitly:

- `action_created_at_index`
- `user_id_created_at_index`

## `login_activities`

| Column | Type | Used for |
|--------|------|----------|
| `id` | bigint | Composite identifier (`login-{id}`) |
| `user_id` | foreign key (nullable) | Actor when known |
| `email` | string (nullable) | Description and search |
| `ip_address` | string (nullable) | Technical details and search |
| `user_agent` | text (nullable) | Technical details |
| `successful` | boolean | Category and status mapping |
| `failed_reason` | string (nullable) | Description and metadata |
| `device` | string (nullable) | Detail drawer technical details |
| `os` | string (nullable) | Detail drawer technical details |
| `browser` | string (nullable) | Detail drawer technical details |
| `location` | string (nullable) | Detail drawer technical details |
| `created_at` | timestamp | Timeline ordering, date filters |
| `updated_at` | timestamp | Detail metadata |

Indexes used implicitly:

- `login_activities_user_id_created_at_index`
- `login_activities_successful_created_at_index`
- `login_activities_ip_address_index`

## Normalised DTO

`ActivityService` projects both table shapes into a common array structure:

- `id` — composite id (`audit-{id}` or `login-{id}`).
- `source` — `audit_log` or `login_activity`.
- `title`, `description`, `category`, `status`, `icon`, `color`.
- `user` — actor array (`id`, `name`, `email`, `avatar`, `initials`) or `null`.
- `subject` — related record array (`type`, `id`, `label`, `url`) or `null`.
- `module` — module label string.
- `ip_address`, `user_agent`, `device`, `browser`, `os`, `location`.
- `metadata` — arbitrary details (`AuditLog.details` or login context).
- `created_at`, `updated_at`, `diff_for_humans`.
