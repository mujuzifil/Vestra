# Phase 8 — SEO Report

## Metadata

- `/blog` page title: "Knowledge Centre | VESTRA".
- `/blog` description focuses on commercial cleaning expertise.
- `/blog/[slug]` uses article `meta_title` / `meta_description` when present, falling back to title and excerpt.

## Structured Data

- Breadcrumb JSON-LD added to `/blog`.
- `BlogPosting` schema added to article detail pages, including:
  - Headline
  - Description
  - Image
  - Author (Person or Organization)
  - Publisher
  - Dates

## Sitemap

- `/blog` was already present in `app/sitemap.ts`.
- Individual article URLs will be added to the sitemap once a CMS-driven static generation step is implemented.
