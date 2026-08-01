# Phase 10 — Design System Report

## Objective
Unify the entire VESTRA® public website (and supporting account / distributor portals) under a single corporate design system.

## Tokens
Source of truth: `frontend/styles/tokens.css` and `frontend/app/globals.css`.

### Semantic text tokens adopted
| Purpose | Tailwind class | CSS variable |
|---|---|---|
| Headings | `text-text-heading` | `--text-heading` (`--neutral-800`) |
| Body | `text-text-body` | `--text-body` (`--neutral-600`) |
| Muted labels | `text-text-muted` | `--text-muted` (`--neutral-500`) |
| Placeholders | `text-text-placeholder` | `--text-placeholder` (`--neutral-400`) |
| Inverse on dark | `text-white` / `text-white/70` | n/a |

### Surface tokens
| Purpose | Tailwind class | CSS variable |
|---|---|---|
| Page background | `bg-surface-page` | `--surface-page` (`--neutral-50`) |
| Cards | `bg-surface-card` | `--surface-card` (`#ffffff`) |
| Borders | `border-default` | `--border-default` (`--neutral-200`) |

### Radius standard
Marketing cards and sections use `rounded-[20px]`.
The shared `Card` primitive now uses `rounded-[20px]` consistently.

## Primitives consolidated
- `frontend/components/ui/button.tsx` — added `gradient` variant and `asChild` support.
- `frontend/components/ui/card.tsx` — unified `rounded-[20px]` radius.
- `frontend/components/common/cta-section.tsx` — now uses the `Button` primitive.
- `frontend/components/common/value-card.tsx`, `contact-card.tsx`, `faq-accordion.tsx` — now use semantic text tokens.
- `frontend/components/layout/footer.tsx` — real social URLs and consistent fallback email.

## Motion
- Created `frontend/hooks/use-reduced-motion.ts`.
- Replaced inline `prefersReducedMotion` checks across all section and page components.
- Global `frontend/styles/motion.css` already respects `prefers-reduced-motion`.

## Validation
- `npm run lint` ✅ (0 errors, 0 warnings)
- `npx tsc --noEmit` ✅
- `npm run build` ✅ (51 static pages generated)

## Scope
Public marketing pages, shared sections, and account / distributor portal components were updated. Legacy commerce UI was already removed in earlier phases; no cart or checkout code remains in the frontend.
