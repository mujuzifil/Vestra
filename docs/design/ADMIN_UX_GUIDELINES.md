# VESTRA Administration UX Guidelines

## 1. Administrator Workflows

### 1.1 Product Management

**Goal:** Maintain an accurate, appealing product catalogue.

**Typical Journey:**
1. View product list to identify updates.
2. Create or edit a product.
3. Upload images, set category, pricing, stock.
4. Add SEO metadata.
5. Save and verify in list.

**Pain Points:**
- Slow form load with many images.
- Unclear image requirements.
- Repeated scrolling between sections.

**Optimisations:**
- Collapsible media and SEO sections by default.
- Image upload with clear constraints.
- Inline validation on SKU uniqueness.
- Duplicate product action for similar items.

**Recommended Layout:**
- Two-column form for basic fields.
- Full-width sections for descriptions, media, SEO.

---

### 1.2 Order Fulfilment

**Goal:** Process orders accurately from payment to delivery.

**Typical Journey:**
1. Dashboard alert shows pending orders.
2. Open orders list, filter by status.
3. View order details.
4. Confirm payment, update status, add tracking.
5. Customer receives notification automatically.

**Pain Points:**
- Too many clicks to update status.
- Missing order context (items, customer history).
- Risk of accidental status changes.

**Optimisations:**
- Status actions in table row with confirmation.
- Order detail page shows items, customer, history, shipping.
- Bulk status update for selected orders.
- Clear status transition rules.

**Recommended Layout:**
- Orders list with filters and row actions.
- Order detail: summary card + tabs for items, history, notes.

---

### 1.3 Customer Management

**Goal:** Understand customers and support their orders.

**Typical Journey:**
1. Search for customer.
2. View customer profile.
3. Review order history and addresses.
4. Respond to message or review.

**Pain Points:**
- Scattered customer information.
- No lifetime value visibility.

**Optimisations:**
- Customer detail page combines profile, stats, addresses, orders.
- Direct links from customer to their orders and reviews.

**Recommended Layout:**
- Customer list with order count.
- Customer view with infolist sections.

---

### 1.4 Reviews Moderation

**Goal:** Maintain quality and trust of customer reviews.

**Typical Journey:**
1. Dashboard shows reviews awaiting moderation.
2. Open reviews list filtered by pending.
3. Read review content.
4. Approve or reject.

**Optimisations:**
- Inline approve/reject actions.
- Rating and status badges.
- Filter by rating and status.

---

### 1.5 Inventory Monitoring

**Goal:** Avoid stockouts and overstocks.

**Typical Journey:**
1. Dashboard low-stock widget alerts.
2. Open products list with low-stock filter.
3. Edit product stock quantity.

**Optimisations:**
- Low-stock badge in product list.
- Dashboard widget always visible.
- Stock update inline in product list (future).

---

### 1.6 Settings Management

**Goal:** Control site configuration without developer help.

**Typical Journey:**
1. Open settings list.
2. Search or filter by group.
3. Edit setting value.
4. Save.

**Optimisations:**
- Group filter.
- Appropriate input per type (text, textarea, toggle, image).
- JSON validation with helpful error messages.

---

### 1.7 User & Permission Management

**Goal:** Control who can access and what they can do.

**Typical Journey:**
1. Create administrator or role.
2. Assign roles/permissions.
3. Activate/deactivate user.

**Optimisations:**
- Role-permission matrix view.
- Status toggle with confirmation.
- Prevent self-deactivation.

---

## 2. Interaction Design Standards

### 2.1 Hover

- Provide immediate visual feedback.
- Do not rely on hover alone to reveal critical actions.

### 2.2 Focus

- Visible focus ring on all interactive elements.
- Trap focus inside modals/drawers.
- Return focus to trigger on close.

### 2.3 Loading

- Show loading state within 200ms of action.
- Skeletons for content areas.
- Spinners for buttons and small actions.

### 2.4 Saving

- Disable submit button while saving.
- Show success toast on completion.
- Return to list or stay on form based on action.

### 2.5 Deleting

- Always require confirmation.
- Describe consequences (e.g., "This will permanently delete the product and its images.").
- Distinguish delete from archive/disable.

### 2.6 Confirmation

- Use modal for irreversible actions.
- Use inline confirmation for low-risk actions (toggle status).

### 2.7 Undo

- Provide undo for non-destructive bulk actions where feasible.
- Example: undo bulk status change within 5 seconds.

### 2.8 Validation

- Inline validation on blur.
- Form-level errors in alert banner.
- Error fields scroll into view.

### 2.9 Notifications

- Success: brief, specific ("Product saved.")
- Error: descriptive with next step ("Could not save product. SKU already exists.")
- Info: relevant to current context.

---

## 3. Responsive Design

### 3.1 Desktop (≥1024px)

- Full sidebar, multi-column layouts.
- Tables display all columns.
- Hover-activated actions visible.

### 3.2 Laptop (1024px–1279px)

- Same as desktop but tighter spacing.
- Consider collapsing less-used sidebar groups.

### 3.3 Tablet (768px–1023px)

- Icon-only sidebar.
- Forms single-column.
- Tables hide non-essential columns.
- Drawer for filters.

### 3.4 Mobile (<768px)

- Hidden sidebar, hamburger menu.
- Single-column layouts.
- Floating action button for primary create.
- Tables convert to cards.
- Bottom sheet for actions.

---

## 4. Accessibility

### 4.1 Contrast

- Minimum 4.5:1 for body text.
- Minimum 3:1 for large text and UI components.
- Test colour combinations in both light and dark contexts.

### 4.2 Keyboard Navigation

- Tab order follows visual order.
- Shortcuts documented (e.g., `Ctrl+K` search).
- Esc closes modals/drawers.

### 4.3 ARIA

- Landmarks: `nav`, `main`, `aside`.
- Live regions for notifications.
- Labels for icon-only buttons.

### 4.4 Screen Readers

- Descriptive link text (avoid "click here").
- Table captions and headers.
- Alt text for product images.

### 4.5 Motion

- Respect `prefers-reduced-motion`.
- Avoid flashing or rapid animations.

### 4.6 Colour Blindness

- Do not rely on colour alone for status.
- Pair badges with text labels.
- Use icons plus colour for alerts.

### 4.7 WCAG Target

Aim for **WCAG 2.1 Level AA** compliance across the admin platform.

---

## 5. Writing & Microcopy

### 5.1 Buttons

- Use verb-first labels: "Save Product", "Send Reply", "Mark as Shipped".
- Avoid vague labels like "Submit" or "OK".

### 5.2 Empty States

- Title: "No orders yet"
- Description: "Orders will appear here once customers start purchasing."
- Action: "View Products"

### 5.3 Errors

- State what happened and how to fix it.
- Example: "The SKU must be unique. 'ESC-001' is already in use."

### 5.4 Success Messages

- Be specific: "Order #INV-1234 marked as shipped."
- Avoid excessive enthusiasm.

---

## 6. Future UX Improvements

- Bulk edit for products and orders.
- Advanced filter builder.
- Saved views per user.
- Keyboard shortcuts cheat sheet.
- Inline editing in tables.
- Activity feed with filtering.
