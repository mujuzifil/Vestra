# VESTRA Platform Terminology Audit

**Scope:** Identify shopping/e-commerce terminology after the B2B restructure and classify each occurrence.  
**Date:** 2026-08-01

---

## Classification Legend

- **KEEP** — Valid in the new B2B context.
- **REPLACE** — Wording should be updated to match corporate positioning.
- **DELETE** — Obsolete; remove when the surrounding code is removed.
- **BACKEND ONLY** — Internal code/API messages not visible to end users.

---

## Frontend Findings

### User-Facing Pages / Components

| File | Line | Term / Phrase | Classification | Recommended Action |
|------|------|---------------|----------------|--------------------|
| `frontend/app/account/account-page-client.tsx` | 181 | `ShoppingBag` icon for "View Products" | KEEP | Icon is generic; label is B2B |
| `frontend/app/account/orders/orders-page-client.tsx` | 313 | `ShoppingBag` icon for "View Products" | KEEP | Icon is generic; label is B2B |
| `frontend/app/account/orders/[id]/order-detail-client.tsx` | 123 | `heroicon-o-shopping-cart` timeline icon | KEEP | Internal icon name; visually represents an order |
| `frontend/app/account/recently-viewed/...` | 91 | `ShoppingBag` icon for "View Products" | KEEP | Icon is generic |
| `frontend/app/account/reviews/reviews-page-client.tsx` | 39, 221 | `ShoppingBag` icon | KEEP | Generic product/order icon |
| `frontend/app/distributor/dashboard/...` | 142–143 | `ShoppingCart` icon for order stats | KEEP | Generic order icon |
| `frontend/app/distributor/analytics/...` | 60 | `ShoppingCart` icon for order stats | KEEP | Generic order icon |
| `frontend/app/distributor/orders/[id]/...` | 59 | `heroicon-o-shopping-cart` timeline icon | KEEP | Generic order icon |
| `frontend/app/distributor/distributor-page-client.tsx` | 109 | "Retail distribution in supermarkets and shops" | KEEP | Accurate business description |
| `frontend/app/distributor/page.tsx` | 163 | "Retail distribution in supermarkets and shops" | KEEP | Accurate business description |
| `frontend/app/contact/contact-page-client.tsx` | 20 | "Where can I buy VESTRA products?" | KEEP | FAQ uses "buy" in a channel-finding context |
| `frontend/app/contact/contact-page-client.tsx` | 22 | "authorized distributors, select retail stores" | KEEP | Accurate B2B channel language |
| `frontend/components/distributor/distributor-sidebar.tsx` | 42 | `ShoppingCart` icon for "Orders" | KEEP | Generic order icon |
| `frontend/components/layout/customer-layout.tsx` | 36 | `ShoppingBag` icon for "Orders" | KEEP | Generic order icon |

### Obsolete URLs (handled by redirects/robots)

| File | Line | Term | Classification | Recommended Action |
|------|------|------|----------------|--------------------|
| `frontend/app/robots.ts` | 14, 15, 25 | `/checkout`, `/cart` | KEEP | Required for SEO disallows |
| `frontend/next.config.ts` | 96–99 | `/cart`, `/checkout` | KEEP | Required redirects |

**Frontend result:** No shopping terminology remains that needs replacement. All `Add to Cart`, `Buy Now`, `Shopping Cart`, `Checkout`, `Continue Shopping`, etc. were removed in Phase 1.

---

## Backend Findings

### Customer-Facing Emails

| File | Line | Term / Phrase | Classification | Recommended Action |
|------|------|---------------|----------------|--------------------|
| `backend/resources/views/emails/orders/packed.blade.php` | 21 | "Thank you for shopping with VESTRA." | REPLACE | Change to "Thank you for choosing VESTRA." |
| `backend/resources/views/emails/orders/shipping.blade.php` | 26 | "Thank you for shopping with VESTRA." | REPLACE | Change to "Thank you for choosing VESTRA." |
| `backend/resources/views/emails/orders/processing.blade.php` | 21 | "Thank you for shopping with VESTRA." | REPLACE | Change to "Thank you for choosing VESTRA." |

### API Messages (orphaned endpoints)

