# Phase 13.14 — Support Workspace Validation Report

## Policy Enforcement

### SupportTicketPolicy
| Method | Rule |
|---|---|
| `viewAny` | `$user->isAdmin()` |
| `view` | `isAdmin()` OR `ticket->user_id === $user->id` |
| `update` | `isAdmin()` |
| `reply` | admin: status !== 'closed'; customer: owner AND status not in ['closed','resolved'] |
| `export` | `isAdmin()` |

### Gate Calls in SupportPage
- `mount()`: `Gate::authorize('viewAny', SupportTicket::class)`
- `openDetailDrawer()`: `Gate::authorize('view', $ticket)`
- `submitReply()`: `Gate::authorize('reply', $ticket)`
- `updateTicketStatus()`: `Gate::authorize('update', $ticket)`
- `assignTicket()`: `Gate::authorize('update', $ticket)`

### Export Controller
- `Gate::authorize('export', SupportTicket::class)` — uses `SupportTicketPolicy::export()`

## Filter Validation
- Sort direction: coerced to `asc|desc` in `applySorting()`
- Export format: validated against `['csv','excel','pdf']`, 400 on invalid
- Date filters: passed as strings to `whereDate()` — DB-level validation

## Input Sanitisation
- `replyMessage` trimmed before check in `submitReply()`
- `updateStatus` compared to existing status before DB write to skip no-ops

## Existing Rules Preserved
- Customer `view` and `reply` access in `SupportTicketPolicy` retained
- `SupportTicketService::reply()` unchanged
- New `adminReply()` added without removing or altering existing methods
