# Performance Report — Phase 13.2R

## Caching
- KPI counts cached for 5 minutes.
- Chart data cached for 1 hour keyed by period.
- Recent activity cached for 5 minutes.

## Database
- Recent activity eager-loads the `user` relationship.
- No N+1 queries introduced.
- Notifications use a single query for items and a separate count query.

## Assets
- Chart.js is bundled as a separate Vite chunk (`dashboard-chart-*.js`, ~153 kB).
- Theme CSS grew to ~158 kB due to the new CRM shell styles.
- Widget lazy-loading removed because the dashboard no longer uses widgets; sections render inline.

## Build Output
```
public/build/assets/theme-CP0nlg4u.css           158.22 kB │ gzip: 26.13 kB
public/build/assets/dashboard-chart-CRxELd3n.js  153.52 kB │ gzip: 54.00 kB
public/build/assets/app-CIomGrQN.js               46.16 kB │ gzip: 17.79 kB
```

## Notes
- All dashboard queries remain scoped to the admin's permissions via existing policies.
- The chart module is loaded only on the dashboard page.