| File | Line | Term / Phrase | Classification | Recommended Action |
|------|------|---------------|----------------|--------------------|
| `backend/app/Http/Controllers/Api/V1/CartController.php` | 39 | "Item added to cart." | DELETE | File will be removed in cleanup |
| `backend/app/Http/Controllers/Api/V1/CartController.php` | 54 | "Cart item updated." | DELETE | File will be removed in cleanup |
| `backend/app/Http/Controllers/Api/V1/CartController.php` | 64 | "Item removed from cart." | DELETE | File will be removed in cleanup |
| `backend/app/Http/Controllers/Api/V1/CartController.php` | 74 | "Cart cleared." | DELETE | File will be removed in cleanup |
| `backend/app/Http/Controllers/Api/V1/CartController.php` | 85 | "Cart merged successfully." | DELETE | File will be removed in cleanup |
| `backend/app/Http/Controllers/Api/V1/WishlistController.php` | 149 | "Product moved to cart." | DELETE | Remove move-to-cart method |
| `backend/app/Http/Controllers/Api/V1/SavedItemController.php` | 131 | "Product moved to cart." | DELETE | Remove move-to-cart method |
| `backend/app/Services/OrderService.php` | 33 | "Your cart is empty." | DELETE | Refactor/remove with cart cleanup |
| `backend/app/Services/DistributorOrderService.php` | 33 | "Your cart is empty." | DELETE | Refactor/remove with cart cleanup |

### Backend Internal Code

| File(s) | Term | Classification | Notes |
|---------|------|----------------|-------|
| `backend/app/Models/Cart.php`, `CartItem.php` | cart, Cart, CartItem | BACKEND ONLY | Models to be removed |
| `backend/app/Services/CartService.php` | cart | BACKEND ONLY | Service to be removed |
| `backend/app/Repositories/CartRepository.php` | cart, cart_items | BACKEND ONLY | Repository to be removed |
| `backend/app/Policies/CartPolicy.php`, `CartItemPolicy.php` | cart | BACKEND ONLY | Policies to be removed |
| `backend/app/Http/Requests/Api/V1/AddToCartRequest.php` | AddToCart | BACKEND ONLY | Request to be removed |
| `backend/app/Http/Requests/Api/V1/UpdateCartItemRequest.php` | UpdateCartItem | BACKEND ONLY | Request to be removed |
| `backend/app/Http/Requests/Api/V1/CheckoutRequest.php` | Checkout | BACKEND ONLY | Request to be removed |
| `backend/tests/Feature/Api/V1/CartControllerTest.php` | cart | BACKEND ONLY | Tests to be removed |
| `backend/tests/Feature/Api/V1/CheckoutTest.php` | checkout | BACKEND ONLY | Tests to be removed |
| `backend/tests/Feature/Api/V1/CustomerOrderExperienceTest.php` | "Add to cart" comment | BACKEND ONLY | Remove comment when test is updated |

### Filament Admin

| File | Line | Term | Classification | Notes |
|------|------|------|----------------|-------|
| `backend/app/Filament/Resources/OrderResource.php` | 26 | `heroicon-o-shopping-cart` icon | KEEP | Generic order icon |
| `backend/app/Filament/Resources/QuotationRequestResource.php` | 219 | `heroicon-o-shopping-cart` icon | KEEP | Generic order icon |
| `backend/app/Filament/Resources/PaymentTransactionResource.php` | 194 | `heroicon-o-shopping-cart` icon | KEEP | Generic order icon |
| `backend/app/Filament/Resources/ProductResource.php` | 24, 35 | `heroicon-o-shopping-bag` icon | KEEP | Generic product icon |
| `backend/app/Filament/Pages/Reports/SalesReport.php` | 12 | `heroicon-o-shopping-bag` icon | KEEP | Generic product icon |
| `backend/app/Filament/Pages/Reports/ReportsDashboard.php` | 48 | `heroicon-o-shopping-bag` icon | KEEP | Generic product icon |
| `backend/app/Filament/Widgets/SearchAnalyticsWidget.php` | 35 | `heroicon-m-shopping-bag` icon | KEEP | Generic product icon |
| `backend/app/Filament/Widgets/Reports/ReportsOverviewKpiWidget.php` | 64 | `heroicon-m-shopping-bag` icon | KEEP | Generic product icon |
| `backend/app/Filament/Widgets/RecentActivityWidget.php` | 45–46 | `heroicon-o-shopping-cart`, `heroicon-o-shopping-bag` | KEEP | Activity icons |
| `backend/app/Filament/Resources/PurchaseOrderResource.php` | multiple | "Purchase Order" | KEEP | Correct B2B procurement term |
| `backend/app/Filament/Widgets/TopDistributorsWidget.php` | 36 | "Total Purchases" | KEEP | Correct B2B metric |

---

## Summary

| Classification | Count |
|----------------|-------|
| KEEP | 30+ |
| REPLACE | 3 |
| DELETE | 11 |
| BACKEND ONLY | 12+ |

### Immediate Actions

1. Update the three order email footers from "Thank you for shopping with VESTRA." to "Thank you for choosing VESTRA."
2. Execute the backend commerce cleanup plan to remove all DELETE-classified messages along with their controllers/services.

### No Action Required

- Icon names (`ShoppingCart`, `ShoppingBag`, `heroicon-o-shopping-cart`) are generic glyphs representing orders/products in the B2B admin and account areas.
- Redirects and robots entries for `/cart` and `/checkout` must remain.
- Backend internal code will be removed as part of the planned commerce cleanup.
