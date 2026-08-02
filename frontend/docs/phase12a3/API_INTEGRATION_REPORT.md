# Phase 12A.3 — Frontend API Integration Report

## New API Clients

| File | Endpoints |
|------|-----------|
| `lib/api/account-dashboard.ts` | `GET /account/dashboard` |
| `lib/api/account-quotes.ts` | `GET /account/quotes`, `GET /account/quotes/{id}` |
| `lib/api/account-documents.ts` | `GET /account/documents`, document download URL helper |
| `lib/api/account-support.ts` | `GET /account/support`, `GET /account/support/{id}`, `POST /account/support`, `POST /account/support/{id}/reply` |
| `lib/api/account-company.ts` | `GET /account/company`, `PUT /account/company` |

## TypeScript Types

Added to `types/index.ts`:

- `CustomerQuoteItem`
- `CustomerQuote`
- `CustomerDocument`
- `SupportTicketReply`
- `SupportTicket`
- `CompanyProfile`
- `AccountDashboard`

## React Query Hooks

| File | Purpose |
|------|---------|
| `hooks/use-account-dashboard.ts` | Dashboard summary |
| `hooks/use-account-quotes.ts` | Paginated quotes list |
| `hooks/use-account-quote.ts` | Single quote detail |
| `hooks/use-account-documents.ts` | Paginated documents list |
| `hooks/use-support-tickets.ts` | Support tickets list + create mutation |
| `hooks/use-support-ticket.ts` | Single ticket + reply mutation |
| `hooks/use-company-profile.ts` | Company profile + update mutation |

## Pages Connected

- `app/account/account-page-client.tsx` — dashboard stats
- `app/account/quotes/quotes-page-client.tsx` — quotes list
- `app/account/quotes/[id]/quote-detail-client.tsx` — quote detail
- `app/account/documents/documents-page-client.tsx` — documents list
- `app/account/support/support-page-client.tsx` — tickets + create + reply
- `app/account/company/company-page-client.tsx` — view/edit company profile
