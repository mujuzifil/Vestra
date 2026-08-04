# Phase 13.17 — Products Workspace Database Mapping

## Models Used

### Product
| Column | Type | Used In |
|---|---|---|
| id | bigint PK | All queries |
| category_id | FK → categories | Filter, table, drawer, export |
| name | string | Table, search, drawer, export |
| sku | string | Table, search, drawer, export |
| slug | string | Drawer |
| short_description | text | Table meta, drawer, search |
| description | text | Available in detail (not primary UI) |
| price | decimal | Table, drawer pricing, export |
| distributor_price | decimal | Table meta, drawer pricing, export |
| stock_quantity | integer | Stock badge, low-stock scope, export |
| status | enum(active,inactive,out_of_stock) | Badges, KPI, filter, export |
| featured | boolean | Filter, table pill, drawer |
| created_at / updated_at | datetime | Sort, drawer, export |

### Category
| Column | Type | Used In |
|---|---|---|
| id | bigint PK | Filter options, join sort |
| name | string | Table, drawer, KPI category count |

### ProductImage
| Column | Type | Used In |
|---|---|---|
| product_id | FK | Eager load |
| image | string path | Table thumbnail, drawer gallery |
| alt_text | string | Drawer alt |
| sort_order | int | Ordered via relation |

### ProductWarehouseStock
| Column | Type | Used In |
|---|---|---|
| product_id | FK | Drawer warehouse section |
| warehouse_id | FK → warehouses | Warehouse name |
| quantity / reserved_quantity / reorder_level | int | Available qty, low flag |

## Scopes
- `active()` / `inactive()` — status filters for KPIs
- `outOfStock()` — `stock_quantity = 0` (stock filter)
- `lowStock()` — `stock_quantity <= 10 AND > 0` (KPI + stock filter)

## Eager Loading
- List: `category`, `images`
- Detail: `category`, `images`, `warehouseStocks.warehouse`

## Explicitly Excluded
- Brand, supplier, barcode columns are not queried or rendered
- No dummy/fake KPI trend calculations (`trend_available` always false)
