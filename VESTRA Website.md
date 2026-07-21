# VESTRA Website
## Phase 1.1 — Project Foundation, Architecture Refactoring & Public Website Baseline

You are taking over the VESTRA website project.

The existing project already contains a partially completed frontend, including the homepage, navigation, branding, and several public sections.

Do NOT discard the current work.

Do NOT rebuild the project from scratch.

Instead, refactor and reorganize the existing implementation into a professional, maintainable website architecture that will support the complete e-commerce platform in future phases.

This phase focuses only on establishing the project foundation while preserving the existing design.

---

# Objectives

The outcome of this phase is a clean, production-ready codebase with a scalable folder structure, reusable components, consistent styling, and a stable public website.

No shopping cart, checkout, payments, authentication, or customer accounts should be implemented during this phase.

---

# Existing Work to Preserve

The following should be retained and improved where necessary:

- VESTRA branding
- Logo
- Homepage hero
- Navigation
- About section
- Our Promise
- Why Choose VESTRA
- Featured Products
- Footer
- Responsive styling
- Overall colour palette
- Existing animations

Improve the implementation where necessary without changing the overall visual identity.

---

# Technology Stack

Frontend

- Next.js 15
- React 19
- TypeScript
- Tailwind CSS
- Shadcn UI
- Framer Motion
- Lucide Icons

Backend

- Laravel 12
- REST API
- MySQL

Do not begin backend implementation yet.

Only prepare the frontend so it can easily integrate with the backend in later phases.

---

# Project Structure

Refactor the project into the following structure.

frontend/

app/

components/

layout/

navigation/

hero/

products/

common/

sections/

hooks/

lib/

services/

styles/

types/

public/

assets/

images/

icons/

fonts/

---

# Component Refactoring

Break the homepage into reusable components.

Example

Navigation

Hero Section

Brand Introduction

Promise Section

Why Choose Section

Featured Products

Vision Section

Call To Action

Footer

Every section should be its own reusable component.

Avoid one large page file.

---

# Design System

Create reusable UI components.

Buttons

Cards

Section Heading

Feature Card

Product Card

Badge

Container

Grid

Input

Textarea

Modal

Drawer

Every component should follow a consistent design language.

---

# Global Layout

Create a reusable application layout.

This should include

Navigation

Main Content

Footer

WhatsApp Floating Button

SEO Metadata

Global Fonts

Theme Configuration

The layout should be shared across all public pages.

---

# Routing

Prepare routes for

/

Home

/about

About

/products

Products

/products/[slug]

Product Details

/distributor

Become a Distributor

/contact

Contact

404

Privacy Policy

Terms

Pages can contain placeholder content if not yet implemented.

---

# Asset Management

Organise all assets.

Example

assets/

images/

products/

branding/

hero/

icons/

backgrounds/

Move existing images into appropriate folders.

Rename files using consistent naming conventions.

Example

heavy-duty-detergent.png

silk-care.png

ecosuit-cleaner.png

pro-finish.png

home-page-image.png

vestra-logo.png

---

# Styling Standards

Use Tailwind utility classes consistently.

Avoid inline styles.

Avoid duplicated styling.

Create reusable utility classes where appropriate.

Use CSS variables for

Primary colour

Accent colour

Text colours

Background colours

Border radius

Spacing

Shadows

Transitions

---

# Responsive Foundation

The website must behave consistently across

1920px

1600px

1440px

1366px

1280px

1024px

768px

480px

320px

There should be

No horizontal scrolling

No overlapping sections

No clipped content

No fixed-width layouts

Use responsive spacing and typography.

---

# Images

Optimise all images.

Use the Next.js Image component.

Lazy-load below-the-fold images.

Compress images where appropriate.

Maintain aspect ratios.

---

# Performance

Optimise for

Core Web Vitals

Fast initial load

Minimal layout shift

Lazy loading

Reusable components

Clean rendering

---

# Code Quality

Use TypeScript throughout.

Create interfaces for reusable data structures.

Remove duplicated code.

Organise imports.

Use meaningful component names.

Maintain consistent formatting.

---

# Future Preparation

Although not implementing them yet, organise the codebase so it is ready for

Laravel API integration

Authentication

Shopping Cart

Checkout

Orders

Inventory

Customer Portal

Admin Dashboard

Product Search

Filtering

Reviews

Payments

Notifications

No mock backend logic should be introduced yet.

---

# Deliverables

By the end of this phase the project should include

A clean project structure

Reusable components

Reusable layouts

Responsive public pages

Optimised assets

Consistent styling

Modern Next.js architecture

A stable homepage preserved from the existing implementation

Ready for backend integration in the next phase

---

# Success Criteria

✔ Existing homepage preserved and improved.

✔ Components fully modular.

✔ Clean folder structure.

✔ Responsive across all major devices.

✔ Assets organised.

✔ Styling standardised.

✔ No duplicated code.

✔ Ready for Laravel backend integration.

✔ Production-quality frontend foundation established.