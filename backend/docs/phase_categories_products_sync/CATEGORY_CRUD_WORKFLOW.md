# Category CRUD Workflow

Add / Edit / View Category are now fully functional Livewire modals on `CategoriesPage`, matching `Add_category.png` and `Edit_category.png`. The legacy Filament `CategoryResource` create/edit redirects are no longer used by the workspace.

## Add Category
- `openCreateModal()` resets the form and opens the modal.
- Fields: Category Name*, Slug*, Description, Parent Category, Sort Order, Status*.
- Slug auto-generates from the name (live) until manually edited; server re-slugifies and guarantees uniqueness.
- Info banner communicates public-website visibility.
- `saveCategory()` → `CategoryAdminService::createCategory()` inserts in a transaction and triggers catalog sync.

## Edit Category
- `openEditModal($id)` hydrates the form from the live record.
- Same fields plus **Delete Category** (shown when the user can delete and the category is empty).
- `updateCategory()` persists changes in a transaction and triggers catalog sync.

## View Category (detail drawer)
Sections: General (name, slug, URL slug, parent, breadcrumb path, sort order, status, product count), Description, Public Website (visibility, public URL, SEO placeholder), Audit (created/updated), Assigned Products. Null values render `Not provided`.

## Validation
- `name` required, max 255
- `slug` required, unique (ignore self), lowercase/hyphen regex
- `parent_id` nullable, must exist, cannot be self or a descendant (cycle prevention)
- `sort_order` required integer ≥ 0
- `status` in {active, inactive}
- Delete blocked when the category has products or subcategories.

## Parent categories
A `parent_id` self-referencing FK was added (`2026_08_05_140000_add_parent_id_to_categories_table.php`) with `nullOnDelete`. `Category` gained `parent()`, `children()`, `ancestorChain()`, and `breadcrumbPath()`.

## Tests
`CategoriesPageTest` covers create, update, delete-guard, view, filters, and KPIs (21 passing).
