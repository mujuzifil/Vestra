# Navigation Architecture Report — Phase 12A.2

## Sidebar Navigation

The `CustomerSidebar` in `frontend/components/layout/customer-layout.tsx` now exposes a single corporate navigation structure.

### Navigation Items

| Label | Route | Icon |
|---|---|---|
| Dashboard | `/account` | LayoutDashboard |
| My Quotes | `/account/quotes` | FileText |
| Distributor Application | `/account/distributor` | Handshake |
| Saved Products | `/account/saved-products` | Bookmark |
| Documents | `/account/documents` | FolderOpen |
| Support | `/account/support` | HeadphonesIcon |
| Company Information | `/account/company` | Building2 |
| Addresses | `/account/addresses` | MapPin |
| Profile | `/account/profile` | User |
| Security | `/account/security` | Shield |
| Preferences | `/account/preferences` | SlidersHorizontal |
| Activity | `/account/activity` | History |
| Notifications | `/notifications` | Bell |

### Removed Navigation Items

- Orders
- Reviews
- Wishlist
- Recently Viewed
- Photo
- Password
- Settings

### Footer Area

The previous "Become a Distributor" footer link was removed because distributor functionality is now a top-level sidebar item.

### Branding

Sidebar badge changed from `Account` to `Business Portal`.

## Redirect Map

Deprecated public account routes now perform server-side redirects:

| Source | Destination |
|---|---|
| `/account/settings` | `/account/profile` |
| `/account/orders` | `/account/quotes` |
| `/account/orders/[id]` | `/account/quotes` |
| `/account/reviews` | `/account` |
| `/account/wishlist` | `/account/saved-products` |
| `/account/recently-viewed` | `/account` |
| `/account/profile/photo` | `/account/profile` |
| `/account/password` | `/account/security` |

## Active State Logic

Active state uses exact match or prefix match:

```ts
const isActive = pathname === item.href || pathname.startsWith(`${item.href}/`);
```

This ensures nested routes such as `/account/quotes/123` highlight the My Quotes item.

## Accessibility

- Each link has `aria-current={isActive ? "page" : undefined}`.
- Mobile drawer has open/close buttons with `aria-label`.
- Mobile drawer includes a backdrop with `aria-hidden="true"`.
