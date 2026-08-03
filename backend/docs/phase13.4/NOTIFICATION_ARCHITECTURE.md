# Notification Architecture

## Overview

Phase 13.4 introduces the Enterprise Notifications Workspace for the VESTRA Admin Portal. The implementation reuses Laravel's built-in `notifications` table and `DatabaseNotification` model rather than creating a parallel notification store. Three new enums provide typed categorisation:

- `App\Enums\NotificationCategory`
- `App\Enums\NotificationPriority`
- `App\Enums\NotificationType`

These values are stored inside the JSON `data` column of the standard `notifications` table, ensuring full backward compatibility with existing notifications.

## Components

### Service Layer

`App\Services\Admin\NotificationService` centralises all notification queries and mutations for the Workspace page:

- `paginateNotifications()` — filtered, sorted, paginated feed.
- `getKpiCards()` — total, unread, mentions, and system alerts.
- `markAsRead()` / `markAsUnread()` — single record updates.
- `markSelectedRead()` / `markSelectedUnread()` / `deleteSelected()` — bulk actions.
- `markAllRead()` — mark every unread notification as read.
- `getNotificationDetails()` — full payload for the detail side panel.

### Livewire Page

`App\Filament\Pages\Workspace\NotificationsPage` is a full Livewire page using the custom CRM layout (`filament.layouts.crm`). It supports:

- URL-backed filters: search, status, priority, category, type, date range.
- Sorting by created_at, read_at, and priority.
- Pagination.
- Selection state with bulk actions.
- Detail side panel that opens on card selection and auto-marks the notification as read.

### Header Dropdown

`App\Livewire\Admin\NotificationCenter` was updated to query real notifications instead of displaying hardcoded dummy data. It now shows the authenticated user's latest 10 notifications and a real unread count.

### Authorisation

`App\Policies\NotificationPolicy` scopes every action to the owning user. The `viewAny` gate requires an admin user.

## Data Flow

1. Existing Laravel notification events fire.
2. `NotificationDispatcherService::sendInApp()` creates a `SystemNotification`.
3. `SystemNotification::toDatabase()` enriches the payload with `category`, `type`, `priority`, `related_type`, `related_id`, and `triggered_by_user_id`.
4. Laravel stores the notification in the `notifications` table.
5. `NotificationsPage` reads the authenticated admin's notifications via `NotificationService`.
6. Blade components render the feed, KPIs, filters, and detail panel.

## Backward Compatibility

Notifications created before this phase lack the new metadata fields. They fall back to:

- `type` → `NotificationType::SYSTEM`
- `category` → derived from type, or `NotificationCategory::SYSTEM`
- `priority` → `NotificationPriority::INFORMATION`

No existing data is migrated because the fallback logic handles legacy rows at read time.
