# Public Website Synchronization

## Trigger

Any admin create / update / delete / scheduled auto-publish of a blog post calls:

```php
app(CatalogSyncService::class)->syncBlog($postId, $slug);
```

## Behaviour

1. Forget blog caches (`blog.categories.active`, `blog.tags.active`, `blog.posts.featured`)
2. POST to frontend `services.frontend.revalidate_url` with paths:
   - `/blog`
   - `/`
   - `/blog/{slug}` when known
3. Tags: `blog`, `blog-posts`

## Public API

- `GET /api/v1/blog/posts` — published listing (pin-aware sort)
- `GET /api/v1/blog/posts/featured` — featured article
- `GET /api/v1/blog/posts/homepage` — `show_on_homepage` or `is_featured`
- `GET /api/v1/blog/posts/{slug}` — article detail (published + public only)
- Categories / tags endpoints unchanged

## Frontend consumers

- Blog landing + featured section
- Article detail metadata (meta/OG/canonical)
- Homepage `LatestArticlesSection` prefers homepage API, falls back to newest posts
- Revalidate route: `frontend/app/api/revalidate/route.ts`

No manual cache clearing or deploy required for content updates.
