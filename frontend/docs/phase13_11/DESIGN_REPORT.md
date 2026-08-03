# Phase 13.11 — Design Report

## Visual reference

`Active_partners.png` at the repository root informed layout, KPI density, table density, and filter placement. Data values shown in the mock (e.g. "128 partners", "Top Performing Territory") are illustrative only — the implementation renders exclusively live, database-backed figures and intentionally omits any KPI without a real aggregation source (see `backend/docs/phase13_11/DATA_MAPPING.md`).

## Design system alignment

Reuses Workspace tokens and patterns already established by the Companies/Quotes workspaces:

- `.vestra-workspace` hero / title / welcome / sections
- `.vestra-kpi-grid--5` + `x-admin.kpi-card`
- Card-wrapped table with filter bar
- Right-side read-only detail drawer
- Status badges with semantic colours
- Primary blue actions consistent with Companies/Quotes

## Status presentation

| Status | Colour token |
|--------|----------------|
| Active | success |
| Suspended | danger |

## Credit utilization bar

A slim progress bar communicates `creditAccount->utilizationPercentage()` at a glance, colour-shifting from success → warning → danger as utilization crosses 70% and 90% thresholds — a real-data equivalent of the "Credit Utilization" column in the reference mock.

## Empty states

Premium empty copy renders when no partners exist or filters return nothing. No sample/placeholder rows are ever rendered.

## Deliberately omitted from the reference mock

- "Top Performing Territory" KPI card — no live territory-performance aggregation exists yet (deferred to Phase 13.12+).
- "Partner Performance" button — no such route exists in the application yet.
- Map view toggle and donut/leaderboard side panels — out of scope for this phase's read/filter/export workspace.
