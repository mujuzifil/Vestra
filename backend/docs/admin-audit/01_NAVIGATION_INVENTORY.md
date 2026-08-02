# Phase 13.0 — Navigation Inventory

## Panel Configuration

- **Panel ID:** `admin`
- **Brand:** VESTRA
- **Domain:** `config('app.admin_domain')`
- **Path:** `/`
- **Font:** Poppins
- **Sidebar:** collapsible on desktop
- **Max content width:** full

## Navigation Groups (as defined in AdminPanelProvider)

1. E-Commerce
2. Catalog
3. Inventory
4. Distributors
5. Finance
6. CRM
7. Operations
8. Requests
9. Reports
10. Administration
11. System

## Dashboard

| Page | Route | Icon | Group | Sort | Notes |
|------|-------|------|-------|------|-------|
| Dashboard | `/` | `heroicon-o-home` | (top-level) | — | 17 widgets configured |
| Force Password Change | `/force-password-change` | `heroicon-o-shield-exclamation` | hidden | — | mandatory password change middleware |

## Administration

| Page | Route | Icon | Sort | Badge |
|------|-------|------|------|-------|
| Administration | `/administration` | `heroicon-o-shield-check` | 1 | — |
| Users | `/users` | `heroicon-o-users` | 2 | — |
| Roles | `/roles` | `heroicon-o-shield-check` | 3 | — |
| Permissions | `/permissions` | `heroicon-o-key` | 4 | — |
| Audit Logs | `/audit-logs` | `heroicon-o-clipboard-document-list` | 5 | — |
| Login Activity | `/login-activities` | `heroicon-o-arrow-right-on-rectangle` | 6 | — |
| Sessions | `/admin-sessions` | `heroicon-o-computer-desktop` | 7 | — |
| Security Policies | `/security-policies` | `heroicon-o-lock-closed` | 8 | — |
| System Health | `/system-health` | `heroicon-o-heart` | 9 | — |

## Catalog

| Page | Route | Icon | Sort |
|------|-------|------|------|
| Products | `/products` | `heroicon-o-shopping-bag` | 1 |
| Categories | `/categories` | `heroicon-o-tag` | 2 |

## Inventory

| Page | Route | Icon | Sort |
|------|-------|------|------|
| Warehouses | `/warehouses` | `heroicon-o-building-office-2` | 1 |
| Product Warehouse Stocks | `/product-warehouse-stocks` | `heroicon-o-cube` | 2 |
| Stock Movements | `/stock-movements` | `heroicon-o-arrows-right-left` | 3 |

## Distributors

| Page | Route | Icon | Sort |
|------|-------|------|------|
| Distributors | `/distributors` | `heroicon-o-building-storefront` | 2 |
| Branches | `/distributor-branches` | `heroicon-o-building-storefront` | 2 |
| Contacts | `/distributor-contacts` | `heroicon-o-users` | 3 |
| Documents | `/distributor-documents` | `heroicon-o-folder` | 4 |
| Price Tiers | `/distributor-price-tiers` | `heroicon-o-queue-list` | 5 |
| Product Prices | `/distributor-product-prices` | `heroicon-o-tag` | 6 |
| Quotations | `/quotation-requests` | `heroicon-o-document-text` | 7 |

## Finance

| Page | Route | Icon | Sort |
|------|-------|------|------|
| Credit Accounts | `/credit-accounts` | `heroicon-o-credit-card` | 1 |
| Credit Transactions | `/credit-transactions` | `heroicon-o-arrows-right-left` | 2 |
| Payment Transactions | `/payment-transactions` | `heroicon-o-banknotes` | 3 |
| Payment Uploads | `/payment-uploads` | `heroicon-o-document-arrow-up` | 4 |

## CRM

| Page | Route | Icon | Sort |
|------|-------|------|------|
| Customers | `/customers` | `heroicon-o-users` | 2 |
| Customer Tags | `/customer-tags` | `heroicon-o-tag` | 12 |

## Operations

| Page | Route | Icon | Sort |
|------|-------|------|------|
| Automated Workflows | `/automated-workflows` | `heroicon-o-bolt` | 1 |
| Distributors | `/distributors` | `heroicon-o-building-storefront` | 2 |
| Purchase Orders | `/purchase-orders` | `heroicon-o-clipboard-document-list` | 3 |
| Suppliers | `/suppliers` | `heroicon-o-truck` | 4 |

