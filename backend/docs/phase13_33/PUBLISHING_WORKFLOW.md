# Publishing Workflow

## Statuses

| Status | Public website |
| --- | --- |
| Draft | Hidden |
| Scheduled | Hidden until `scheduled_at` |
| Published | Visible (when visibility is public and `published_at` ≤ now) |
| Archived | Hidden from public; retained in admin |

## Scheduling

- Status `scheduled` requires a future `scheduled_at`.
- Command: `php artisan blog:publish-scheduled`
- Scheduler: every minute in `routes/console.php`
- On publish: status → Published, `published_at` set, `syncBlog()` runs

## Actions

- **Save as Draft** — forces draft status and saves
- **Publish** — publishes immediately unless status is already Scheduled (then schedules)
- **Archive** — set via status dropdown and save
- **Delete** — confirmation dialog; removes from DB and syncs public paths
