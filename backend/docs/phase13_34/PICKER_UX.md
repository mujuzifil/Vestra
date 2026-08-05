# Media Picker UX

Selecting an image field opens `MediaAssetPicker`:

1. **Choose Existing** — search Media Library, select, confirm
2. **Upload New** — validate, store in `media_assets`, auto-select

Used by:

- Products form (`openMediaPicker` / `pendingMediaAssets`)
- Blog featured image + RTE inline image

Replace in Media Library updates denormalized consumer paths and triggers `syncMedia()`.
