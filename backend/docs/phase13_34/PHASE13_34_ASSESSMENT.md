# Phase 13.34 — Assessment

## Goal

Replace the read-only Media aggregator with a centralized Digital Asset Manager (`media_assets` + `media_asset_usages`), integrate Products and Blog through a shared Choose Existing / Upload New picker, enforce reference-safe deletes, and sync public surfaces via `CatalogSyncService::syncMedia()`.

## Decisions

- First-class `MediaAsset` table (not Spatie morph media as DAM)
- Denormalized paths kept on `product_images.image` and `blog_posts.featured_image` for API compatibility
- Settings Spatie media remains legacy
- No production deploy

## Delivered

- Schema + import command `media:import-legacy`
- Media Library UI (upload, filters, details, usage, replace, archive, delete)
- Shared `MediaAssetPicker` Livewire component
- Product + Blog featured/gallery/inline integration
- Feature tests in `MediaPageTest`
