# Permissions

## Policy

`App\Policies\NotificationPolicy` guards all notification actions.

| Method | Rule |
|--------|------|
| `viewAny(User $user)` | Only admin users (`$user->isAdmin()`) |
| `view(User $user, DatabaseNotification $notification)` | Owner-only: `notifiable_type === User::class && notifiable_id === $user->id` |
| `update(User $user, DatabaseNotification $notification)` | Same as `view` |
| `delete(User $user, DatabaseNotification $notification)` | Same as `view` |

## Livewire Authorisation

`NotificationsPage::mount()` calls `Gate::authorize('viewAny', DatabaseNotification::class)`.

Single-record actions (`markAsRead`, `markAsUnread`, `deleteNotification`, `openDetailPanel`) re-fetch the notification from `Auth::user()->notifications()` and authorise via the policy.

Bulk actions (`markAllRead`, `bulkMarkRead`, `bulkMarkUnread`, `bulkDelete`) require `viewAny`. The service layer scopes all mutations to the authenticated user's notifications, so even if IDs from another user were submitted, they would not be affected.

## Ownership Guarantees

- `NotificationService::baseQuery()` always starts from `$this->currentUser()->notifications()`.
- Every `whereIn('id', ...)` is chained to that base query.
- `deleteSelected()` and `markSelectedRead()` therefore cannot touch another user's records.
