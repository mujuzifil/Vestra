# VESTRA Administration Component Library

This document defines every reusable component in the VESTRA Administration Platform. Each component includes purpose, variants, usage guidelines, and accessibility requirements.

---

## 1. Buttons

### 1.1 Purpose

Trigger actions, submit forms, navigate to primary flows.

### 1.2 Variants

| Variant | Visual | Usage |
|---------|--------|-------|
| Primary | Navy background, white text | Main action on a page or form |
| Secondary | White background, neutral border | Alternative action, cancel adjacent to primary |
| Danger | Red background, white text | Destructive actions (delete, cancel order) |
| Ghost | Transparent, navy text | Low-emphasis actions inside tables/cards |
| Icon | Icon only, transparent | Toolbar actions, compact UI |

### 1.3 Guidelines

- Only one Primary button per section.
- Danger buttons require confirmation.
- Disabled state uses `opacity: 0.5` and `cursor: not-allowed`.
- Loading state shows spinner and disables click.

### 1.4 Accessibility

- Minimum touch target: `44×44px`.
- Focus ring visible on keyboard navigation.
- `aria-label` for icon-only buttons.

---

## 2. Inputs

### 2.1 Text Input

**Purpose:** Single-line text entry.

**States:** default, hover, focus, disabled, error, read-only.

**Guidelines:**
- Label above input, aligned left.
- Helper text below for format hints.
- Error message replaces helper text when invalid.

### 2.2 Textarea

**Purpose:** Multi-line text entry.

**Guidelines:**
- Minimum height: `80px`.
- Auto-resize optional for long content.
- JSON fields use monospace font.

### 2.3 Select / Dropdown

**Purpose:** Choose one option from a list.

**Guidelines:**
- Use for 5–20 options.
- Searchable when options exceed 10.
- Group related options with section headers.

### 2.4 Checkbox & Switch

**Purpose:** Binary choices.

**Guidelines:**
- Checkbox for lists and multi-select.
- Switch for toggle settings (active/inactive, featured).
- Switch labels describe current state clearly.

### 2.5 Date Picker

**Purpose:** Select dates and date ranges.

**Guidelines:**
- Use native date input on mobile where appropriate.
- Date range picker shows start and end clearly.
- Disable future dates when selecting order dates.

### 2.6 Rich Editor

**Purpose:** Formatted long-form content (product descriptions, emails).

**Guidelines:**
- Toolbar limited to essential formatting.
- Support bold, italic, lists, links, headings.
- Avoid full HTML source editing for non-technical users.

### 2.7 File Upload

**Purpose:** Upload images, documents, CSVs.

**Guidelines:**
- Show accepted file types and max size.
- Display preview for images.
- Show upload progress.
- Allow drag-and-drop.

---

## 3. Data Components

### 3.1 Tables

**Purpose:** Display tabular data with sorting, filtering, pagination.

**Structure:**
- Header row: column labels, sort icons.
- Body rows: data cells, actions.
- Footer: pagination, row count.

**Guidelines:**
- Align text left, numbers right.
- Use badges for status columns.
- Truncate long text with tooltip on hover.
- Sticky header on scroll.

### 3.2 Data Cards

**Purpose:** Highlight a single record summary.

**Guidelines:**
- One primary metric or status.
- Supporting metadata below.
- Action menu top-right.

### 3.3 KPI Cards

**Purpose:** Display a key metric with context.

**Structure:**
- Label (caption)
- Value (display size)
- Trend indicator (optional)
- Icon (optional)

**Guidelines:**
- Use for dashboard stats.
- Group related KPIs visually.

### 3.4 Charts

**Purpose:** Visualise trends and distributions.

**Types:**
- Line chart: revenue over time
- Bar chart: sales by category
- Doughnut chart: order status distribution

**Guidelines:**
- Always label axes.
- Use VESTRA colour palette.
- Provide data table alternative for accessibility.

---

## 4. Feedback Components

### 4.1 Badges

**Purpose:** Indicate status or category.

**Variants:** success, warning, danger, info, neutral.

**Guidelines:**
- One badge per status cell.
- Use uppercase text for status badges.
- Colour alone must not convey meaning; text label required.

### 4.2 Tags

