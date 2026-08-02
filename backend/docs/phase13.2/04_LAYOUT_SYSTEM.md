# Phase 13.2 — Layout System

## CRM Application Shell

The admin portal uses a custom application shell defined in:

- `backend/resources/views/filament/layouts/crm.blade.php`
- `backend/resources/css/filament/admin/components/crm-shell.css`

### Shell Structure

```
.vestra-crm
├── .vestra-sidebar (fixed left navigation)
│   ├── brand lockup (logo + Admin Portal)
│   ├── navigation groups
│   └── footer (Settings, Collapse)
├── .vestra-crm__overlay (mobile backdrop)
└── .vestra-crm__main
    ├── .vestra-header (sticky top bar)
    └── .vestra-content-shell (page content)
```

## Sidebar Behavior

- Expanded width: `280px`
- Collapsed width: `80px`
- State persisted in `localStorage` key `vestra-sidebar-collapsed`
- Mobile: hidden off-canvas drawer toggled via header hamburger
- Desktop: collapsible via brand-row button or footer Collapse action
- Tooltips appear on collapsed navigation items

## Header

- Sticky at top of main content
- Left: mobile menu toggle, desktop sidebar collapse toggle
- Center: global search input
- Right: date selector, notifications, help, user menu

## Content Shell

- Max width: `1600px`
- Responsive padding across breakpoints
- All new placeholder pages extend this layout

## Page Template

New placeholder pages extend the CRM layout and render:

```blade
<div class="vestra-workspace">
    <section class="vestra-workspace__hero">
        <div>
            <h1 class="vestra-workspace__title">Page Title</h1>
            <p class="vestra-workspace__welcome">Subtitle.</p>
        </div>
    </section>
    <x-admin.empty-state ... />
</div>
```

This template becomes the standard for all future admin pages.
