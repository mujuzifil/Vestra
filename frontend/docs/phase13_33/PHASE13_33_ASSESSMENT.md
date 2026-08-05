# Phase 13.33 — Frontend Assessment

Blog public surfaces consume live API data only. Admin CMS changes revalidate `/blog`, `/`, and `/blog/{slug}` via the existing revalidate endpoint.

## Changes

- `types`: `show_on_homepage`, `is_pinned`, `allow_comments`, `og_title`, `og_description`
- `lib/api/blog.ts`: `getHomepagePosts()`
- Homepage `LatestArticlesSection`: prefer homepage posts, fallback to newest
- Article `generateMetadata`: OG/meta/canonical-aware
