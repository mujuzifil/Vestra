# CRM Readiness Assessment

## 1. Objective

Evaluate how close the existing Admin Portal is to functioning as a lightweight CRM for VESTRA’s B2B operations.

## 2. CRM Domain Checklist

| CRM Capability | Status | Notes |
|----------------|--------|-------|
| **Leads** | Partial | Quotes and contact enquiries act as inbound leads. No dedicated Lead model. |
| **Customers / Accounts** | Yes | `Customer` and `User` models exist; company information stored. |
| **Companies** | Partial | Company fields exist on customers; no standalone Company model. |
| **Contacts** | Partial | Customers are treated as contacts; no multi-contact per company support. |
| **Quotes / Opportunities** | Yes | `QuoteRequest` lifecycle supports value, status, priority, and assignment. |
| **Distributor Applications** | Yes | Full submission, review, approval, and notification workflow. |
| **Support Tickets** | Partial | Frontend support APIs exist (Phase 12A.3); admin ticket resource is missing. |
| **Tasks / Follow-ups** | No | No task or reminder system observed. |
| **Activity Timeline** | Partial | Events exist; no unified activity feed resource in admin. |
| **Assignments** | Partial | Quotes and distributor requests can be assigned; no workload dashboard. |
| **Communication History** | Partial | Notifications and emails tracked; no threaded communication log. |
| **Documents** | Partial | Distributor documents supported; customer documents not in admin. |
| **Tags / Segments** | Yes | `CustomerTagResource` supports customer segmentation. |
| **Reports & Analytics** | Partial | 14 report pages exist; limited forecasting and pipeline views. |
| **Sales Pipeline** | Partial | Quote status acts as pipeline stages; no visual pipeline view. |

## 3. What Already Exists

- **Customer-centric data model**: customers, company fields, quotes, saved products, documents, support enquiries, notifications.
- **Workflow engines**: quote lifecycle, distributor lifecycle, contact enquiry workflow.
- **Assignment model**: sales representative can be linked to quotes.
- **Event/notification layer**: status changes trigger notifications and emails.
- **RBAC**: roles and permissions can model sales, support, and admin teams.
- **Exports**: data can be extracted for external CRM if needed.
- **Distributor operations**: credit accounts, branches, contacts, documents, quotations, orders, invoices.

## 4. What Is Missing

1. **Admin Support Ticket Resource** — view and manage customer support enquiries.
2. **Task / Follow-up System** — schedule callbacks, site visits, quote follow-ups.
3. **Unified Activity Feed** — one timeline combining quotes, support, documents, profile changes.
4. **Pipeline View** — Kanban-style quote/opportunity board.
5. **Multi-contact Companies** — allow several contacts under one business account.
6. **Lead Source Tracking** — identify where enquiries originated (website, referral, event).
7. **Deal Value & Forecasting** — aggregate expected value by stage, month, representative.
8. **Communication Log** — record calls, emails, WhatsApp, meetings per customer/opportunity.
9. **Advanced Search & Segmentation** — filter customers by industry, district, status, lifetime value.
10. **Sales Performance Dashboard** — per-representative conversion rates, response times.

## 5. CRM Readiness Score

**6 / 10 — Foundation in place, but not yet a fully functional CRM.**

The platform can support basic sales and support workflows today. With targeted additions (support resource, tasks, activity feed, pipeline view), it can serve as an effective lightweight CRM without adopting a third-party product.

## 6. Recommended CRM Roadmap

1. **Short term** — Add admin Support Ticket resource, customer activity timeline, and quote pipeline view.
2. **Medium term** — Introduce Tasks/Follow-ups, multi-contact companies, and communication log.
3. **Long term** — Add forecasting, advanced segmentation, integrations (email/WhatsApp), and sales analytics.
