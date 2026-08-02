# Phase 13.2S — Responsive Validation

## Breakpoints Covered
- 320px, 375px, 390px, 414px, 768px, 1024px, 1280px, 1440px, 1920px

## Behaviour by Breakpoint

### < 640px (Mobile)
- KPI grid: 1 column
- Sidebar: hidden off-canvas, toggled via hamburger menu
- Header search: hidden
- Date selector: hidden
- User menu: icon only
- Hero quick actions: full-width stacked buttons

### 640px – 1023px (Tablet)
- KPI grid: 2 columns
- Sidebar: hidden off-canvas
- Header search: visible from 768px+
- Date selector: hidden below 1024px

### 1024px – 1279px (Small desktop)
- KPI grid: 3 columns
- Sidebar: visible, collapsible
- Date selector: visible
- Header search: visible

### 1280px+ (Desktop)
- KPI grid: 5 columns
- Sidebar: visible, collapsible
- Three-column bottom grid (Tasks / Notifications / Calendar)

## Key Responsive Rules
- `.vestra-crm__main` margin-left follows sidebar width on desktop
- `.vestra-sidebar--collapsed` reduces width to `80px`
- Collapse trigger hidden on mobile
- Mobile overlay covers content when sidebar is open
- Tooltips appear for collapsed sidebar items on hover

## Notes
- Visual browser testing at each breakpoint should be performed after deployment.
- No layout regressions were introduced in the build output.
