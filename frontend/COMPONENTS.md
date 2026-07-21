# VESTRA Component Library

This document describes the reusable components used across the VESTRA Next.js frontend.

---

## Common Components

### `Container`
**Purpose:** Max-width wrapper that provides consistent horizontal padding.
**Props:**
- `children: React.ReactNode`
- `className?: string`
**Usage:**
```tsx
<Container className="py-12">
  <p>Content</p>
</Container>
```

### `PageHero`
**Purpose:** Dark gradient page banner with title, subtitle, and optional breadcrumb.
**Props:**
- `title: string`
- `subtitle?: string`
- `breadcrumb?: { label: string; href?: string }[]`
- `className?: string`
**Usage:**
```tsx
<PageHero title="About Us" subtitle="..." breadcrumb={[{ label: "About Us" }]} />
```

### `SectionHeader`
**Purpose:** Standardized section title with accent underline.
**Props:**
- `title: string`
- `subtitle?: string`
- `centered?: boolean` (default `true`)
- `light?: boolean` (default `false`)
- `id?: string`
- `className?: string`
**Usage:**
```tsx
<SectionHeader title="Core Values" subtitle="..." />
```

### `CTASection`
**Purpose:** Reusable call-to-action band.
**Props:**
- `title: string`
- `description?: string`
- `buttonText: string`
- `buttonHref: string`
- `secondaryButton?: { text: string; href: string }`
- `light?: boolean`
- `className?: string`
**Usage:**
```tsx
<CTASection title="Partner with us" buttonText="Contact" buttonHref="/contact" />
```

### `EmptyState`
**Purpose:** Placeholder for empty search/filter results.
**Props:**
- `title: string`
- `description?: string`
- `className?: string`

### `ProductGallery`
**Purpose:** Product image viewer with thumbnail selector.
**Props:**
- `images: string[]`
- `productName: string`
- `className?: string`

### `ContactCard`
**Purpose:** Contact info card with icon and linkable lines.
**Props:**
- `icon: string` (name from `Icon` mapper)
- `title: string`
- `lines: { label?: string; value: string; href?: string }[]`
- `className?: string`

### `FAQAccordion`
**Purpose:** Animated collapsible FAQ list.
**Props:**
- `items: FaqItem[]`
- `className?: string`

### `ValueCard`
**Purpose:** Icon + title + description card used for values and benefits.
**Props:**
- `icon: string`
- `title: string`
- `description: string`
- `index?: number`
- `className?: string`

### `MissionVisionCard`
**Purpose:** Large gradient card for mission/vision statements.
**Props:**
- `icon: string`
- `label: string`
- `title: string`
- `description: string`
- `className?: string`

### `AnimatedSection` / `AnimatedItem`
**Purpose:** Client wrappers for Framer Motion scroll animations from server components.
**Props:**
- `direction?: "up" | "down" | "left" | "right"`
- `delay?: number`
- `duration?: number`
- `className?: string`

### `Breadcrumb`
**Purpose:** Home-based trail with chevron separators.
**Props:**
- `items: { label: string; href?: string }[]`
- `className?: string`

### `Icon`
**Purpose:** Dynamic Lucide icon mapper.
**Props:**
- `name: string`
- `className?: string`

### `FormField` (`InputField`, `TextareaField`, `SelectField`)
**Purpose:** Accessible form fields with labels and error messages.

---

## Form Components

### `ContactForm`
**Purpose:** Validated contact form with loading/success states.
**Usage:**
```tsx
<ContactForm />
```

### `DistributorForm`
**Purpose:** Validated distributor application form.
**Usage:**
```tsx
<DistributorForm />
```

---

## Section Components

Homepage sections live in `components/sections/`:

- `HeroSection`
- `BrandIntroSection`
- `PromiseSection`
- `WhyChooseSection`
- `FeaturedProductsSection`
- `VisionBannerSection`
- `DistributorCtaSection`

---

## Layout Components

- `Navbar` — sticky responsive navigation with active route highlighting
- `Footer` — site footer with links and contact info
- `WhatsAppFloat` — floating WhatsApp button
- `RootLayoutClient` — client shell wrapping Navbar, Footer, and WhatsApp button

---

## Utilities

- `lib/animations.ts` — shared Framer Motion variants
- `lib/metadata.ts` — SEO metadata factory
- `lib/structured-data.tsx` — Schema.org JSON-LD helpers
- `lib/utils.ts` — `cn()` and `formatPrice()`
- `lib/data.ts` — static data layer (to be replaced by Laravel API)
