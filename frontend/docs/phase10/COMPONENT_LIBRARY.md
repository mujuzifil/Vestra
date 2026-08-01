# Phase 10 — Component Library

## Primitive UI components

### `frontend/components/ui/button.tsx`
CVA-based button with variants:
- `default` — secondary green fill
- `secondary` — primary navy fill
- `outline` — bordered card style
- `ghost` — subtle hover background
- `link` — text link
- `danger` — red fill
- `accent` — gold fill
- `gradient` — marketing CTA gradient (new)

Props:
- `isLoading`, `leftIcon`, `rightIcon`, `asChild`

Marketing CTAs set `className="rounded-full px-7 py-3.5 h-auto"`.

### `frontend/components/ui/card.tsx`
Standard Card/Header/Title/Description/Content/Footer.
Radius: `rounded-[20px]`.

### `frontend/components/ui/alert.tsx`, `empty-state.tsx`, `skeleton-grid.tsx`, `api-error.tsx`
Kept existing; align with semantic tokens.

## Common layout components

| Component | Purpose |
|---|---|
| `Container` | Max-width 1320 px wrapper with responsive padding |
| `PageHero` | Page header with gradient background and breadcrumb |
| `SectionHeader` | Reusable section title/subtitle with divider |
| `CTASection` | Full-width call-to-action banner using `Button` |
| `ValueCard` | Icon + title + description card |
| `ContactCard` | Contact method card |
| `FAQAccordion` | Accessible accordion |
| `Breadcrumb` | Navigation breadcrumbs |
| `Icon` | Unified icon resolver |

## Section components
All `frontend/components/sections/*.tsx` components now:
- Use semantic tokens.
- Use `useReducedMotion()` instead of inline checks.
- Use the `Button` primitive for CTAs where applicable.

## Hooks
- `useReducedMotion()` — returns `true` when the user prefers reduced motion.
