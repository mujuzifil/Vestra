# Phase 13.15 — Enquiries Workspace: Frontend Overview

## Purpose

The Enquiries Workspace frontend provides a full CRM-style interface for the **Customer Success** team to handle incoming contact form submissions. It replaces the legacy Filament resource table with a purpose-built Livewire page consistent with the Applications, Companies, and Partners workspaces.

## Page Layout

```
┌────────────────────────────────────────────────────────────────┐
│  Page Header                                                    │
│  [Title: Enquiries]  [Search input]   [Refresh] [Export ▼]    │
├────────────────────────────────────────────────────────────────┤
│  KPI Cards (5 columns)                                          │
│  [Total] [New] [In Progress] [Resolved] [Unassigned]           │
├────────────────────────────────────────────────────────────────┤
│  Filter Bar                                                     │
│  [Status ▼] [Type ▼] [Priority ▼] [Source ▼] [Assigned To ▼] │
│  [Received ▼]                                      [Reset ×]   │
├────────────────────────────────────────────────────────────────┤
│  Enquiry Table                                                  │
│  Sender | Subject | Type | Priority | Status | Assigned |      │
│  Read | Replied | Received | Actions                           │
│  ─────────────────────────────────────────────────────────────│
│  ... rows ...                                                   │
├────────────────────────────────────────────────────────────────┤
│  Pagination bar                                                 │
└────────────────────────────────────────────────────────────────┘
```

When a row is clicked, a **right-side detail drawer** slides in with:
- Contact info, subject & message, attachments
- Reply textarea + Send Reply action
- Assign administrator dropdown
- Status update buttons
- Internal notes textarea

## Blade Component Tree

```
filament/pages/customer-success/enquiries.blade.php
  x-enquiries.page-header
  x-enquiries.kpi-cards
    x-admin.kpi-card (per card)
  x-enquiries.filter-bar
  x-enquiries.enquiry-table
    x-enquiries.enquiry-row (per row)
      x-enquiries.status-badge
      x-enquiries.priority-badge
  x-enquiries.pagination
  x-enquiries.empty-state
  x-enquiries.detail-drawer
    x-enquiries.status-badge
    x-enquiries.priority-badge
```

## Key UX Behaviours

- Unread enquiries are visually distinguished with a light primary background tint.
- Opening the detail drawer auto-marks the enquiry as read.
- The reply textarea is disabled after a reply has been sent (shows sent reply as a blockquote).
- All mutations are Gate-protected; non-admins see a 403 page.
- Filters persist in the URL via `#[Url]` Livewire attributes — shareable links work.
- Escape key closes the detail drawer.
