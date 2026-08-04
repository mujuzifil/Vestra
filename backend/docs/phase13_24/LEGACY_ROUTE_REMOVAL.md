# Phase 13.24 — Legacy Route Removal

## Quote Requests admin module

Deleted Filament stack:

- `App\Filament\Resources\QuoteRequestResource`
- `Pages/ListQuoteRequests`, `ViewQuoteRequest`, `EditQuoteRequest`
- `resources/views/filament/components/quote-request-attachments.blade.php`

Canonical CRM page: **Sales → Quotes** (`/sales/quotes`).

## Redirects

Registered in `routes/web.php`:

- `301 /quote-requests` → `/sales/quotes`
- `301 /quote-requests/{any}` → `/sales/quotes`

## Retained

- Public API `POST /api/v1/quote-requests`
- `App\Http\Resources\V1\QuoteRequestResource` (JSON)
- Model / policy / QuoteAdminService / Quotes workspace
- `QuotationRequestResource` (distributor quotations — separate module)

## Link remaps

- Dashboard View Quotes, Companies create/quick actions → Quotes workspace
- Activity subject maps: `QuoteRequest` → `/sales/quotes`, `DistributorRequest` → `/distributors/applications`