## Requests

| Page | Route | Icon | Sort |
|------|-------|------|------|
| Contact Messages | `/contact-messages` | `heroicon-o-envelope` | 1 |
| Customer Feedbacks | `/customer-feedback` | `heroicon-o-chat-bubble-left-right` | 2 |
| Quote Requests | `/quote-requests` | `heroicon-o-clipboard-document-list` | 2 |
| Distributor Requests | `/distributor-requests` | `heroicon-o-truck` | 3 |

## Reports

| Page | Route | Icon | Sort |
|------|-------|------|------|
| Reports Dashboard | `/reports` | `heroicon-o-chart-pie` | 1 |
| Revenue | `/reports/revenue` | `heroicon-o-banknotes` | 10 |
| Sales | `/reports/sales` | `heroicon-o-shopping-bag` | 20 |
| Customers | `/reports/customers` | `heroicon-o-users` | 30 |
| Customer Intelligence | `/reports/customer-intelligence` | `heroicon-o-user-group` | 31 |
| Inventory | `/reports/inventory` | `heroicon-o-cube` | 40 |
| Inventory Intelligence | `/reports/inventory-intelligence` | `heroicon-o-cube-transparent` | 51 |
| Engagement | `/reports/engagement` | `heroicon-o-chat-bubble-left-right` | 50 |
| Distributors | `/reports/distributors` | `heroicon-o-truck` | 60 |
| Distributor Intelligence | `/reports/distributor-intelligence` | `heroicon-o-building-office` | 41 |
| Procurement | `/reports/procurement` | `heroicon-o-truck` | 50 |
| Credit | `/reports/credit` | `heroicon-o-credit-card` | 60 |
| Forecasting | `/reports/forecast` | `heroicon-o-arrow-trending-up` | 60 |
| API Analytics | `/reports/api-analytics` | `heroicon-o-signal` | 70 |
| Operational Monitoring | `/reports/operational-monitoring` | `heroicon-o-server-stack` | 71 |
| Search Analytics | `/search-analytics` | `heroicon-o-magnifying-glass` | 10 |

## System

| Page | Route | Icon | Sort | Notes |
|------|-------|------|------|-------|
| Settings | `/settings` | `heroicon-o-cog-6-tooth` | 1 | custom dashboard page |
| System Information | `/system-information` | `heroicon-o-server` | 2 | — |
| Notification Dashboard | `/notification-dashboard` | `heroicon-o-bell-alert` | 79 | — |
| Notification Templates | `/notification-templates` | `heroicon-o-bell` | 80 | — |
| Notification Deliveries | `/notification-deliveries` | `heroicon-o-paper-airplane` | 81 | — |
| Announcements | `/announcements` | `heroicon-o-megaphone` | 82 | — |
| Settings (resource) | `/settings` | `heroicon-o-cog-6-tooth` | 99 | `$shouldRegisterNavigation = false` |

## E-Commerce

| Page | Route | Icon | Sort |
|------|-------|------|------|
| Orders | `/orders` | `heroicon-o-shopping-cart` | 1 |
| Reviews | `/reviews` | `heroicon-o-star` | 3 |

## Content

This group is **not declared** in `AdminPanelProvider` but is used by Blog resources.

| Page | Route | Icon | Sort |
|------|-------|------|------|
| Blog Posts | `/blog-posts` | `heroicon-o-newspaper` | 1 |
| Blog Categories | `/blog-categories` | `heroicon-o-folder` | 2 |
| Blog Tags | `/blog-tags` | `heroicon-o-tag` | 3 |
| Blog Authors | `/blog-authors` | `heroicon-o-user` | 4 |

## Observations

- **Missing group declaration:** `Content` is not listed in `AdminPanelProvider::navigationGroups()`; Filament auto-creates it.
- **Group overlap:** `Distributors` appears both as a top-level group and under `Operations`.
- **Sort inconsistencies:** `Distributor Intelligence` (sort 41) is placed before `Inventory` (40) in the reports group due to numeric ordering.
- **Duplicate concept:** `Quotations` (Distributor group) and `Quote Requests` (Requests group) are separate resources with overlapping terminology.
- **Legacy group:** `E-Commerce` still contains Orders and Reviews, which are largely unused after the B2B transformation.
