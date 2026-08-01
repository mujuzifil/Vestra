# Phase 10 — SEO Audit

## Metadata
- Every public page uses `createMetadata()` from `frontend/lib/metadata.tsx`.
- Title, description, keywords, Open Graph, Twitter Cards, and canonical URL are set.

## Structured data
- `frontend/lib/structured-data.tsx` provides schemas for:
  - Organization
  - LocalBusiness
  - Product
  - BreadcrumbList
  - ContactPage
  - BlogPosting
- JSON-LD is rendered on relevant pages.

## Sitemap & robots
- `frontend/app/sitemap.ts` generates sitemap.
- `frontend/app/robots.ts` controls crawl rules.
- Shopping routes (`/cart`, `/checkout`) were already removed from the sitemap in earlier phases.

## Internal linking
- Footer and navbar link to all primary pages.
- Related-resources sections provide contextual cross-links.
- Breadcrumbs implemented on product detail and nested pages.

## Recommendations
- Continue updating Open Graph images as new brand photography becomes available.
- Add FAQ schema on high-traffic enquiry pages in a future phase.
