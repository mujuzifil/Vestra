# Component Report — Phase 12A.2

## New Components

### Dashboard Components

Located in `frontend/app/account/account-page-client.tsx`:

- `StatCard` — Reusable statistic card for the business activity summary. Accepts label, value, optional note, icon, and color.
- `ActivityIcon` — Maps activity types to lucide-react icons.

### Distributor Page Components

Located in `frontend/app/account/distributor/distributor-page-client.tsx`:

- `StatusBadge` — Renders pending/approved/rejected status with color and icon.
- `Timeline` — Visual application progress timeline.
- `NoApplicationState` — Benefits grid and Apply Now CTA.
- `PendingState` — Review timeline and reference number.
- `ApprovedState` — Approval confirmation and dashboard CTA.
- `RejectedState` — Rejection message with admin notes and contact CTA.

### Saved Products Components

Located in `frontend/app/account/saved-products/saved-products-page-client.tsx`:

- `ProductCard` — Card displaying saved product image, category, applications, and CTAs.

## Reused Shared Components

- `Container` — Page width wrapper.
- `PageHero` — Page title, subtitle, breadcrumb.
- `CustomerLayout` — Sidebar layout wrapper.

## New Hooks

- `useSavedItems` — Query hook for `/auth/saved-for-later`.
- `useAddSavedItem` — Mutation to add a saved product.
- `useRemoveSavedItem` — Mutation to remove a saved product.

## Removed Components

The following client components were deleted as part of redirecting legacy routes:

- `frontend/app/account/orders/orders-page-client.tsx`
- `frontend/app/account/orders/[id]/order-detail-client.tsx`
- `frontend/app/account/reviews/reviews-page-client.tsx`
- `frontend/app/account/wishlist/wishlist-page-client.tsx`
- `frontend/app/account/recently-viewed/recently-viewed-page-client.tsx`
- `frontend/app/account/profile/photo/photo-page-client.tsx`
- `frontend/app/account/password/password-page-client.tsx`
- `frontend/app/account/settings/settings-page-client.tsx`

## Component Patterns

- All new client components are declared with `"use client"`.
- Auth redirect guard is consistent across pages:
  ```ts
  useEffect(() => {
    if (!authLoading && !isAuthenticated) router.push("/auth/login");
  }, [authLoading, isAuthenticated, router]);
  ```
- Loading states use a centered `Loader2` spinner.
- Empty states include an icon, heading, description, and primary CTA.
