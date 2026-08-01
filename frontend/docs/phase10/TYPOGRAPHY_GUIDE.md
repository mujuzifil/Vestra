# Phase 10 — Typography Guide

## Font family
- Primary UI and headings: Poppins (`var(--font-poppins)`), fallback system sans-serif.
- Mono: JetBrains Mono (`var(--font-mono)`).

## Semantic colour usage
Never rely on raw neutral palette classes for text. Use semantic tokens:

| Element | Class |
|---|---|
| Page / card headings | `text-text-heading` |
| Body copy | `text-text-body` |
| Labels, captions, helper text | `text-text-muted` |
| Input placeholders | `text-text-placeholder` |
| Text on dark hero/CTA | `text-white`, `text-white/75`, `text-white/70` |

## Heading hierarchy
- Every page has exactly one `<h1>`.
- Section titles use `<h2>` via `SectionHeader`.
- Card titles and subsection titles use `<h3>`.
- Avoid skipping levels (e.g. no `<h4>` directly under `<h2>` without an `<h3>`).

## Type scale in use
| Level | Size | Usage |
|---|---|---|
| Hero H1 | `text-4xl sm:text-5xl lg:text-[clamp(3rem,6vw,6rem)]` | Home / page heroes |
| Page H1 | `text-3xl sm:text-4xl lg:text-[clamp(2.5rem,5vw,3.75rem)]` | Page hero headings |
| Section H2 | `text-3xl sm:text-4xl lg:text-[clamp(2.5rem,5vw,3.75rem)]` | `SectionHeader` |
| Card title | `text-lg lg:text-xl font-bold` | Product / value / contact cards |
| Body | `text-base lg:text-lg leading-relaxed` | Paragraphs |
| Caption | `text-sm` | Metadata, labels |

## Readability rules
- Use `font-bold`/`font-extrabold` for headings only.
- Body text uses default / `font-medium` for emphasis.
- Do not use opacity utilities (e.g. `text-white/50`) for primary body text.
