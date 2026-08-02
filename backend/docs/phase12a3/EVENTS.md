# Phase 12A.3 — Events & Activity

## Domain Events

Located in `App\Events\Account`:

- `QuoteViewed`
- `SupportTicketCreated`
- `SupportReplyCreated`
- `CompanyProfileUpdated`
- `CustomerDocumentDownloaded`
- `NotificationRead`

## Listener

`App\Listeners\Account\LogCustomerActivity` writes business activity to the `audit_logs` table for relevant events.

## Dispatch Points

| Event | Controller |
|-------|------------|
| `QuoteViewed` | `Account\QuoteController::show` |
| `SupportTicketCreated` | `Account\SupportTicketController::store` |
| `SupportReplyCreated` | `Account\SupportTicketController::reply` |
| `CompanyProfileUpdated` | `Account\CompanyProfileController::update` |
| `CustomerDocumentDownloaded` | `Account\DocumentController::download` |
| `NotificationRead` | `NotificationController::markAsRead`, `markAllAsRead` |

## CRM Readiness

All events receive the `User` and relevant entity, making it straightforward to hook into future CRM pipelines.
