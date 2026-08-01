# Phase 2 — Homepage Design Report

## Objective
Transform the VESTRA® homepage from a generic product-focused layout into a premium corporate manufacturing website that communicates trust, manufacturing capability, product quality, distributor opportunity, and commercial supply capability.

## Design Direction
The homepage follows the visual language of professional B2B manufacturers such as Unilever Professional, Diversey, and Ecolab:
- Clean, spacious layouts
- Strong typography hierarchy
- Navy and green brand palette with gold accents
- Corporate iconography
- Subtle motion that respects `prefers-reduced-motion`
- Lead-generation CTAs rather than shopping actions

## Section Architecture

| Order | Section | Purpose |
|-------|---------|---------|
| 1 | Hero | Immediate brand promise, primary CTAs |
| 2 | Why Choose VESTRA® | Six value propositions |
| 3 | Product Categories | Catalogue entry points (no pricing) |
| 4 | Industries We Serve | B2B audience targeting |
| 5 | Featured Products | Flagship solutions with quote CTAs |
| 6 | Manufacturing Excellence | Capability and trust |
| 7 | Become a Distributor | Partner recruitment |
| 8 | Request a Quote | Primary conversion moment |
| 9 | Testimonials | Social proof (placeholder) |
| 10 | Latest Articles | Content marketing (placeholder) |
| 11 | Contact Banner | Final conversion CTA |

## Key UX Decisions
- No prices, no cart, no checkout references on the homepage.
- All primary CTAs lead to `Request a Quote`, `Contact Sales`, or `Become a Distributor`.
- Navbar is transparent over the hero and becomes solid navy on scroll.
- Footer includes contact details, business hours, social links, and registered trademark.

## Files Created / Modified
- `frontend/app/page.tsx`
- `frontend/components/sections/hero-section.tsx`
- `frontend/components/sections/why-choose-section.tsx`
- `frontend/components/sections/product-categories-section.tsx`
- `frontend/components/sections/industries-section.tsx`
- `frontend/components/sections/featured-products-section.tsx`
- `frontend/components/sections/manufacturing-section.tsx`
- `frontend/components/sections/distributor-cta-section.tsx`
- `frontend/components/sections/request-quote-section.tsx`
- `frontend/components/sections/testimonials-section.tsx`
- `frontend/components/sections/latest-articles-section.tsx`
- `frontend/components/sections/contact-banner-section.tsx`
- `frontend/components/navigation/navbar.tsx`
- `frontend/components/layout/footer.tsx`
- `frontend/components/common/icon.tsx`
- `frontend/lib/structured-data.tsx`
