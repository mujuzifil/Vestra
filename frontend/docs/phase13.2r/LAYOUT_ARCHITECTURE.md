# Layout Architecture — Phase 13.2R

## CRM Shell
```
<html>
  <body>
    <div class="vestra-crm">
      <aside class="vestra-sidebar"> ... </aside>
      <div class="vestra-crm__main">
        <header class="vestra-header"> ... </header>
        <main class="vestra-content-shell">
          <div class="vestra-content-shell__container">
            {{ page content }}
          </div>
        </main>
      </div>
    </div>
  </body>
</html>
```

## Sidebar
- Fixed left, 280px wide on desktop.
- Collapsible drawer on mobile.
- Brand, grouped navigation, user footer.

## Header
- Sticky top, 72px tall.
- Mobile menu toggle, global search, date selector, notifications, help, user menu.

## Content Shell
- Max width 1600px, centered.
- Responsive padding.

## Dashboard Grid
- KPI grid: 1 / 2 / 3 / 5 columns.
- Chart + Activity: 1 column mobile, 2:1 desktop.
- Tasks / Notifications / Calendar: 1 / 2 / 3 columns.
