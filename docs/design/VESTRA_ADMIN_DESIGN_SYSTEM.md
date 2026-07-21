# VESTRA Administration Design System

## 1. Administration Product Vision

### 1.1 Design Philosophy

The VESTRA Administration Platform is the command centre for a premium garment-care brand. Every screen should feel intentional, trustworthy, and efficient. The experience must communicate competence: administrators should sense that the system understands their workload and removes friction from complex operational tasks.

We do not customise Filament for the sake of customisation. We redesign the administrative experience so it behaves like a premium commercial SaaS product: calm, consistent, fast, and visually disciplined.

### 1.2 Product Principles

| Principle | Definition | In Practice |
|-----------|------------|-------------|
| **Clarity First** | Every element communicates its purpose instantly. | Labels are explicit, actions are contextual, data is scannable. |
| **Efficiency by Default** | Common tasks require the fewest clicks and keystrokes. | Bulk actions, smart defaults, keyboard shortcuts, persistent filters. |
| **Calm Confidence** | The UI never feels noisy or anxious. | Restrained colour, generous whitespace, progressive disclosure. |
| **Operational Truth** | Numbers and statuses are always credible and current. | Real-time badges, clear timestamps, cache transparency where used. |
| **Progressive Power** | Beginners see simplicity; experts see depth. | Advanced filters, power-user shortcuts, and detail-on-demand. |
| **Brand Continuity** | The admin is unmistakably VESTRA, not a generic tool. | Navy/green/gold identity, VESTRA logo, consistent voice. |

### 1.3 Information Density

- **Desktop:** Comfortable density. Tables show 10–25 rows. Cards group related data without crowding.
- **Tablet:** Slightly reduced density. Tables remain readable; side panels collapse.
- **Mobile:** Task-focused density. One primary action per screen, cards stack vertically.

Default padding inside cards: `24px`. Default gap between sections: `32px`.

### 1.4 Navigation Philosophy

- Persistent left sidebar for primary modules.
- Contextual secondary navigation inside page headers (tabs, segmented controls).
- Breadcrumbs reveal hierarchy without replacing wayfinding.
- Global search is always available from the top bar.

### 1.5 Professional Tone & Brand Personality

- **Tone:** Direct, helpful, expert. Avoid playful copy in operational contexts.
- **Voice:** "Save changes", "Mark as shipped", "Low stock alert" — action-oriented and specific.
- **Personality:** Premium but approachable. The system is a reliable colleague, not a robotic tool.

---

## 2. Visual Identity System

### 2.1 Color Palette

All colors are rooted in the public VESTRA website identity and adapted for high-productivity administrative interfaces.

#### Primary

| Token | Hex | Usage |
|-------|-----|-------|
| `--primary-900` | `#050d18` | Deepest backgrounds, emphasis surfaces |
| `--primary-800` | `#0a1628` | Sidebar background (dark mode), hero surfaces |
| `--primary-700` | `#0d1f33` | Header, active navigation |
| `--primary-600` | `#142c47` | Hover states on dark surfaces |
| `--primary-500` | `#0d3b66` | Primary buttons, links, key actions |
| `--primary-400` | `#4a90d9` | Focus rings, informational accents |
| `--primary-300` | `#7db8ec` | Hover links, subtle highlights |

#### Secondary (VESTRA Green)

| Token | Hex | Usage |
|-------|-----|-------|
| `--secondary-600` | `#5aa33d` | Success hover |
| `--secondary-500` | `#70c050` | Success states, positive KPIs, primary confirmation |
| `--secondary-400` | `#8fd974` | Success backgrounds, charts |
| `--secondary-100` | `#e8f5e4` | Success surface tint |

#### Accent (VESTRA Gold)

| Token | Hex | Usage |
|-------|-----|-------|
| `--accent-500` | `#d4af37` | Warnings, featured highlights, premium badges |
| `--accent-300` | `#f0d878` | Warning backgrounds, chart accents |
| `--accent-100` | `#fcf8e3` | Warning surface tint |

#### Semantic

