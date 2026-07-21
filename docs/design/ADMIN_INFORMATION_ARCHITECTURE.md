# VESTRA Administration Information Architecture

## 1. Navigation Structure

### 1.1 Primary Sidebar Navigation

The sidebar is the primary way administrators move between modules. Items are grouped by function and ordered by frequency of use.

```
Dashboard
│
├── E-Commerce
│   ├── Orders
│   ├── Customers
│   └── Reviews
│
├── Catalog
│   ├── Products
│   └── Categories
│
├── Requests
│   ├── Contact Messages
│   ├── Customer Feedback
│   └── Distributor Requests
│
├── Reports
│   ├── Sales Overview
│   ├── Inventory Report
│   └── Customer Insights
│
├── Administration
│   ├── Administrators
│   ├── Roles
│   └── Permissions
│
└── System
    ├── Settings
    └── Audit Logs
```

### 1.2 Navigation Item Anatomy

Each sidebar item contains:

- **Icon:** `20px`, outline style
- **Label:** Body text, `--neutral-50`
- **Badge:** Optional count badge (e.g., pending orders, low stock)
- **Expand chevron:** For groups with children

### 1.3 Group Ordering Rationale

| Group | Order | Rationale |
|-------|-------|-----------|
| Dashboard | 1 | Entry point; always first |
| E-Commerce | 2 | Core daily operational work |
| Catalog | 3 | Product management, frequent but less than orders |
| Requests | 4 | Customer-facing inbound communication |
| Reports | 5 | Periodic analytical review |
| Administration | 6 | User and permission management |
| System | 7 | Configuration and audit |

---

## 2. Page Layout Patterns

### 2.1 List Page Pattern

Used for: Products, Categories, Orders, Customers, Reviews, Contact Messages, Feedback, Distributor Requests, Administrators, Roles, Permissions.

```
┌─────────────────────────────────────────────────────────────┐
│  Breadcrumbs > Module                                       │
│  Page Title                                    [Primary CTA] │
├─────────────────────────────────────────────────────────────┤
│  [Filters] [Search]                              [Export]    │
├─────────────────────────────────────────────────────────────┤
│  Table                                                        │
│  ─────────────────────────────────────────────────────────  │
│  Name        Status    Date        Actions                   │
│  ─────────────────────────────────────────────────────────  │
│  ...                                                         │
├─────────────────────────────────────────────────────────────┤
│  Showing 1-10 of 124        [Pagination]                     │
└─────────────────────────────────────────────────────────────┘
```

### 2.2 Create / Edit Page Pattern

Used for: Product Create/Edit, Category Create/Edit, Order Edit, Administrator Create/Edit, Role Create/Edit, Permission Create/Edit, Settings Edit.

```
┌─────────────────────────────────────────────────────────────┐
│  Breadcrumbs > Module > Create / Edit                       │
│  Page Title                                     [Save] [Cancel]│
├─────────────────────────────────────────────────────────────┤
│  ┌──────────────┐  ┌──────────────┐                        │
│  │ Section 1    │  │ Section 2    │                        │
│  │              │  │              │                        │
│  └──────────────┘  └──────────────┘                        │
│  ┌────────────────────────────────────┐                    │
│  │ Full-width section                 │                    │
│  └────────────────────────────────────┘                    │
└─────────────────────────────────────────────────────────────┘
```

### 2.3 Detail / View Page Pattern

Used for: Order View, Customer View.

