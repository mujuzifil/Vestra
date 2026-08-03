# Event Integration

## Existing Infrastructure

VESTRA already has a comprehensive notification event pipeline:

- `App\Events\Notification\*` event classes
- `App\Listeners\Notification\DispatchNotificationListener` maps events to templates and users
- `App\Services\NotificationDispatcherService` delivers via email, SMS, in-app, and push
- `App\Notifications\SystemNotification` persists in-app notifications to the database

## Phase 13.4 Changes

### `SystemNotification` Enrichment

`SystemNotification::toDatabase()` now stores typed metadata:

- `category`
- `type`
- `priority`
- `related_type`
- `related_id`
- `triggered_by_user_id`

`NotificationDispatcherService::sendInApp()` accepts these values from `$metadata` and passes them to `SystemNotification`.

### Future Event Enrichment

To fully categorise notifications, each event config in `DispatchNotificationListener::resolveConfig()` should include a `metadata` array. Example:

```php
$event instanceof QuoteRequestSubmitted => [
    'users' => [...],
    'template' => 'quote_request.admin_notification',
    'channels' => [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
    'topic' => 'order_updates',
    'variables' => [...],
    'metadata' => [
        'type' => NotificationType::QUOTE_SUBMITTED,
        'priority' => NotificationPriority::INFORMATION,
        'category' => NotificationCategory::SALES,
        'related_type' => 'quote',
        'related_id' => $event->quoteRequest->id,
    ],
],
```

This was intentionally not applied broadly in Phase 13.4 to avoid scope creep and risk in existing notification flows. The Workspace page handles legacy notifications gracefully via fallback values.

## Real-Time Preparation

The Notifications page is structured so future WebSocket/Laravel Echo integration can broadcast new notifications without restructuring components. Livewire actions are public methods and state changes are minimal, making re-rendering from external events straightforward.
