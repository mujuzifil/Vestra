# Phase 8 — Blog CMS Architecture

## Backend Tables

| Table | Purpose |
|-------|---------|
| `blog_authors` | Authors with bio, role, avatar, social links, active flag. |
| `blog_categories` | Hierarchical categories with SEO metadata and sort order. |
| `blog_tags` | Tags for granular article grouping. |
| `blog_posts` | Core article data: content, media, status, visibility, scheduling, SEO. |
| `blog_category_post` | Many-to-many pivot between posts and categories. |
| `blog_post_tag` | Many-to-many pivot between posts and tags. |
| `blog_post_views` | Lightweight anonymous view log for future analytics. |

## Models

- `App\Models\BlogAuthor`
- `App\Models\BlogCategory`
- `App\Models\BlogTag`
- `App\Models\BlogPost`
- `App\Models\BlogPostView`

All slug-aware models use the existing `HasSlug` trait.

## Enums

- `BlogPostStatus`: draft, published, scheduled, archived.
- `BlogPostVisibility`: public, internal.

## Public API

| Method | Route | Description |
|--------|-------|-------------|
| GET | `/api/v1/blog/posts` | Paginated published articles with filters. |
| GET | `/api/v1/blog/posts/featured` | Single featured article or null. |
| GET | `/api/v1/blog/posts/{slug}` | Single article detail. |
| GET | `/api/v1/blog/categories` | Active categories. |
| GET | `/api/v1/blog/tags` | Active tags. |

## Admin Filament Resources

- `BlogPostResource` — full post editor with rich text, media, SEO, scheduling, categories, tags.
- `BlogCategoryResource` — category management.
- `BlogTagResource` — tag management.
- `BlogAuthorResource` — author profiles.

All resources are restricted to admin users.

## Future-Ready Features

- Draft / scheduled / archived publishing workflow.
- Internal vs public visibility.
- Featured article flag.
- Content blocks JSON field for future block editor.
- Gallery upload support.
- View count and `blog_post_views` table for popularity sorting.
