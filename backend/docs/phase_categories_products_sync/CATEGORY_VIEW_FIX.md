# Category View 500 — Root Cause & Fix

## Symptom
Clicking **View** on a category returned a 500 SERVER ERROR.

## Root cause
`Category::products()` was defined as:

```php
return $this->hasMany(Product::class)->orderBy('sort_order');
```

The `products` table has **no** `sort_order` column (see `2026_07_15_140001_create_products_table.php`). When the detail drawer loaded assigned products (`getDetail()` eager-loads `products`), MySQL raised `Unknown column 'sort_order'`, surfacing as a 500.

## Fix
Order products by a real column:

```php
// app/Models/Category.php
public function products(): HasMany
{
    return $this->hasMany(Product::class)->orderBy('name');
}
```

The detail drawer was also rebuilt to use `@entangle('showDetailDrawer')` (live Livewire sync) instead of a stale `@js($show)` snapshot, and to render real DB values with `Not provided` fallbacks.

## Regression test
`CategoriesPageTest::test_view_details_loads_products_without_server_error` opens the drawer for a category with an assigned product and asserts a successful render including the product, breadcrumb, and public-website section.
