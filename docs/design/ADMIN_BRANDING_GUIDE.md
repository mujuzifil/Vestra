# VESTRA Administration Branding Guide

## 1. Brand Positioning

The VESTRA Administration Platform is an extension of the VESTRA brand. While the public website speaks to customers (premium garment care, trust, fabric protection), the admin platform speaks to internal operators (control, clarity, efficiency). Both share the same visual DNA but are optimised for their respective audiences.

---

## 2. Logo Usage

### 2.1 Logo Placement

- **Top bar:** VESTRA wordmark or logo mark on the left.
- **Login page:** Centred logo mark above the login card.
- **Favicon:** VESTRA icon in browser tab.
- **Email notifications:** VESTRA logo in header.

### 2.2 Logo Variants

- **Full logo:** Wordmark + symbol for login and large surfaces.
- **Wordmark only:** Top bar, where horizontal space is limited.
- **Icon only:** Favicon, avatar fallback, mobile app icon.

### 2.3 Logo Clear Space

- Minimum clear space around logo: equal to the height of the "V" in VESTRA.
- Do not distort, rotate, or apply effects to the logo.

### 2.4 Logo Colour

- **Light backgrounds:** Navy logo (`#0a1628`).
- **Dark backgrounds:** White logo.
- **Single colour surfaces:** Use navy or white only; never green or gold logo on its own.

---

## 3. Login Experience

### 3.1 Layout

- Full-height layout with a subtle brand illustration or pattern on one side.
- Login card on the other side, centred vertically.
- Card width: `420px` maximum.

### 3.2 Visual Treatment

- Background: `--primary-800` with a soft green glow accent (`--shadow-glow`).
- Card: white, `--radius-xl`, `--shadow-xl`.
- Logo: white wordmark above card.

### 3.3 Copy

- Title: "Sign in to VESTRA"
- Subtitle: "Administrator Portal"
- Button: "Sign In"
- Footer: "© VESTRA. All rights reserved."

### 3.4 Force Password Change Page

- Same layout as login.
- Title: "Update Your Password"
- Description: "For security, please create a new password before continuing."

---

## 4. Favicon

- Format: SVG primary, ICO fallback.
- Colour: Navy icon on transparent background.
- Sizes: 16×16, 32×32, 180×180 (touch icon).

---

## 5. Colour Usage in Admin

### 5.1 Primary Brand Expression

- Navy (`--primary-800`) is the dominant brand colour, used for sidebar and headers.
- Green (`--secondary-500`) is used for success states and positive accents.
- Gold (`--accent-500`) is used sparingly for warnings and premium highlights.

### 5.2 Colour Balance

- 70% neutral surfaces
- 20% navy brand surfaces
- 8% green/gold accents
- 2% red for errors

### 5.3 Dark Surfaces

- Sidebar background: `--primary-800`
- Active navigation: `--primary-700`
- Text on dark: `--neutral-50`
- Muted text on dark: `--neutral-400`

---

## 6. Typography in Brand Context

- Use **Poppins** for all admin UI text.
- Use **JetBrains Mono** for technical values (order IDs, SKUs, JSON).
- Maintain the type hierarchy defined in the design system.

---

## 7. Brand Imagery

### 7.1 Illustrations

- Style: Flat, geometric, minimal line work.
- Colours: Navy, green, gold on neutral or white backgrounds.
- Usage: Empty states, onboarding, login background.

### 7.2 Photography

- Avoid product photography inside the admin interface.
- Product thumbnails are the exception.

### 7.3 Icons

- Heroicons outline style for navigation and actions.
- Solid style for status badges.
- Consistent `20px` navigation, `16px` inline.

---

## 8. Relationship to Public Website

| Element | Public Website | Admin Platform |
|---------|----------------|----------------|
| Primary colour | Navy (`#0a1628`) | Navy (`#0a1628`) |
| Accent | Green + Gold | Green + Gold |
| Typography | Poppins | Poppins |
| Radius | Large (`16px–28px`) | Medium (`8px–16px`) |
| Density | Generous, editorial | Efficient, operational |
| Mood | Premium, inviting | Focused, authoritative |

The admin should feel like a sibling to the public site, not a stranger.

---

## 9. Tone of Voice

- **Professional:** No slang, no emojis in operational copy.
- **Helpful:** Explain consequences of actions.
- **Concise:** Use the fewest words that preserve clarity.
- **Confident:** Avoid hedging language like "maybe" or "possibly."

---

## 10. Brand Compliance Checklist

- [ ] Logo used correctly in top bar and login
- [ ] Favicon present and correct
- [ ] Primary navy used for sidebar/header
- [ ] Green reserved for success and positive actions
- [ ] Gold reserved for warnings and premium highlights
- [ ] Poppins used throughout
- [ ] Empty states use VESTRA illustration style
- [ ] No external placeholder images
- [ ] Copy tone is professional and concise
