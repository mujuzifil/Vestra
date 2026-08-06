# Stage 24.10 — Frontend notes (Companies & Quotes CRM)

## Scope

Backend-led CRM hardening. Frontend continues to use existing endpoints:

- `POST /api/v1/quote-requests` — public quote submit (now company-linked server-side)
- `GET /api/v1/account/quotes` / `GET /api/v1/account/quotes/{id}` — customer portal reflects admin status changes from the same DB rows
- Account company APIs remain the source for company profile create/update

## Behavior notes

- Guest quote submissions may create a lightweight account user by email so the quote can attach to a `CompanyProfile`
- Status labels in the account portal come from `QuoteRequest` enum values (`pending`, `contacted`, `quoted`, `approved`, `declined`, `closed`)
- No new Business/Dealer/Institution registration forms in this release

## Smoke checklist

- [ ] Submit public quote → appears in Admin Quotes with company
- [ ] Approve quote in admin → account quote status updates
- [ ] Account company edit → Companies CRM shows updated fields
