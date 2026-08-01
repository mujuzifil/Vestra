# Phase 10 — Visual Consistency Report

## Colour consistency
- Raw palette classes (`text-primary-900`, `text-neutral-600`, `text-neutral-400`) removed from shared marketing components and replaced with semantic tokens.
- Account and distributor portal components updated to use `text-text-heading`, `text-text-body`, and `text-text-muted`.

## Button consistency
- All primary marketing CTAs now use `Button asChild variant="gradient"` with `rounded-full`.
- Secondary/outline CTAs use `Button asChild variant="outline"`.
- Text links use `Button variant="link"`.

## Card consistency
- Marketing cards use `rounded-[20px]`, `bg-surface-card`, `border-default`, and consistent hover shadows.
- The `Card` primitive matches the same radius.

## Spacing consistency
- Sections follow the defined spacing scale.
- Container padding is uniform.

## Icon consistency
- All icons imported from `lucide-react` or resolved via `Icon`.
- Consistent sizing (`w-4 h-4`, `w-5 h-5`, `w-6 h-6`).

## Remaining items
- A few status-colour helpers in account/distributor pages (`bg-green-100`, `text-red-600`) remain for semantic status badges and are acceptable.
- Further illustration/photography standardisation can happen as new assets are produced.
