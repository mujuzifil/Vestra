# Phase 7 — Corporate Where to Buy Experience Design Report

## Objective
Transform `/where-to-buy` into a premium corporate distributor locator and sales-enquiry page.

## Sections Delivered

1. **Hero**
   - Gradient header with distributor locator badge.
   - Headline: "Find VESTRA® Products Near You".
   - Supporting copy and Find a Distributor / Become a Distributor CTAs.

2. **Distribution Network**
   - Four stat cards: Districts Served, Authorised Partners, Commercial Customers, Network Status.
   - Values sourced from the public API with settings fallbacks.

3. **Where You Can Buy**
   - Six channel cards: Authorised Distributors, Wholesale Stores, Retail Shops, Commercial Laundry Suppliers, Institutional Supply Partners, Supermarkets.

4. **Coverage Map**
   - Static Uganda coverage component grouped by region.
   - District status badges: Covered, Coming Soon, Planned.
   - Empty state when no coverage data exists.

5. **Distributor Directory**
   - Searchable/filterable directory by business name, district, and region.
   - Result cards show address, phone, email, business type, and service areas.
   - Empty state with sales contact CTAs.
   - Only active distributors from the database are displayed.

6. **Need Help Finding a Distributor?**
   - Phone, WhatsApp, and Email support cards.

7. **Become a Distributor**
   - Dark gradient CTA section with partnership benefits and buttons.

8. **FAQ**
   - Six common questions about purchasing, delivery, and partnership.

9. **Related Resources**
   - Cards linking Products, Request a Quote, Blog, Contact.

10. **Final CTA**
    - Contact Sales / Become a Distributor / Request a Quote.

## Design System Compliance
- Uses existing gradient hero, `ValueCard`, `SectionHeader`, `FAQAccordion`, `AnimatedItem`, and `Container`.
- Responsive grids adapt 1 → 2 → 3–4 columns.
- No e-commerce terminology or shopping actions.
