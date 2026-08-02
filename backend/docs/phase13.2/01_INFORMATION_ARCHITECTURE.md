# Phase 13.2 — Information Architecture

## Objective
Replace the Filament resource-driven navigation with a single CRM-first workspace architecture.

## Target Navigation

```
Workspace
  Dashboard
  Tasks
  Notifications
  Activity

Sales
  Companies
  Quotes
  Pipeline
  Opportunities

Distributors
  Applications
  Active Partners
  Territories
  Credit

Customer Success
  Support
  Enquiries
  Feedback

Products
  Products
  Categories
  Inventory
  Warehouses

Operations
  Suppliers
  Purchase Orders
  Workflows

Marketing
  Blog
  Media
  SEO

Analytics
  Executive
  Sales
  Operations
  Finance

Communications
  Templates
  Notifications
  Campaigns

Administration
  Staff
  Roles
  Settings
  Integrations
  Audit
```

## Group Ordering
Groups are registered in `AdminPanelProvider` in the exact order above. Filament renders them in that order.

## Sorting Within Groups
Sort values are assigned per group to control item order:

- Workspace: Dashboard (-2), Tasks (2), Notifications (3), Activity (4)
- Sales: Companies (1), Quotes (2), Pipeline (3), Opportunities (4)
- Distributors: Applications (1), Active Partners (2), Territories (3), Credit (4)
- Customer Success: Enquiries (1), Feedback (2), Support (placeholder)
- Products: Products (1), Categories (2), Inventory (3), Warehouses (4)
- Operations: Suppliers (1), Purchase Orders (2), Workflows (3)
- Marketing: Blog (1), Media (2), SEO (3)
- Analytics: Executive (1), Sales (2), Operations (3), Finance (4)
- Communications: Templates (1), Notifications (2), Campaigns (3)
- Administration: Staff (1), Roles (2), Settings (3), Integrations (4), Audit (5)
