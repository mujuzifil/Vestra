# Phase 13.7 — Design Report

## Visual reference

`Quotes.png` at repository root guided layout, hierarchy, KPI density, table density and filter placement.

## Design system alignment

Reuses Workspace tokens and patterns from CRM shell:

- `.vestra-workspace` hero / title / welcome / sections
- `.vestra-kpi-grid--5` + `x-admin.kpi-card`
- Card-wrapped table with filter bar
- Right-side detail/edit drawers
- Status/priority badges with semantic colours
- Primary blue actions consistent with Companies

## Status presentation

Existing backend statuses are shown with colour-coded badges:

| Status | Colour token |
|--------|----------------|
| Pending | warning |
| Contacted | info |
| Quoted | primary |
| Approved | success |
| Declined | danger |
| Closed | gray |

## Empty states

Premium empty copy when no quotes exist or filters return nothing. No sample rows are rendered.
