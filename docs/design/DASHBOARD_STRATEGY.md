# VESTRA Administration Dashboard Strategy

## 1. Dashboard Purpose

The dashboard is the administrator's daily command centre. It must answer three questions within seconds:

1. What needs my attention now?
2. How is the business performing today?
3. What should I do next?

The design prioritises operational urgency over exhaustive analytics.

---

## 2. Dashboard Layout

```
┌─────────────────────────────────────────────────────────────┐
│  Dashboard                                      [Date Range] │
├─────────────────────────────────────────────────────────────┤
│  [KPI 1]    [KPI 2]    [KPI 3]    [KPI 4]                  │
├─────────────────────────────────────────────────────────────┤
│  Quick Actions                                               │
│  [Create Product] [View Orders] [View Messages] [Settings]  │
├─────────────────────────────────────────────────────────────┤
│  ┌────────────────────────────┐  ┌───────────────────────┐  │
│  │ Revenue Chart              │  │ Orders by Status      │  │
│  │ (line chart, 30 days)      │  │ (doughnut)            │  │
│  └────────────────────────────┘  └───────────────────────┘  │
├─────────────────────────────────────────────────────────────┤
│  ┌────────────────────────────┐  ┌───────────────────────┐  │
│  │ Recent Orders              │  │ Low Stock Products    │  │
│  │ (table, 5 rows)            │  │ (table, 5 rows)       │  │
│  └────────────────────────────┘  └───────────────────────┘  │
├─────────────────────────────────────────────────────────────┤
│  ┌─────────────────────────────────────────────────────────┐│
│  │ Recent Activity Feed                                     ││
│  │ (audit log timeline)                                     ││
│  └─────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────┘
```

---

## 3. KPI Cards

### 3.1 Executive KPIs

| KPI | Value Format | Trend | Context |
|-----|--------------|-------|---------|
| Today's Revenue | `UGX 1,240,000` | vs yesterday | Paid orders today |
| Weekly Revenue | `UGX 8,450,000` | vs last week | Paid orders this week |
| Monthly Revenue | `UGX 34,200,000` | vs last month | Paid orders this month |
| Total Orders (today) | `24` | — | All orders created today |

### 3.2 Operational KPIs

| KPI | Value Format | Alert Threshold | Action |
|-----|--------------|-----------------|--------|
| Pending Orders | `12` | > 5 turns amber | View orders |
| Awaiting Payment | `5` | > 3 turns amber | Follow up |
| Low Stock Products | `3` | > 0 turns red | Restock |
| New Contact Messages | `4` | > 0 turns amber | Reply |
| New Reviews to Moderate | `7` | > 0 turns amber | Moderate |

### 3.3 KPI Card Design

- Icon left, value right.
- Label above value.
- Trend pill below value (green positive, red negative, neutral flat).
- Alert KPIs use amber/red icon colour when threshold exceeded.

---

## 4. Quick Actions

A horizontal row of primary action buttons immediately below KPIs:

1. **Create Product** → `/admin/products/create`
2. **View Pending Orders** → `/admin/orders?filter=status:pending`
3. **Reply to Messages** → `/admin/contact-messages?filter=status:new`
4. **Manage Settings** → `/admin/settings`
5. **View Reports** → `/admin/reports` (future)

Guidelines:
- Limit to 5 actions.
- Order by frequency and urgency.
- Use icon + label button style.

---

## 5. Charts

### 5.1 Revenue Trend

- **Type:** Line chart
- **X-axis:** Date (last 30 days)
- **Y-axis:** Revenue (UGX)
- **Series:** Paid revenue
- **Interactions:** Hover shows exact value and date
- **Empty state:** "No revenue in this period"

### 5.2 Orders by Status

- **Type:** Doughnut chart
- **Segments:** Pending, Paid, Processing, Shipped, Delivered, Cancelled, Refunded
- **Colours:** VESTRA semantic palette
- **Centre label:** Total orders

### 5.3 Top Products

- **Type:** Horizontal bar chart
- **Metric:** Units sold or revenue
- **Limit:** Top 5 products
- **Interactions:** Click bar to view product

### 5.4 Chart Accessibility

- Provide data table toggle.
- Use patterns or labels, not colour alone.
- Screen reader summary text above chart.

---

## 6. Table Widgets

### 6.1 Recent Orders

| Column | Width | Format |
|--------|-------|--------|
| Invoice | 20% | link |
| Customer | 25% | text |
| Total | 15% | money |
| Status | 15% | badge |
| Date | 15% | datetime |
| Action | 10% | view icon |

- Limit: 5 rows
- Link to full orders list at bottom

### 6.2 Low Stock Products

| Column | Width | Format |
|--------|-------|--------|
| Product | 40% | link |
| SKU | 20% | text |
| Stock | 15% | badge (red) |
| Price | 15% | money |
| Action | 10% | edit icon |

- Limit: 5 rows
- Link to full products list with low-stock filter

---

## 7. Alerts & Notifications Panel

A compact panel showing the most recent actionable alerts:

- "3 orders are awaiting payment"
- "Stock for Wool & Delicate Fabric Wash is low (5 units)"
- "2 new distributor requests pending review"
- "5 reviews awaiting moderation"

Each alert links to the relevant filtered list.

---

## 8. Recent Activity Feed

A chronological timeline of recent system events:

- Administrator actions (product created, order updated, settings changed)
- Customer events (order placed, review submitted)
- System events (low stock alert, failed payment)

Format:
- Icon indicating event type
- Actor + action + object
- Timestamp (relative, e.g., "5 minutes ago")

---

## 9. Widget Hierarchy & Visual Priority

| Priority | Widget | Reason |
|----------|--------|--------|
| 1 | KPI Cards | Immediate business health |
| 2 | Quick Actions | Task initiation |
| 3 | Charts | Trend understanding |
| 4 | Table Widgets | Operational detail |
| 5 | Alerts | Urgent attention |
| 6 | Activity Feed | Context and audit |

---

## 10. Future Dashboard Enhancements

- Customisable widget layout (drag-and-drop)
- Saved date-range presets
- Real-time WebSocket updates for new orders
- AI-powered recommendations (e.g., "Restock EcoSuit Cleaner")
- Comparative period analytics
- Export dashboard snapshot to PDF

---

## 11. Responsive Dashboard

### Desktop
- 4-column KPI grid
- Charts side-by-side
- Table widgets side-by-side

### Tablet
- 2-column KPI grid
- Charts stack
- Table widgets stack

### Mobile
- 1-column KPI grid (horizontal scroll optional)
- Charts stack full-width
- Tables become card lists
- Quick actions become 2-column grid
