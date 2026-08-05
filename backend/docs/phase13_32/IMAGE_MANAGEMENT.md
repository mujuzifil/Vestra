# Image Management

Uses existing `ProductImage` records and the public disk `products/` path.

## Add Product

- Multi-file dropzone (`imageUploads`)
- Temporary Livewire previews before save
- On create, files stored via `ProductAdminService::storeImages()`

## Edit Product

- Existing thumbnails with remove (`removeProductImage`)
- **Add Image** control + drag/drop zone share one file input
- Upload previews for pending files
- Remove deletes DB row and public disk file when present

## Constraints

- JPG / PNG / WebP
- Max 5MB per file (`max:5120`)
- No fake/demo uploads

## Details

Product Details shows all stored images with click-to-preview (new tab). Empty image sets show `Not provided`.
