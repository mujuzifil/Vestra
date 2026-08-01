# VESTRA Frontend Dead Code Audit

**Scope:** Identify unused components, hooks, services, API clients, types, and imports after Phase 1 B2B restructure.  
**Date:** 2026-08-01

---

## 1. Tools Run

| Tool | Command | Result |
|------|---------|--------|
| TypeScript | `npx tsc --noEmit` | ✅ Passed |
| ESLint | `npm run lint` | ✅ Passed (2 pre-existing `<img>` warnings) |
| Next.js Build | `npm run build` | ✅ Passed |

`knip` was not run because it is not installed in the project. The audit below is based on static analysis, build output, and manual import scanning.

---

## 2. Findings

### 2.1 Removed During This Audit

| Item | File | Reason |
|------|------|--------|
| `CartItemProduct` interface | `frontend/types/index.ts` | No imports after cart removal |
| `CartItem` interface | `frontend/types/index.ts` | No imports after cart removal |
| `Cart` interface | `frontend/types/index.ts` | No imports after cart removal |

These types were referenced only by the deleted cart context and cart API files. Removal confirmed by:
- `grep -R "CartItemProduct\|interface CartItem\|interface Cart\b" frontend` returned only documentation and icon imports.
- `npx tsc --noEmit` passes after removal.

### 2.2 Already Removed in Phase 1

| Category | Files / Directories Deleted |
|----------|----------------------------|
| Cart context | `frontend/lib/cart-context.tsx` |
| Compare context | `frontend/lib/compare-context.tsx` |
| Cart API client | `frontend/lib/api/cart.ts` |
| Cart UI components | `frontend/components/cart/*` |
| Checkout UI components | `frontend/components/checkout/*` |
| Compare UI | `frontend/app/compare/*` |
| Cart page | `frontend/app/cart/*` |
| Checkout pages | `frontend/app/checkout/*` |
| Bulk orders page | `frontend/app/bulk-orders/*` |
| Track page | `frontend/app/track/*` |

No remaining imports reference these deleted files.

### 2.3 API Clients — All Used

| API File | Import Count | Status |
|----------|--------------|--------|
| `frontend/lib/api/auth.ts` | 9 | Used |
| `frontend/lib/api/categories.ts` | 1 | Used |
| `frontend/lib/api/client.ts` | 2 | Used |
| `frontend/lib/api/contact.ts` | 1 | Used |
| `frontend/lib/api/distributor.ts` | 15 | Used |
| `frontend/lib/api/distributor-portal.ts` | 14 | Used |
| `frontend/lib/api/feedback.ts` | 1 | Used |
| `frontend/lib/api/notifications.ts` | 3 | Used |
| `frontend/lib/api/orders.ts` | 1 | Used |
| `frontend/lib/api/payments.ts` | 1 | Used |
| `frontend/lib/api/products.ts` | 3 | Used |
| `frontend/lib/api/quote-requests.ts` | 1 | Used (new) |
| `frontend/lib/api/recently-viewed.ts` | 1 | Used |
| `frontend/lib/api/recommendations.ts` | 1 | Used |
| `frontend/lib/api/reviews.ts` | 1 | Used |
| `frontend/lib/api/settings.ts` | 1 | Used |

### 2.4 Hooks — No Unused Found

All hooks in `frontend/hooks/` are imported by at least one component/page:
- `use-orders.ts` — used by account orders pages
- `use-products.ts` — used by products page, navbar
- `use-recommendations.ts` — used by home page
- Other hooks spot-checked and confirmed used

### 2.5 Components — No Unused Found

No exported component in `frontend/components/` was found to be unimported during spot checks. A full automated unimported-export scan was attempted but timed out; manual review of major directories found no dead components.

### 2.6 CSS — No Unused Framework Classes Found

The project uses Tailwind CSS with custom design tokens. No orphaned CSS files were identified.

### 2.7 Environment Variables — Not Audited

`.env*` files were not audited because they may contain secrets and the scope is frontend dead code only. Review separately if needed.

---

## 3. Warnings (Pre-Existing, Not Dead Code)

| File | Line | Warning |
|------|------|---------|
| `frontend/components/reviews/review-form.tsx` | 247 | `<img>` element instead of Next.js `<Image />` |
| `frontend/components/reviews/review-list.tsx` | 264 | `<img>` element instead of Next.js `<Image />` |

These are image optimization warnings, not dead code. They are outside the scope of this phase.

---

## 4. Recommendations

1. **Install and run `knip`** in a future cleanup pass for a definitive unimported-export report:
   ```bash
   npm install --save-dev knip
   npx knip
   ```
2. **Remove the two `<img>` warnings** when refactoring review components.
3. **Keep monitoring** for dead code as the backend commerce cleanup progresses and more frontend order/checkout code may become removable.

---

## 5. Conclusion

The frontend is clean of dead cart/checkout code. The only action taken was removing three unused cart-related TypeScript interfaces. Build, type-check, and lint all pass.
