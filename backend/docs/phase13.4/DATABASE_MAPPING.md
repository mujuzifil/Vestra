# Database Mapping

## Table: `notifications`

Laravel's standard notifications table is reused unchanged.

| Column | Type | Purpose |
|--------|------|---------|
| `id` | uuid | Primary key |
| `type` | string | Notification class name (e.g. `App\Notifications\SystemNotification`) |
| `notifiable_type` | string | Polymorphic owner type |
| `notifiable_id` | bigint | Polymorphic owner ID |
| `data` | text | JSON payload |
| `read_at` | timestamp | Read timestamp |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Update timestamp |

## JSON `data` Schema

| Key | Type | Description |
|-----|------|-------------|
| `template_key` | string | Template identifier used to render the notification |
| `title` | string | Short notification title |
| `message` | string | Longer notification body |
| `priority` | string | `information`, `success`, `warning`, or `critical` |
| `category` | string | `crm`, `sales`, `distributor`, `customer`, `operations`, `marketing`, `system`, or `security` |
| `type` | string | Specific event type, e.g. `quote_submitted` |
| `action_url` | string\|null | Optional link to a related record |
| `variables` | object | Template variables |
| `related_type` | string\|null | Related entity slug, e.g. `quote`, `support_ticket` |
| `related_id` | int\|null | Related entity primary key |
| `triggered_by_user_id` | int\|null | User who triggered the event |

## Query Strategy

Filters and sorting operate on the JSON payload using Laravel's JSON column operators (`data->priority`, `data->category`, `data->type`). Search uses raw `JSON_EXTRACT` with `LOWER` and `LIKE` for case-insensitive matching. All queries are scoped to `Auth::user()->notifications()` so users can never access another user's notifications.

## Indexes

The existing migration already indexes:

- `notifiable_type`, `notifiable_id`, `read_at`
- `created_at`

These indexes support the most common query patterns (listing unread, ordering by date).
