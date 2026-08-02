# Account Portal Redesign Report — Phase 12A.2

## Objective

Transform the authenticated customer account portal from a commerce-oriented experience into a corporate B2B business portal aligned with the VESTRA® corporate design system.

## Scope

Frontend-only redesign. No backend changes were made in this phase.

## Previous State

The account portal used e-commerce language and workflows:

- Dashboard displayed "Total Orders", "Pending Payment", "Processing", "Completed", "Cancelled".
- Sidebar contained Orders, Reviews, Wishlist, Recently Viewed, Photo, Password.
- Quick actions included Track Order and View Orders.
- Recent Orders section displayed invoices, payment buttons, and tracking links.

## New State

The portal now presents a business workspace:

- Dashboard title changed to **Business Portal**.
- Sidebar navigation focuses on business activities: My Quotes, Distributor Application, Saved Products, Documents, Support, Company Information.
- Dashboard displays business activity summary cards: Quote Requests, Distributor Application Status, Saved Products, Documents, Support Enquiries, Recent Notifications.
- Quick actions direct users to Request a Quote, Browse Products, Become a Distributor, Contact Sales, Update Business Profile, View Documents, Support Centre, Update Profile.
- Recent Activity Timeline uses lucide-react icons and describes account events.
- Saved Addresses preview supports business delivery planning.

## Files Changed

- `frontend/components/layout/customer-layout.tsx`
- `frontend/app/account/account-page-client.tsx`
- `frontend/app/account/page.tsx`
- `frontend/app/account/security/security-page-client.tsx`
- `frontend/app/account/profile/profile-page-client.tsx`
- `frontend/app/account/preferences/preferences-page-client.tsx`
- `frontend/app/account/addresses/addresses-page-client.tsx`
- `frontend/app/account/addresses/page.tsx`
- `frontend/app/account/activity/activity-page-client.tsx`
- `frontend/app/account/activity/page.tsx`
- `frontend/app/account/delete/delete-page-client.tsx`

## Files Created

- `frontend/app/account/quotes/page.tsx`
- `frontend/app/account/quotes/quotes-page-client.tsx`
- `frontend/app/account/quotes/[id]/page.tsx`
- `frontend/app/account/quotes/[id]/quote-detail-client.tsx`
- `frontend/app/account/distributor/page.tsx`
- `frontend/app/account/distributor/distributor-page-client.tsx`
- `frontend/app/account/saved-products/page.tsx`
- `frontend/app/account/saved-products/saved-products-page-client.tsx`
- `frontend/app/account/documents/page.tsx`
- `frontend/app/account/documents/documents-page-client.tsx`
- `frontend/app/account/support/page.tsx`
- `frontend/app/account/support/support-page-client.tsx`
- `frontend/app/account/company/page.tsx`
- `frontend/app/account/company/company-page-client.tsx`
- `frontend/hooks/use-saved-items.ts`

## Files Redirected

Legacy commerce routes now redirect to corporate destinations:

| Old Route | Redirect Target |
|---|---|
| `/account/settings` | `/account/profile` |
| `/account/orders` | `/account/quotes` |
| `/account/orders/[id]` | `/account/quotes` |
| `/account/reviews` | `/account` |
| `/account/wishlist` | `/account/saved-products` |
| `/account/recently-viewed` | `/account` |
| `/account/profile/photo` | `/account/profile` |
| `/account/password` | `/account/security` |

## Design System Compliance

- Uses Phase 10 design tokens: `bg-surface-card`, `bg-surface-page`, `border-border-default`, `rounded-[20px]`, `shadow-sm`, `text-text-heading`, `text-muted`, `text-body`.
- Primary actions use `bg-secondary-600`.
- All icons are from `lucide-react`.
- `Container` and `PageHero` shared components reused.

## Data Handling

- Real APIs are used where available: auth profile, addresses, activity, distributor application status, notifications, saved items.
- Pages without a customer API show honest empty states: Quote Requests, Documents, Support Enquiries, Company Details.
- No fabricated data is displayed.
