# Phase 12A.3 — Business Portal API Reference

## Base Path

All endpoints are prefixed with `/api/v1/account` and require a valid Sanctum token.

---

## Dashboard

### GET /account/dashboard

Returns a summary of the authenticated customer's business activity.

**Response**

```json
{
  "success": true,
  "data": {
    "quotes": { "submitted": 5, "pending": 2, "approved": 1 },
    "support_enquiries": 3,
    "documents": 4,
    "saved_products": 7,
    "unread_notifications": 2,
    "distributor_status": "pending",
    "recent_quotes": [...],
    "recent_documents": [...]
  }
}
```

---

## Quotes

### GET /account/quotes

List quote requests belonging to the authenticated customer.

**Query Parameters**

- `page` (optional, default: 1)
- `per_page` (optional, default: 15)

**Response**

Paginated list of `CustomerQuote` resources with `data`, `current_page`, `last_page`, `per_page`, `total`.

### GET /account/quotes/{id}

Return a single quote request.

### GET /account/quotes/{id}/attachments/{index}

Download an attachment by index from the quote's attachments array.

---

## Documents

### GET /account/documents

List customer-specific documents.

**Query Parameters**

- `page` (optional, default: 1)
- `per_page` (optional, default: 15)

### GET /account/documents/{id}/download

Download a document file.

---

## Support

### GET /account/support

List support tickets submitted by the customer.

### GET /account/support/{id}

Return a single support ticket including replies.

### POST /account/support

Create a new support ticket.

**Fields**

- `subject` (required, string, max 255)
- `message` (required, string, max 5000)
- `enquiry_type` (optional: general, sales, distributor, quote, technical_support, other)
- `priority` (optional: low, medium, high, urgent)
- `attachments[]` (optional, files: pdf, jpg, png, doc, docx, max 5MB each)

### POST /account/support/{id}/reply

Add a reply to an existing ticket.

**Fields**

- `message` (required, string, max 5000)
- `attachments[]` (optional)

---

## Company Profile

### GET /account/company

Return the customer's company profile (auto-created if missing).

### PUT /account/company

Update the company profile.

**Fields**

All fields are optional and nullable:

- `company_name`, `industry`, `business_type`
- `tax_identification`, `registration_number`
- `website` (must be a valid URL if provided)
- `district`, `city`, `country`, `address`
- `primary_contact_name`, `primary_contact_phone`, `primary_contact_email`

---

## Notifications

Account notifications reuse the existing `/api/v1/notifications` endpoints.
The account prefix exposes read-only aliases for convenience:

- `GET /account/notifications`
- `GET /account/notifications/unread`
- `PATCH /account/notifications/{id}/read`
- `PATCH /account/notifications/read-all`
