# Phase 13.14 — Support Workspace Database Mapping

## Models Used

### SupportTicket
| Column | Type | Used In |
|---|---|---|
| id | bigint PK | All queries |
| user_id | FK → users | Customer display, eager load |
| reference_number | string | Table, drawer, export |
| subject | string | Table, drawer, search, export |
| enquiry_type | string | Table, filter, export |
| message | text | Drawer original message |
| status | enum(open,in_progress,resolved,closed) | Badges, KPI counts, filter, status update |
| priority | enum(low,medium,high,urgent) | Badges, filter, export |
| assigned_to | FK → users | Filter, drawer assignee section |
| attachments | JSON | Drawer attachments list |
| resolved_at | datetime | KPI avg resolution calculation, export |
| created_at | datetime | Table, date filter, sort, export |

### SupportTicketReply
| Column | Type | Used In |
|---|---|---|
| id | bigint PK | |
| support_ticket_id | FK → support_tickets | Eager load in drawer |
| user_id | FK → users (nullable) | Customer reply author |
| staff_id | FK → users (nullable) | Admin/staff reply author |
| message | text | Drawer conversation |
| attachments | JSON | (available, not rendered in drawer for admin MVP) |
| is_internal | boolean | Internal note badge & styling |
| created_at | datetime | Reply diffForHumans timestamp |

## Eager Loading
`SupportPage::getSelectedTicketProperty()` loads: `user`, `assignedStaff`, `replies.user`, `replies.staff`
`SupportAdminService::paginateTickets()` loads: `user`, `assignedStaff`

## KPI Queries
All counts are simple `WHERE status = ?` aggregates — no joins, live DB data only.
`getAvgResolutionHours()` uses `TIMESTAMPDIFF(SECOND, created_at, resolved_at)` — omitted from KPI cards if no resolved tickets.

## No Fake Data
All KPI cards show real counts. Trend comparisons use `created_at < startOfMonth` — when no previous data exists, trend shows "—" with "No comparison available".
