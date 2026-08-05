# Blog Article Editor

## Route

- New: `/marketing/blog/article`
- Edit: `/marketing/blog/article?id={id}`
- Page: `App\Filament\Pages\Marketing\BlogArticlePage`
- View: `resources/views/filament/pages/marketing/blog-article.blade.php`
- Styles: `resources/css/filament/admin/components/blog.css`

## Layout (matches `new_article.png`)

- Hero: breadcrumb, title, Save as Draft / Publish / more menu
- Left: Article Information, Featured Image, Article Options
- Right: Publishing, Organization, SEO & Visibility

## Fields

| Section | Fields |
| --- | --- |
| Information | title, slug, excerpt, rich content |
| Publishing | status (draft/scheduled/published/archived), scheduled_at, allow_comments |
| Organization | blog categories, author (staff), tags |
| SEO | meta title/description, OG title/description, canonical URL, slug preview, visibility |
| Options | is_featured, show_on_homepage, is_pinned |
| Media | featured image upload/replace/remove (jpg/png/webp) |

## Persistence

`BlogAdminService::createPost` / `updatePost` / `deletePost` write to the database and call `CatalogSyncService::syncBlog()`.