**Purpose:** Display multiple attributes or filters.

**Guidelines:**
- Removable for filter tags.
- Neutral colour by default.

### 4.3 Alerts / Banners

**Purpose:** Communicate important system or page-level messages.

**Variants:** info, success, warning, error.

**Guidelines:**
- Dismissible unless critical.
- Title + description format.
- Error alerts include recovery action.

### 4.4 Notifications (Toasts)

**Purpose:** Confirm action completion or warn of async result.

**Guidelines:**
- Auto-dismiss after 5 seconds.
- Persistent for errors until dismissed.
- Include undo action where applicable.

### 4.5 Progress

**Purpose:** Show completion status.

**Guidelines:**
- Linear for multi-step flows.
- Circular for loading indicators.
- Always include label or percentage.

### 4.6 Loading & Skeletons

**Purpose:** Indicate content is loading.

**Guidelines:**
- Skeletons match final layout shape.
- Spinners for buttons and small areas.
- Full-page loader only for initial app load.

### 4.7 Empty States

**Purpose:** Guide users when no data exists.

**Structure:**
- Illustration or icon
- Title
- Description
- Primary action

### 4.8 Error States

**Purpose:** Recover from failures.

**Structure:**
- Error icon
- Title
- Description
- Retry action
- Link to support if persistent

---

## 5. Overlay Components

### 5.1 Modals / Dialogs

**Purpose:** Capture attention for confirmations or focused tasks.

**Guidelines:**
- Overlay backdrop `opacity: 0.65`.
- Close on Escape, click outside, or explicit close.
- Max-width based on content: small `400px`, medium `560px`, large `720px`.

### 5.2 Drawers

**Purpose:** Show detail or secondary forms without leaving the page.

**Guidelines:**
- Slide in from right.
- Width: `400px` default, `560px` wide.
- Header with title and close action.

### 5.3 Tabs

**Purpose:** Organise content into sections within a single view.

**Guidelines:**
- Use for 2–6 sections.
- Active tab has bottom border indicator.
- Avoid nested tabs.

### 5.4 Accordions

**Purpose:** Progressive disclosure of sections.

**Guidelines:**
- One panel open at a time by default.
- Use for FAQs, advanced filters, detailed settings.

### 5.5 Dropdown Menus

**Purpose:** Reveal related actions.

**Guidelines:**
- Trigger by button click.
- Group related items with dividers.
- Keyboard navigable.

---

## 6. Navigation Components

### 6.1 Sidebar Item

**States:** default, hover, active, collapsed.

**Guidelines:**
- Active item has left border accent and lighter background.
- Group items collapse/expand.

### 6.2 Breadcrumbs

**Purpose:** Show hierarchy and allow upward navigation.

**Guidelines:**
- Separator: `>` or `/`.
- Current page is non-interactive.

### 6.3 Pagination

**Purpose:** Navigate large datasets.

**Guidelines:**
- Show previous/next, first/last, and page numbers.
- Page size selector: 10, 25, 50, 100.
- Display total count.

### 6.4 Command Palette / Global Search

**Purpose:** Quick navigation and action triggering.

**Guidelines:**
- Trigger: `Ctrl+K`.
- Group results by module.
- Show keyboard shortcut hints.

---

## 7. Form Patterns

### 7.1 Form Section

**Purpose:** Group related fields visually.

**Guidelines:**
- Card container with title.
- Two-column layout for short fields.
- Full-width for rich text and uploads.

### 7.2 Inline Validation

**Purpose:** Provide immediate feedback.

**Guidelines:**
- Validate on blur for simple rules.
- Validate on submit for complex rules.
- Error text below field.

### 7.3 Form Actions

**Purpose:** Primary and secondary actions at the end of a form.

**Guidelines:**
- Primary action left-aligned on the right group.
- Cancel is secondary.
- Save shortcut: `Ctrl+Enter` optional.

---

## 8. Accessibility Checklist per Component

- [ ] Sufficient colour contrast (WCAG AA 4.5:1 for text)
- [ ] Keyboard operable
- [ ] Focus indicator visible
- [ ] ARIA labels for icon-only controls
- [ ] Screen reader announcements for dynamic content
- [ ] Reduced-motion support for animations
- [ ] Error states communicated textually
