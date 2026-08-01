# Phase 5 — Distributor Experience Design Report

## Objective
Transform `/distributor` into a premium B2B partner-acquisition page and make the distributor application fully backend-integrated.

## Sections Delivered

1. **Hero**
   - Full-width gradient header with partnership badge.
   - Clear headline: "Become an Authorised VESTRA® Distributor".
   - Supporting value proposition and dual CTAs: Apply Now, Contact Sales.

2. **Why Partner With VESTRA®**
   - Six value cards: Trusted Brand, Competitive Margins, Growing Demand, Reliable Supply, Marketing Support, Sales Assistance.

3. **Who Can Apply**
   - Eight applicant-type cards: Wholesalers, Retail Chains, Regional Distributors, Supermarkets, Cleaning Suppliers, Commercial Supply Companies, Entrepreneurs, Institutional Suppliers.

4. **Distributor Benefits**
   - Six benefit rows with icon + text: Territory, Bulk Pricing, Training, Materials, Priority Support, Inventory.

5. **Application Process**
   - Visual six-step timeline: Submit → Review → Verification → Discussion → Approval → Onboarding.

6. **Distributor Application Form**
   - Expanded fields mapped to backend columns.
   - Client-side validation, loading state, error handling.
   - Optional file upload for supporting documents.
   - Success redirect to `/distributor/success?ref=...`.

7. **Partnership at a Glance**
   - Placeholder statistics section with clear documentation that live metrics will replace placeholders later.

8. **FAQ**
   - Six common questions using the shared FAQ accordion.

9. **Final CTA**
   - Corporate call-to-action section with Apply Now and Contact Sales buttons.

## Design System Compliance
- Uses existing `PageHero` visual language (gradient, white text, rounded CTAs).
- Reuses `ValueCard`, `SectionHeader`, `FAQAccordion`, `CTASection`, `AnimatedSection`, `Container`.
- Responsive grids: 1 col mobile, 2 col tablet, 3–4 col desktop.
- No e-commerce terminology or shopping actions remain on the page.
