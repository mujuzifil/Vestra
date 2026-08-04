# Phase 13.18 — Categories: Component Library

| Component | Props | Role |
|-----------|-------|------|
| `categories.page-header` | `title`, `description`, `canCreate`, `createUrl`, `csvUrl`, `excelUrl`, `pdfUrl` | Hero + search + actions |
| `categories.kpi-cards` | `cards` | KPI grid (`vestra-kpi-grid--4`) |
| `categories.filter-bar` | — (wires to Livewire) | Status + date filters |
| `categories.category-table` | `categories`, `sortField`, `sortDirection` | Table shell |
| `categories.category-row` | `category` | Row + actions menu |
| `categories.status-badge` | `status` | Active / inactive badge |
| `categories.pagination` | `paginator` | Page controls |
| `categories.empty-state` | `hasFilters`, `canCreate`, `createUrl` | Empty / no-results |
| `categories.detail-drawer` | `show`, `category` | Slide-over detail |

## Table Columns

Category, Slug, Products, Sort, Status, Updated, Actions

## Drawer Sections

1. Header (name, slug, product count)
2. Status badge + Edit action
3. Description
4. Details definition list
5. Assigned products list (live relation)
