# Phase 8 — Corporate Knowledge Centre Design Report

## Objective

Transform `/blog` from a placeholder "coming soon" page into a premium B2B Knowledge Centre that positions VESTRA® as an industry authority and supports lead generation.

## Sections Delivered

1. **Hero**
   - Gradient header with Knowledge Centre badge.
   - Headline: "VESTRA® Knowledge Centre".
   - Supporting copy focused on commercial cleaning expertise.
   - CTAs: Browse Articles, Request a Quote.

2. **Featured Article**
   - Large two-column layout highlighting the latest featured post.
   - Displays featured image, category badge, reading time, author, and publication date.
   - Hidden when no featured post exists.

3. **Explore Topics (Categories)**
   - Responsive grid of category cards with icon mapping.
   - Clicking a category filters the article grid.
   - Uses existing design tokens and hover elevations.

4. **Search & Filter**
   - Search input with debounced API calls.
   - Category dropdown.
   - Sort dropdown: Newest, Oldest, Most Popular, Reading Time.
   - Result count label.

5. **Latest Articles Grid**
   - Responsive 1 → 2 → 3 column grid of article cards.
   - Each card shows image, category, title, excerpt, author, date, reading time.
   - Premium empty state when no published articles exist.

6. **Newsletter**
   - Name, email, and interest selection form.
   - Placeholder submission ready for future mailing platform integration.

7. **Related Resources**
   - Cards linking Products, Request a Quote, Become a Distributor, Contact.

8. **FAQ**
   - Common questions about the Knowledge Centre.

9. **Final CTA**
   - Contact Sales / Request a Quote / Become a Distributor.

10. **Article Detail Page** (`/blog/[slug]`)
    - Full article rendering with styled HTML content.
    - Featured image, category badges, author, reading time.
    - Tags, back navigation, and bottom CTA.
    - JSON-LD BlogPosting schema.

## Design System Compliance

- Reuses `Container`, `SectionHeader`, `Breadcrumb`, `FAQAccordion`, `AnimatedItem`, `Icon`, and `ValueCard` patterns.
- No e-commerce language or shopping actions.
- Consistent gradient heroes, rounded cards, and spacing from Phases 2–7.
