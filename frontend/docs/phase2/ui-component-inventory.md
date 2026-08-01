# Phase 2 — UI Component Inventory

## New Section Components

| Component | Location | Description |
|-----------|----------|-------------|
| `HeroSection` | `frontend/components/sections/hero-section.tsx` | Full-bleed hero with headline, CTAs, trust pills, hero image |
| `WhyChooseSection` | `frontend/components/sections/why-choose-section.tsx` | 6 value cards on navy gradient |
| `ProductCategoriesSection` | `frontend/components/sections/product-categories-section.tsx` | Dynamic category grid from API |
| `IndustriesSection` | `frontend/components/sections/industries-section.tsx` | Static 10-industry card grid |
| `ManufacturingSection` | `frontend/components/sections/manufacturing-section.tsx` | Split-layout capability section |
| `RequestQuoteSection` | `frontend/components/sections/request-quote-section.tsx` | Dark CTA section with audience chips |
| `TestimonialsSection` | `frontend/components/sections/testimonials-section.tsx` | Placeholder testimonial cards |
| `LatestArticlesSection` | `frontend/components/sections/latest-articles-section.tsx` | Placeholder blog cards |
| `ContactBannerSection` | `frontend/components/sections/contact-banner-section.tsx` | Final green CTA banner |

## Updated Components

| Component | Changes |
|-----------|---------|
| `FeaturedProductsSection` | Removed pricing; CTAs changed to Learn More / Request a Quote |
| `DistributorCtaSection` | Split layout with benefits list and product imagery |
| `Navbar` | Transparent-over-hero state; solid on scroll / other pages |
| `Footer` | Corporate branding, business hours, registered trademark, social icons |
| `Icon` | Added Factory, Building2, School, Hotel, Stethoscope, Briefcase, ShoppingCart, TrendingUp, Landmark, HeartHandshake, Facebook, Instagram, Newspaper |

## Shared Components Used
- `Container`
- `SectionHeader`
- `SkeletonGrid`
- `ApiError`
- `JsonLd`
