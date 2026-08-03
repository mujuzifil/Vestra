# Phase 13.7 — API Reference

## Workspace routes (Filament admin panel)

| Method | Path | Name | Description |
|--------|------|------|-------------|
| GET | `/sales/quotes` | `filament.admin.pages.sales.quotes` | Quotes workspace page |
| GET | `/sales/quotes/export` | `filament.admin.sales.quotes.export` | Export filtered quotes |

### Export query parameters

`format` (`csv`|`excel`|`pdf`), `search`, `status[]`, `priority[]`, `district[]`, `city[]`, `assigned_to`, `date_from`, `date_until`, `close_from`, `close_until`, `min_value`, `max_value`

## Existing APIs (unchanged)

| Method | Path | Purpose |
|--------|------|---------|
| POST | `/api/v1/quote-requests` | Public quote submit |
| GET | `/api/v1/account/quotes` | Customer quote list |
| GET | `/api/v1/account/quotes/{quote}` | Customer quote detail |
| GET | `/api/v1/account/quotes/{quote}/attachments/{index}` | Customer attachment download |

`QuoteRequestService::submit` and customer account quote APIs were not modified in this phase.
