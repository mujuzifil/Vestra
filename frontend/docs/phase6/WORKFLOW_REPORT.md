# Phase 6 — Request a Quote Workflow Report

## Public Submission Flow

```
Visitor lands on /request-quote
↓
Reviews value proposition and process
↓
Completes grouped quote form with one or more products
↓
Frontend validates fields and file sizes client-side
↓
Submits multipart/form-data to POST /api/v1/quote-requests
↓
Backend validates StoreQuoteRequestRequest
↓
QuoteRequestService creates QuoteRequest + QuoteRequestItems
↓
Attachments stored in storage/app/public/quote_attachments/{id}
↓
QuoteRequestSubmitted event fired
↓
DispatchNotificationListener sends:
  • Admin notification to all is_admin users (email + in-app)
  • Customer confirmation to matching user account (email + in-app)
↓
Customer receives QuoteRequestReceivedMail
↓
API returns success with reference_number
↓
Frontend displays inline success with reference and CTAs
```

## Backend Changes
- Migration added `priority`, `estimated_value`, `expected_close_date`, `attachments`, and `crm_metadata` to `quote_requests`.
- `QuoteRequestService::submit()` now accepts and stores CRM fields and attachments.
- `StoreQuoteRequestRequest` validates up to 5 attachments (PDF, images, DOC, DOCX, XLS, XLSX).
- `DispatchNotificationListener` now sends both admin and customer notifications for `QuoteRequestSubmitted`.
- Filament `QuoteRequestResource` exposes CRM fields and attachment download links.

## Form Field Mapping
- Full public form fields map directly to existing `QuoteRequest` columns.
- Product rows map to `QuoteRequestItem` records.
- Attachments are stored as an array of storage paths in the `attachments` JSON column.

## CRM Readiness
- New nullable columns provide a lightweight CRM foundation.
- `crm_metadata` JSON can store status history and communication notes until a dedicated CRM module is built.
