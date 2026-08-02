# Phase 13.0 — Resource Relationships

## Entity Map

```
User (admin)
  ├─ Roles
  ├─ Permissions
  ├─ Audit Logs
  ├─ Login Activities
  ├─ Admin Sessions
  └─ API Tokens (planned)

User (customer)
  ├─ CompanyProfile
  ├─ CustomerTags
  ├─ Addresses
  ├─ Orders
  ├─ Payments
  ├─ QuoteRequests
  ├─ SavedItems
  ├─ SupportTickets
  ├─ CustomerDocuments
  └─ Audit Logs

DistributorRequest
  └─ (approval) → Distributor + User

Distributor
  ├─ User
  ├─ CreditAccount
  │    └─ CreditTransactions
  ├─ Branches
  ├─ Contacts
  ├─ Documents
  ├─ Quotations
  ├─ Orders
  └─ Invoices

Product
  ├─ Category
  ├─ Images
  ├─ ProductWarehouseStocks
  ├─ StockMovements
  ├─ Reviews
  ├─ DistributorProductPrices
  └─ QuoteRequestItems

BlogPost
  ├─ Author
  ├─ Categories
  └─ Tags

Setting
  └─ Media (settings collection)

NotificationTemplate
  └─ NotificationDeliveries
```

## Disconnected Resources

| Resource | Disconnection |
|----------|---------------|
| ContactMessage | Not linked to Customer or QuoteRequest. |
| CustomerFeedback | Not linked to Customer or Product. |
| Announcement | Standalone; no delivery target metrics. |
| AutomatedWorkflow | No visible relation manager to execution logs. |
| PurchaseOrder / Supplier | Related in model but not exposed as relation managers in navigation. |

## Duplicate Concepts

| Concept | Locations | Risk |
|---------|-----------|------|
| Quotations | `QuoteRequest` (Requests) + `QuotationRequest` (Distributors) | Users may confuse customer quotes with distributor quotes. |
| Users | `UserResource` (admin) + `CustomerResource` (same model, different scope) | Two resources for the same table. |
| Documents | `DistributorDocument` + `CustomerDocument` (not yet in admin) | Divergent document models. |
| Orders | Retail `Order` + Distributor `Order` (same table, different context) | Reporting may mix retail and wholesale orders. |

## Missing Relationships

- Customer → Company Profile (not exposed in CustomerResource relation managers).
- Quote Request → Customer (link exists via `user_id` but not surfaced in admin form).
- Distributor Request → Customer (no link to existing customer record).
- Support Ticket → Customer (not represented in admin portal).
- Customer Document → Customer (not represented in admin portal).