| Token | Hex | Usage |
|-------|-----|-------|
| `--danger-500` | `#dc2626` | Errors, destructive actions, critical alerts |
| `--danger-100` | `#fee2e2` | Error surface tint |
| `--warning-500` | `#d4af37` | Warnings, pending states |
| `--warning-100` | `#fcf8e3` | Warning surface tint |
| `--success-500` | `#70c050` | Success, complete states |
| `--success-100` | `#e8f5e4` | Success surface tint |
| `--info-500` | `#4a90d9` | Informational hints, neutral blue accents |
| `--info-100` | `#dbeafe` | Info surface tint |

#### Neutral Palette

| Token | Hex | Usage |
|-------|-----|-------|
| `--neutral-50` | `#f8fafc` | Page background |
| `--neutral-100` | `#f1f5f9` | Card hover, secondary backgrounds |
| `--neutral-200` | `#e2e8f0` | Borders, dividers |
| `--neutral-300` | `#cbd5e1` | Disabled borders |
| `--neutral-400` | `#94a3b8` | Placeholder text, inactive icons |
| `--neutral-500` | `#64748b` | Secondary text |
| `--neutral-600` | `#475569` | Body text, labels |
| `--neutral-700` | `#334155` | Strong body text |
| `--neutral-800` | `#1e293b` | Headings |
| `--neutral-900` | `#0f172a` | Maximum contrast text |

### 2.2 Typography

**Primary Font:** `Poppins, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif`

**Monospace Font:** `JetBrains Mono, SF Mono, Consolas, monospace` (for JSON, code, transaction IDs)

| Level | Size | Weight | Line Height | Letter Spacing | Usage |
|-------|------|--------|-------------|----------------|-------|
| Display | `2rem` (32px) | 700 | 1.2 | -0.02em | Page titles |
| H1 | `1.5rem` (24px) | 700 | 1.3 | -0.01em | Section headings |
| H2 | `1.25rem` (20px) | 600 | 1.35 | 0 | Card titles |
| H3 | `1rem` (16px) | 600 | 1.4 | 0 | Subsection headings |
| Body | `0.875rem` (14px) | 400 | 1.5 | 0 | Body text, table cells |
| Body Small | `0.8125rem` (13px) | 400 | 1.5 | 0 | Metadata, captions |
| Caption | `0.75rem` (12px) | 500 | 1.4 | 0.01em | Badges, labels, timestamps |

### 2.3 Spacing Scale

| Token | Value | Usage |
|-------|-------|-------|
| `--space-1` | `4px` | Tight internal gaps |
| `--space-2` | `8px` | Inline icon gaps |
| `--space-3` | `12px` | Button internal padding (vertical) |
| `--space-4` | `16px` | Card internal padding, form gaps |
| `--space-5` | `20px` | Section internal spacing |
| `--space-6` | `24px` | Card padding, modal padding |
| `--space-8` | `32px` | Section separation |
| `--space-10` | `40px` | Page section separation |
| `--space-12` | `48px` | Major page breaks |

### 2.4 Border Radius

| Token | Value | Usage |
|-------|-------|-------|
| `--radius-sm` | `8px` | Small buttons, badges, inputs |
| `--radius-md` | `12px` | Cards, panels, modals |
| `--radius-lg` | `16px` | Large cards, drawers |
| `--radius-xl` | `24px` | Hero surfaces, login container |
| `--radius-full` | `9999px` | Pills, avatars |

### 2.5 Elevation & Shadows

| Token | Value | Usage |
|-------|-------|-------|
| `--shadow-sm` | `0 1px 2px rgba(10, 22, 40, 0.05)` | Subtle borders replacement |
| `--shadow-md` | `0 4px 12px rgba(10, 22, 40, 0.08)` | Cards, dropdowns |
| `--shadow-lg` | `0 12px 32px rgba(10, 22, 40, 0.12)` | Modals, drawers |
| `--shadow-xl` | `0 24px 56px rgba(10, 22, 40, 0.16)` | Full-screen overlays |
| `--shadow-focus` | `0 0 0 3px rgba(112, 192, 80, 0.35)` | Focus rings |

### 2.6 Borders

