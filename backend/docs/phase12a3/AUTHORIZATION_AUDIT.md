# Phase 12A.3 — Authorization Audit

## Principle

Every business-portal endpoint enforces customer ownership. Customers must never access another customer's data.

## Policies

| Policy | Model | Actions |
|--------|-------|---------|
| `QuoteRequestPolicy` | `QuoteRequest` | `viewAsCustomer`, `downloadAsCustomer` — requires `user_id === $user->id` |
| `CustomerDocumentPolicy` | `CustomerDocument` | `view`, `download` — requires `user_id === $user->id` |
| `SupportTicketPolicy` | `SupportTicket` | `view`, `reply` — requires `user_id === $user->id` |
| `CompanyProfilePolicy` | `CompanyProfile` | `view`, `update` — requires `user_id === $user->id` |

## Implementation

- Quote controller uses `CustomerQuoteService::listForUser` / `findForUser` to scope queries.
- Support and document controllers use route model binding with policy authorization.
- Company profile controller fetches or creates a profile scoped to the authenticated user.

## Security Notes

- Missing customer-owned resources return `404 Not Found` to avoid leaking existence.
- Admin access to these resources remains governed by existing admin policies.
