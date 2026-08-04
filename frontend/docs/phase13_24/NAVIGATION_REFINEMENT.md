# Phase 13.24 — Navigation Refinement

## Sidebar collapse

- Single collapse control in the top header (`vestra-header__collapse-btn`), beside the hamburger.
- Removed sidebar brand-row collapse button and footer “Collapse” control.
- State key: `localStorage.vestra-sidebar-collapsed` (JSON boolean).
- Layout (`crm.blade.php`) and sidebar both listen to `toggle-sidebar-collapse`; layout persists state.
- Collapsed mode: icon-only rail; expanded: labels restored.

## Header chrome

Kept: hamburger, collapse, search field, date filter, user menu.

Removed (no backend): ⌘K badge, notification bell, help icon.

## Logo

- Asset: `public/images/vestra-logo.png` (identical to `frontend/public/assets/images/branding/vestra-logo.png`).
- Removed CSS `filter: brightness(0) invert(1)` so brand colours match the public site.
- Rendering: `object-fit: contain`, fixed height, intrinsic width/height for crisp Retina display.
