# Phase 12A.3 — Database Changes

## Migrations

| File | Purpose |
|------|---------|
| `2026_08_02_100000_add_user_id_to_quote_requests_table.php` | Links existing quote requests to authenticated users. |
| `2026_08_02_101000_create_company_profiles_table.php` | Stores per-customer company/business information. |
| `2026_08_02_102000_create_customer_documents_table.php` | Stores customer-specific documents with secure file paths. |
| `2026_08_02_103000_create_support_tickets_table.php` | Stores customer support enquiries. |
| `2026_08_02_104000_create_support_ticket_replies_table.php` | Stores replies to support tickets from users and staff. |

## Relationships

- `User::companyProfile()` — `hasOne(CompanyProfile::class)`
- `User::customerDocuments()` — `hasMany(CustomerDocument::class)`
- `User::supportTickets()` — `hasMany(SupportTicket::class)`
- `User::quoteRequests()` — `hasMany(QuoteRequest::class)`
- `QuoteRequest::user()` — `belongsTo(User::class)`
- `SupportTicket::replies()` — `hasMany(SupportTicketReply::class)`
- `SupportTicket::assignedStaff()` — `belongsTo(User::class, 'assigned_to')`
- `SupportTicketReply::user()` / `staff()` — polymorphic author

## Indexes

- `quote_requests.user_id`
- `company_profiles.user_id` (unique)
- `customer_documents.user_id`
- `support_tickets.user_id`
- `support_ticket_replies.support_ticket_id`

## Backwards Compatibility

`quote_requests.user_id` is nullable to preserve existing public submissions.
