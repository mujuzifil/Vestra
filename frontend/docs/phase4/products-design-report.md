# Phase 4 — Products Catalogue Design Report

## Objective
Transform the Products catalogue (`/products` and `/products/[slug]`) from an e-commerce grid into a premium B2B manufacturer catalogue.

## Design Direction
- Manufacturer-style catalogue (SC Johnson Professional, Diversey, Ecolab).
- Lead-generation focus: Request Quote, Contact Sales, Become a Distributor.
- No pricing, stock, Add to Cart, Buy Now, ratings, wishlist, or compare-to-shop UI.

## Section Architecture — Listing Page

| Order | Section | Purpose |
|-------|---------|---------|
| 1 | Hero | "Professional Cleaning Products" with CTAs |
| 2 | Product Categories | Premium category cards |
| 3 | Search & Filter Bar | Horizontal, responsive, B2B filters |
| 4 | Products Grid | Corporate product cards |
| 5 | Product Comparison | Multi-select comparison table |
| 6 | Why Choose VESTRA® | Corporate value cards |
| 7 | Need Help Choosing? CTA | Final conversion |

## Product Detail Page Sections

| Section | Content |
|---------|---------|
| Overview | Description, SKU, availability |
| Key Benefits | Benefit list |
| Applications | Feature-derived chips |
| Package Sizes | Sizes from specifications |
| Usage Instructions | From specifications |
| Industries Served | Category-to-industry map |
| Product Specifications | Specification table |
| Recently Viewed | No pricing |
| Related Products | No pricing |
| Bottom CTA | Request Quote / Distributor |

## Key UX Decisions
- Client-side filtering across all products (small catalogue) to avoid backend changes.
- Package sizes and industries are derived from `specifications` and a static category map.
- Comparison limited to 3 products.
- Empty state includes reset and Request Quote CTA.

## Files Modified / Created
- `frontend/app/products/layout.tsx`
- `frontend/app/products/page.tsx`
- `frontend/app/products/[slug]/product-page-client.tsx`
- `frontend/components/ui/empty-products.tsx`
- `frontend/docs/phase4/*.md`
