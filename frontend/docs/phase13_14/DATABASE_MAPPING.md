# Phase 13.14 — Support Workspace Frontend Database Mapping

## Data Flow
All data originates from the Laravel backend via Livewire computed properties. No direct DB access from the frontend layer.

## Livewire Properties → DB Columns
| Livewire Property | DB Column | Table |
|---|---|---|
| `search` | reference_number, subject, message, user.name, user.email | support_tickets / users |
| `statusFilter[]` | status | support_tickets |
| `priorityFilter[]` | priority | support_tickets |
| `enquiryTypeFilter[]` | enquiry_type | support_tickets |
| `assignedToFilter` | assigned_to | support_tickets |
| `dateFrom` / `dateUntil` | created_at | support_tickets |
| `sortField` / `sortDirection` | varies | support_tickets |

## Displayed Fields
| Template Location | Data Source |
|---|---|
| Ticket row — reference | `SupportTicket::reference_number` |
| Ticket row — subject | `SupportTicket::subject` |
| Ticket row — customer | `SupportTicket::user->name`, `user->email` |
| Ticket row — type | `SupportTicket::enquiry_type` |
| Ticket row — priority badge | `SupportTicket::priority` |
| Ticket row — status badge | `SupportTicket::status` |
| Ticket row — assigned | `SupportTicket::assignedStaff->name` |
| Drawer — replies | `SupportTicketReply::message`, `staff->name`, `user->name`, `created_at` |
| KPI cards | Aggregated counts from `support_tickets` |