```
┌─────────────────────────────────────────────────────────────┐
│  Breadcrumbs > Module > Detail                              │
│  Title + Status Badge                          [Edit] [Back] │
├─────────────────────────────────────────────────────────────┤
│  ┌──────────────┐  ┌──────────────────────────────────────┐ │
│  │ Summary Card │  │ Detail Sections / Relations          │ │
│  └──────────────┘  └──────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

---

## 3. Global Elements

### 3.1 Top Bar

**Left:**
- Collapse/expand sidebar toggle (tablet/mobile)
- VESTRA logo wordmark

**Centre:**
- Global search input
- Placeholder: "Search orders, products, customers..."
- Shortcut hint: `Ctrl+K`

**Right:**
- Notification bell with unread badge
- Profile avatar + name dropdown
- Dropdown items: Profile, Force Password Change, Logout

### 3.2 Breadcrumbs

- Located below top bar, above page title
- Format: `Dashboard > Module > Action`
- Last item is non-clickable (current page)
- Truncate long names with ellipsis on mobile

### 3.3 Global Search

- Trigger: `Ctrl+K` or clicking search input
- Overlay search modal
- Results grouped by module
- Max 5 results per group
- Highlight matched text

### 3.4 Quick Actions

Available from dashboard and global command palette:

- Create Product
- Create Order
- View Pending Orders
- View Low Stock
- View New Contact Messages

### 3.5 Notification Centre

- Dropdown panel from bell icon
- Recent 10 notifications
- Mark all as read
- Unread badge count
- Notification types: order updates, low stock, new contact message, system alert

### 3.6 Profile Menu

- Name and email
- Role badge
- "Account Settings" link
- "Change Password" link
- Divider
- Logout

---

## 4. Module Page Specifications

### 4.1 Dashboard

- Full-width workspace
- KPI stat cards in 4-column grid
- Recent Orders table widget
- Low Stock table widget
- Quick action buttons

### 4.2 Orders

- List: invoice, customer, total, status, payment status, date
- Filters: status, payment status, date range
- Bulk actions: export, delete
- Row actions: view, edit, mark paid, mark shipped, cancel

### 4.3 Customers

- List: name, email, orders, created date
- Filters: has orders, date range
- Read-only list; view action opens detail page
- Detail: profile, stats, addresses, order history

### 4.4 Products

- List: image, name, category, SKU, price, stock, status
- Filters: status, category, stock level, featured
- Row actions: edit, delete
- Create: basic info, descriptions, media, SEO

### 4.5 Categories

- List: name, slug, sort order, status
- Row actions: edit, delete

### 4.6 Reviews

- List: customer, product, rating, title, status
- Filters: status, rating
- Row actions: approve, reject, edit, delete

### 4.7 Contact Messages

- List: name, email, subject, replied, status
- Filters: status, replied
- Row actions: edit (reply), send reply, delete

### 4.8 Customer Feedback

- List: customer, category, subject, status
- Filters: status, category
- Row actions: mark in progress, mark resolved, edit, delete

### 4.9 Distributor Requests

- List: company, contact, email, status
- Filters: status
- Row actions: edit, delete

### 4.10 Administrators

- List: name, email, roles, status
- Filters: status, role
- Row actions: edit, reset password, toggle status, delete

### 4.11 Roles

- List: name, permissions, created date
- Row actions: edit, delete

### 4.12 Permissions

- List: name, guard
- Row actions: edit, delete

### 4.13 Settings

- List: label, key, group, value
- Filters: group
- Row actions: edit

### 4.14 Reports (Future)

- Sales Overview
- Inventory Report
- Customer Insights

---

## 5. Responsive Behaviour

### 5.1 Desktop (≥1024px)

- Full sidebar expanded
- Multi-column forms and dashboards
- Tables show all columns

### 5.2 Tablet (768px–1023px)

- Sidebar collapsed to icon-only
- Hover/tap expands individual group
- Forms reduce to single column
- Tables hide less critical columns

### 5.3 Mobile (<768px)

- Sidebar hidden; hamburger menu opens overlay drawer
- Single-column layouts
- Tables become card lists
- Floating action button for primary create actions
- Top bar search becomes icon-only

---

## 6. URL Structure

| Module | List | Create | Edit | View |
|--------|------|--------|------|------|
| Dashboard | `/admin` | — | — | — |
| Orders | `/admin/orders` | — | `/admin/orders/{id}/edit` | `/admin/orders/{id}` |
| Customers | `/admin/customers` | — | — | `/admin/customers/{id}` |
| Reviews | `/admin/reviews` | — | `/admin/reviews/{id}/edit` | — |
| Products | `/admin/products` | `/admin/products/create` | `/admin/products/{id}/edit` | — |
| Categories | `/admin/categories` | `/admin/categories/create` | `/admin/categories/{id}/edit` | — |
| Contact Messages | `/admin/contact-messages` | — | `/admin/contact-messages/{id}/edit` | — |
| Feedback | `/admin/customer-feedbacks` | — | `/admin/customer-feedbacks/{id}/edit` | — |
| Distributor Requests | `/admin/distributor-requests` | — | `/admin/distributor-requests/{id}/edit` | — |
| Administrators | `/admin/users` | `/admin/users/create` | `/admin/users/{id}/edit` | — |
| Roles | `/admin/roles` | `/admin/roles/create` | `/admin/roles/{id}/edit` | — |
| Permissions | `/admin/permissions` | `/admin/permissions/create` | `/admin/permissions/{id}/edit` | — |
| Settings | `/admin/settings` | — | `/admin/settings/{id}/edit` | — |
| Reports | `/admin/reports` | — | — | — |
| Audit Logs | `/admin/audit-logs` | — | — | — |

---

## 7. Future Scalability

The information architecture supports adding new modules without reorganising existing navigation:

- New operational modules join **E-Commerce**, **Catalog**, or **Requests** groups.
- New analytical modules join **Reports**.
- New configuration modules join **System** or **Administration**.
- Deep links follow the established `/admin/{module}/{id}/{action}` pattern.
