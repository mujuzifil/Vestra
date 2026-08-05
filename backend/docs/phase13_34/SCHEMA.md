# Media Asset Schema

## Tables

### `media_assets`

Canonical file record: path, mime, checksum, dimensions, metadata, status, uploader.

### `media_asset_usages`

Polymorphic references (`usable_type` / `usable_id`) with `context` (`product_primary`, `product_gallery`, `blog_featured`, `blog_inline`, `homepage`, `marketing`, …).

### Consumer FKs

- `product_images.media_asset_id`
- `blog_posts.featured_media_asset_id`

## Import

```
php artisan media:import-legacy
```

Creates assets for existing product/blog paths and attaches usages without duplicating files when checksums match.