- Default border: `1px solid var(--neutral-200)`
- Hover border: `1px solid var(--neutral-300)`
- Focus border: `1px solid var(--primary-400)`
- Error border: `1px solid var(--danger-500)`

### 2.7 Opacity

| Token | Value | Usage |
|-------|-------|-------|
| `--opacity-disabled` | `0.5` | Disabled controls |
| `--opacity-hover` | `0.9` | Hover state overlay |
| `--opacity-backdrop` | `0.65` | Modal backdrops |

### 2.8 Motion

**Easing:**
- `--ease-base`: `cubic-bezier(0.4, 0, 0.2, 1)`
- `--ease-in`: `cubic-bezier(0.4, 0, 1, 1)`
- `--ease-out`: `cubic-bezier(0, 0, 0.2, 1)`
- `--ease-bounce`: `cubic-bezier(0.68, -0.55, 0.265, 1.55)`

**Durations:**
- `--duration-fast`: `150ms` (hover, focus)
- `--duration-base`: `200ms` (state changes)
- `--duration-slow`: `300ms` (page transitions, drawers)
- `--duration-slower`: `400ms` (modals, toasts)

**Guidelines:**
- Use motion to communicate state, not to decorate.
- Respect `prefers-reduced-motion`.
- Loading skeletons pulse with `--duration-slow`.

### 2.9 Icons

- **Icon Library:** Heroicons (already used by Filament) for operational consistency.
- **Style:** Outline icons for navigation and actions; solid icons for status badges and micro-indicators.
- **Size:**
  - Navigation: `20px`
  - Buttons inline: `16px`
  - Table actions: `16px`
  - Empty states: `48px`

### 2.10 Illustrations

- Use simple geometric illustrations in VESTRA navy and green for empty states.
- Avoid stock photography inside the admin.
- Illustrations should reinforce the action needed (e.g., empty orders = open box illustration).

---

## 3. Layout System

### 3.1 Application Shell

```
┌─────────────────────────────────────────────────────────────┐
│  Top Bar (logo, global search, notifications, profile)      │
├──────────────┬──────────────────────────────────────────────┤
│              │  Breadcrumbs + Page Title + Primary Actions   │
│  Sidebar     ├──────────────────────────────────────────────┤
│  Navigation  │                                              │
│              │  Content Area                                │
│              │  (cards, tables, forms, widgets)             │
│              │                                              │
└──────────────┴──────────────────────────────────────────────┘
```

### 3.2 Sidebar

- **Width:** `260px` desktop, `72px` collapsed icon-only
- **Background:** `--primary-800`
- **Active item:** `--primary-700` background, `--secondary-500` left border indicator
- **Hover:** `--primary-600` background
- **Text:** `--neutral-50` primary, `--neutral-400` muted
- **Groups:** Collapsible, with clear group labels in `--neutral-400`

### 3.3 Top Bar

- **Height:** `64px`
- **Background:** `--neutral-50`
- **Elements:**
  - VESTRA logo (left)
  - Global search (centre, collapses to icon on tablet)
  - Notification bell + badge (right)
  - Profile menu with avatar + name (right)

### 3.4 Workspace

- **Content max-width:** `1440px`, centred
- **Default padding:** `32px` desktop, `24px` tablet, `16px` mobile
- **Background:** `--neutral-50`

### 3.5 Cards

- Background: white
- Border: `1px solid var(--neutral-200)`
- Border radius: `--radius-md`
- Shadow: `--shadow-sm`
- Padding: `--space-6`
- Hover shadow for clickable cards: `--shadow-md`

### 3.6 Responsive Breakpoints

| Breakpoint | Width | Layout Changes |
|------------|-------|----------------|
| `sm` | 640px | Mobile layout |
| `md` | 768px | Tablet; sidebar collapses to icon-only |
| `lg` | 1024px | Laptop; full sidebar |
| `xl` | 1280px | Desktop |
| `2xl` | 1536px | Large desktop |

---

## 4. Component Foundations

### 4.1 Buttons

**Primary Button**
- Background: `--primary-500`
- Text: white
- Hover: `--primary-600`
- Padding: `10px 16px`
- Radius: `--radius-sm`
- Font weight: 600

