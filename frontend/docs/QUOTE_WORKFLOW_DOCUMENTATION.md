# VESTRA Quote Request Workflow

**Date:** 2026-08-01

---

## 1. Workflow Overview

```
┌─────────────────┐
│  Public Visitor │
└────────┬────────┘
         │
         ▼
┌──────────────────────────┐
│  /request-quote page     │
│  Fills enquiry form      │
└────────┬─────────────────┘
         │ POST /api/v1/quote-requests
         ▼
┌──────────────────────────┐
│  QuoteRequestController  │
│  validates input         │
└────────┬─────────────────┘
         │
         ▼
┌──────────────────────────┐
│  QuoteRequestService     │
│  - creates reference     │
│  - persists request      │
│  - persists items        │
│  - fires event           │
│  - sends customer email  │
└────────┬─────────────────┘
         │
    ┌────┴────┐
    ▼         ▼
Customer   Admin Notification
Email      (email + in-app)
    │         │
    ▼         ▼
Sales Team  Filament Admin
reviews &   manages status
contacts    and assignment
```

---

## 2. Public Submission

### 2.1 Endpoint

```http
POST /api/v1/quote-requests
Content-Type: application/json
```

### 2.2 Request Body Example

```json
{
  "full_name": "John Doe",
  "company_name": "Acme Ltd",
  "email": "john@acme.com",
  "phone": "+256 701 234 567",
  "district": "Kampala",
  "city": "Nakawa",
  "address": "Plot 123, Industrial Area",
  "preferred_delivery_date": "2026-08-15",
  "delivery_location": "Kampala Warehouse",
  "requirements": "We need bulk supply for a hotel chain.",
  "items": [
    {
      "product_id": 1,
      "product_name": "Heavy Duty Detergent",
      "package_size": "20L",
      "quantity": 50,
      "notes": "Urgent"
    }
  ]
}
```

### 2.3 Validation Rules

| Field | Rule |
|-------|------|
| `full_name` | required, string, min 2, max 255 |
| `company_name` | required, string, min 2, max 255 |
| `email` | required, valid email, max 255 |
| `phone` | required, string, min 7, max 50 |
| `district` | optional, string, max 255 |
| `city` | optional, string, max 255 |
| `address` | optional, string, max 1000 |
| `preferred_delivery_date` | optional, date, not in the past |
| `delivery_location` | optional, string, max 1000 |
| `requirements` | optional, string, max 5000 |
| `items` | optional, array, max 50 |
| `items.*.product_name` | required with items, string, max 255 |
| `items.*.product_id` | optional, exists in products table |
| `items.*.package_size` | optional, string, max 255 |
| `items.*.quantity` | required with items, integer, min 1, max 999999 |
| `items.*.notes` | optional, string, max 2000 |

### 2.4 Response Example (201 Created)

```json
{
  "success": true,
  "message": "Thank you. Your quotation request has been received.",
  "data": {
    "id": 1,
    "reference_number": "QR-20260801-0001",
    "full_name": "John Doe",
    "company_name": "Acme Ltd",
    "email": "john@acme.com",
    "phone": "+256 701 234 567",
    "status": "pending",
    "status_label": "Pending",
    "items": [
      {
        "id": 1,
        "product_id": 1,
        "product_name": "Heavy Duty Detergent",
        "package_size": "20L",
        "quantity": 50,
        "notes": "Urgent"
      }
    ],
    "created_at": "2026-08-01T10:00:00.000000Z"
  }
}
```

---

## 3. Reference Number Format

Format: `QR-YYYYMMDD-SEQUENCE`

- `QR` — quote request prefix
- `YYYYMMDD` — submission date
- `SEQUENCE` — four-digit daily counter, zero-padded

Example: `QR-20260801-0001`

---

## 4. Status Lifecycle

| Status | Meaning | Editable in Filament |
|--------|---------|----------------------|
| `pending` | New submission, not yet reviewed | Yes |
| `contacted` | Sales team has reached out | Yes |
| `quoted` | Formal quotation sent | Yes |
| `approved` | Customer accepted quotation | Yes |
| `declined` | Customer rejected or did not proceed | Yes |
| `closed` | No further action required | Yes |

---

## 5. Notifications

### 5.1 Customer Confirmation Email

- **Recipient:** requester email
- **Mailable:** `App\Mail\QuoteRequestReceivedMail`
- **View:** `emails/quote-request/received.blade.php`
- **Subject:** "We received your quotation request — {reference_number}"
- **Content:** acknowledgement, reference number, product summary, expected contact window

### 5.2 Admin Notification

- **Event:** `QuoteRequestSubmitted`
- **Template:** `quote_request.admin_notification`
- **Recipients:** all users with `is_admin = true`
- **Channels:** email + in-app
- **Content:** reference, customer name, company, email, phone, product summary

---

## 6. Admin Management

### 6.1 Access

Log in to Filament admin → Requests → Quote Requests.

### 6.2 Available Actions

- View full request details
- Update status
- Assign to admin user
- Add admin notes
- Delete (admin only)
- Bulk status updates

### 6.3 Filters

- Status
- Assigned To
- Submitted Date Range

---

## 7. Error Handling

| Scenario | Response |
|----------|----------|
| Missing required fields | 422 Unprocessable with validation errors |
| Invalid email | 422 Unprocessable |
| Past delivery date | 422 Unprocessable |
| Throttle exceeded | 429 Too Many Requests |
| Server error | 500 with generic message (logged internally) |

---

## 8. Future Enhancements

- Multi-item form in frontend (currently supports one item with optional product_id).
- SMS notification to sales team.
- Auto-assign to sales representative based on region or product.
- Integration with CRM for lead tracking.
- Convert approved quote request into a distributor quotation or order.
