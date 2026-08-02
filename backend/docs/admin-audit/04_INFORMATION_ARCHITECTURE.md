# Phase 13.0 — Information Architecture

## Current Grouping Logic

| Group | Intended Domain | Actual Contents |
|-------|-----------------|-----------------|
| E-Commerce | Retail commerce | Orders, Reviews |
| Catalog | Product master data | Products, Categories |
| Inventory | Stock management | Warehouses, Product Warehouse Stocks, Stock Movements |
| Distributors | Distributor master data | Distributors, Branches, Contacts, Documents, Price Tiers, Product Prices, Quotations |
| Finance | Money/credit | Credit Accounts, Credit Transactions, Payment Transactions, Payment Uploads |
| CRM | Customer relationships | Customers, Customer Tags |
| Operations | Business operations | Automated Workflows, Distributors, Purchase Orders, Suppliers |
| Requests | Inbound enquiries | Contact Messages, Customer Feedbacks, Quote Requests, Distributor Requests |
| Reports | Analytics | 15 report pages |
| Administration | Admin users/security | Users, Roles, Permissions, Audit Logs, Login Activity, Sessions, Security Policies, System Health |
| System | Platform config | Settings, System Information, Notification Dashboard/Templates/Deliveries, Announcements |

## Issues Identified

### 1. Distributors Split Across Two Groups

`DistributorResource` is registered under **Operations** while all distributor supporting resources (branches, contacts, documents, etc.) are under **Distributors**. This forces admins to jump between groups for the same business concept.

### 2. Content Group Not Declared

Blog resources use a `Content` navigation group that is not declared in `AdminPanelProvider`. Filament auto-creates it, which makes group ordering unpredictable.

### 3. E-Commerce Group Is Legacy

Orders and Reviews are retail-commerce concepts. After the corporate B2B redesign, these resources are low-value and confuse the primary B2B narrative.

### 4. Requests Group Is Heterogeneous

Contact Messages, Customer Feedbacks, Quote Requests, and Distributor Requests all share "inbound" status but serve different business functions. Quote Requests and Distributor Requests are high-value B2B workflows; feedback/contact are lower-priority support items.

### 5. Operations Contains Unrelated Items

Automated Workflows, Purchase Orders, Suppliers, and Distributors are grouped together but serve different operational domains.

### 6. Reports Overwhelms the Sidebar

15 report pages create a long list. There is no nested grouping (e.g., Sales, Operations, Intelligence) within Reports.

### 7. Settings vs. System vs. Administration

- **Administration** contains user/security tools.
- **System** contains settings, notifications, and announcements.
- The boundary between Administration and System is unclear.

## Recommendations for v3.0

1. Consolidate all distributor-related resources under a single **Distributors** group.
2. Rename or remove **E-Commerce**; archive Orders/Reviews if no longer needed.
3. Split **Requests** into **Sales** (Quote Requests) and **Applications** (Distributor Requests), or keep a single **Inbound** group with clear sub-labels.
4. Declare **Content** explicitly and place Blog resources there.
5. Introduce nested report categories or a reports hub with sections.
6. Merge low-level system items (Notification Deliveries, Announcements) under a single **Communications** sub-group.
