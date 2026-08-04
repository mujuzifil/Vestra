# Phase 13.24 — Dashboard Layout Report

## Structure

1. Hero (welcome + quick actions)
2. KPI grid
3. Sales Overview chart + Recent Activity (2:1 grid)

## Removed cards

- My Tasks
- Notifications
- Calendar

Workspace pages for Tasks / Notifications / Activity remain available via sidebar navigation.

## Recent Activity

- Source: `AuditLog` via `WorkspaceDataService::getRecentActivities()` (live DB only).
- Limit: 6 items.
- Header: “Recent Activity” + “View all” on one line → `/workspace/activity`.
- CSS: equal spacing, title ellipsis, subtitle wrap, timestamps preserved, list max-height to avoid card overflow.

## Quick actions

| Label | Target |
|-------|--------|
| View Quotes | `/sales/quotes` (`QuotesPage`) |
| Applications | `/distributors/applications` (`ApplicationsPage`) |