**Secondary Button**
- Background: white
- Border: `1px solid var(--neutral-200)`
- Text: `--neutral-700`
- Hover: `--neutral-50`

**Danger Button**
- Background: `--danger-500`
- Text: white
- Hover: `#b91c1c`

**Ghost Button**
- Background: transparent
- Text: `--primary-500`
- Hover: `--primary-100`

### 4.2 Inputs

- Height: `40px`
- Padding: `8px 12px`
- Border: `1px solid var(--neutral-200)`
- Radius: `--radius-sm`
- Focus: `border-color: var(--primary-400); box-shadow: var(--shadow-focus)`
- Error: `border-color: var(--danger-500)`

### 4.3 Tables

- Header background: `--neutral-50`
- Header text: `--neutral-600`, uppercase caption style
- Row hover: `--neutral-50`
- Selected row: `--primary-100`
- Cell padding: `12px 16px`
- Border: `1px solid var(--neutral-200)` between rows only

### 4.4 Badges

| Type | Background | Text | Border |
|------|------------|------|--------|
| Success | `--success-100` | `--success-500` | none |
| Warning | `--warning-100` | `--warning-500` | none |
| Danger | `--danger-100` | `--danger-500` | none |
| Info | `--info-100` | `--info-500` | none |
| Neutral | `--neutral-100` | `--neutral-600` | none |

### 4.5 Notifications

- Toast position: top-right
- Background: white
- Shadow: `--shadow-lg`
- Border-left: `4px solid` semantic colour
- Icon: semantic solid icon
- Auto-dismiss: 5 seconds
- Success/danger/information/warning variants

### 4.6 Empty States

- Centered layout inside card
- Icon: `48px`, `--neutral-400`
- Title: H3, `--neutral-800`
- Description: Body, `--neutral-500`
- Primary action button below

### 4.7 Loading States

- **Skeleton:** Pulsing rounded rectangles in `--neutral-200`
- **Spinner:** `--primary-500` circular spinner, `20px`
- **Button loading:** Spinner replaces icon, text remains, disabled state
- **Page loading:** Skeleton cards matching layout structure

---

## 5. Motion & Interaction

### 5.1 Hover

- Buttons: background colour shift, `150ms`
- Table rows: background `--neutral-50`, `150ms`
- Cards: shadow elevation increase, `200ms`
- Links: colour shift to `--primary-400`, underline optional

### 5.2 Focus

- All interactive elements: `--shadow-focus` ring
- Focus-visible only; no focus ring on mouse click

### 5.3 Loading / Saving

- Forms show spinner on primary action during submission
- Buttons disable to prevent double submission
- Skeleton placeholders for async page sections

### 5.4 Deleting / Confirmation

- Destructive actions always require confirmation
- Confirmation dialog title: "Delete [Item]?"
- Primary destructive button labelled "Delete"
- Secondary button: "Cancel"

### 5.5 Validation

- Inline field errors appear below input
- Error text: `--danger-500`, `--duration-fast` fade-in
- Form-level errors shown as alert banner at top of form

### 5.6 Animations

- Page transitions: fade-in `200ms`
- Drawer: slide-in from right `300ms`
- Modal: scale + fade `200ms`
- Toast: slide-in from top-right `300ms`

---

## 6. Future-Proofing

The design system is intentionally modular:

- Tokens are abstracted; swapping a brand colour updates the entire platform.
- Components are documented by behaviour, not Filament class names, so they can be reimplemented if the admin stack changes.
- Layout primitives (sidebar, top bar, workspace) are defined independently of any framework.
- New modules (Analytics, CRM, Marketing, etc.) follow the same card/table/form/widget patterns.

---

## 7. Implementation Notes for Filament 3

- Apply the design system through a custom Filament theme rather than ad-hoc overrides.
- Use `AdminPanelProvider` for global colours, logo, and favicon.
- Override Blade components for cards, tables, buttons, and forms only where the default cannot satisfy the design system.
- Keep custom CSS scoped to the Filament theme to avoid conflicts with the public frontend.
