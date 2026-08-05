# Phase 13.34 — Frontend

Public product/blog image URLs remain path-based from the API. Media Library replacements update those paths server-side and revalidate via existing `/api/revalidate` (products, blog, home).

No frontend structural changes required for DAM identity; consumers continue to use image URL strings.
