# Audit Log Integration

## Sources of Truth

The Activity Centre is read-only and consumes two existing tables exactly as they are today:

- `audit_logs` — managed by `App\Models\AuditLog`.
- `login_activities` — managed by `App\Models\LoginActivity`.

No migrations, triggers, or new activity tables were created for this phase.

## AuditLog usage

`AuditLog` rows provide the bulk of operational history:

- `user_id` → actor (nullable for system events).
- `action` → primary signal for category, status, icon, and title.
- `subject_type` / `subject_id` → related record (morph).
- `details` → JSON metadata shown in the detail drawer.
- `ip_address` / `user_agent` → technical context.
- `created_at` / `updated_at` → timeline ordering.

`ActivityService` eager-loads `user` and `subject` when querying `AuditLog` so labels and URLs can be resolved without N+1 queries.

## LoginActivity usage

`LoginActivity` rows provide authentication telemetry:

- `user_id` → actor when known (nullable for anonymous failures).
- `email` → attempted email address for failures.
- `successful` / `failed_reason` → status and description.
- `device`, `os`, `browser`, `location` → enriched technical context.
- `ip_address` / `user_agent` → network context.
- `created_at` → timeline ordering.

Successful attempts are classified as `Authentication / Success`; failed attempts are `Security / Error`.

## Authorisation

`App\Policies\AuditLogPolicy` restricts every Activity Centre action to admin users:

- `viewAny` — required to open the page.
- `view` — required to open the detail drawer for an `AuditLog` row.
- `export` — required to export data.

`LoginActivity` rows do not have a dedicated policy; their detail view is gated by `AuditLogPolicy::viewAny()`, preserving the admin-only scope.

## No fabricated data

KPI cards, feed rows, exports, and filter counts are computed directly from database rows. When no data exists, the UI renders an empty state rather than placeholders or zeroed mock metrics.
