# PWA & Mobile UI Optimization Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Transform the MikroTik Management application into a full-fledged installable Progressive Web App (PWA) with offline fallback, custom app icons, safe-area support, and a polished mobile UX adhering strictly to the `antislop-ui` design standards.

**Architecture:** 
- Add a W3C standard Web App Manifest (`manifest.json`) and a dedicated Service Worker (`sw.js`) with cache-first strategy for static assets and network-first strategy with offline page fallback for dynamic app routes.
- Generate high-fidelity PWA icons (192x192, 512x512, 512x512 maskable, apple-touch-icon, and favicon.svg) adhering to the MikroTik network management brand.
- Enhance the mobile layout (`components/layouts/app.blade.php` and `layouts/app.blade.php`) with viewport safe-area insets (`env(safe-area-inset-bottom)`), responsive bottom navigation with 5 accessible touch zones (including quick drawer trigger), and clean mobile typography.
- Audit and eliminate AI-slop visual artifacts (decorative emojis in headings/buttons, excessive pills) replacing them with semantic SVG icons and clean, purposeful typography per `/antislop-ui`.

**Tech Stack:** Laravel 11/12, Tailwind CSS v4, Alpine.js v3, Vite 8, Web App Manifest, Service Worker API, PHP GD (for icon generation).

**Spec:** User request: "buat aplikasi ini jadi PWA dan jika perlu ada penyesuaian untuk tampilan mobile /antislop-ui".

## Global Constraints

- Must be installable as standalone PWA on Android (Chrome), iOS (Safari), and Desktop (Edge/Chrome).
- Must work with both light and dark mode seamlessly without FOUC.
- Must follow `/antislop-ui`: zero decorative emojis in buttons, headings, badges, and tabs.
- Must preserve all existing functionality (realtime polling, router sync, voucher generation, POS cashier, session tracking).
- Must respect notch and home indicator safe areas (`viewport-fit=cover`, `env(safe-area-inset-*)`).

## Review Focus

1. **Service Worker Scoping and CSRF/API Handling**: Ensure POST/PUT/DELETE requests and MikroTik live-polling routes bypass offline caching so live network telemetry is never stale.
2. **Safe Area Overflow on iOS/Android**: Verify bottom navigation and main content container padding does not overlap with the Android gesture navigation bar or iOS home bar.
3. **PWA Installability Criteria**: Ensure manifest contains valid `name`, `short_name`, `start_url`, `display: standalone`, `theme_color`, `background_color`, and 192/512px icons.
4. **Offline Fallback Behavior**: When network connection drops, navigation requests gracefully display a branded, clean offline page with a "Coba Lagi" (retry) button.
5. **No Broken Blade Renders or JS Syntax Errors**: Verify `npm run build` passes with zero errors and assets bundle correctly.

---

### Task 1: Generate PWA Icons & Web App Manifest

**Files:**
- Create: `public/icons/icon-192.png`
- Create: `public/icons/icon-512.png`
- Create: `public/icons/icon-maskable-512.png`
- Create: `public/icons/apple-touch-icon.png`
- Create: `public/icons/favicon.svg`
- Create: `public/favicon.ico`
- Create: `public/manifest.json`

- [ ] Write a PHP generation script utilizing GD to create crisp network router / bolt icon assets with consistent branding (`#09090b` dark background, `#10b981` emerald accent).
- [ ] Run the icon generation script and verify created files in `public/icons/`.
- [ ] Create `public/manifest.json` specifying app name, short name, start URL (`/`), standalone display mode, orientation, theme colors, categories, and icon definitions.
- [ ] Validate manifest JSON syntax.

---

### Task 2: Service Worker & Offline Fallback Page

**Files:**
- Create: `public/sw.js`
- Create: `public/offline.html`

- [ ] Create `public/offline.html` with clean, functional offline messaging, network status indicator, and reconnect button (no generic AI emojis or slop).
- [ ] Create `public/sw.js` with:
  - Cache versioning (`mikrotik-manager-v1`).
  - Pre-caching of core static assets (manifest, offline.html, icons).
  - Network-first strategy for HTML navigations with fallback to `/offline.html`.
  - Cache-first strategy for static image/font assets.
  - Bypass caching for live telemetry polling (`/api/`, `/traffic/`, `/users/`, POST requests).
  - Proper `skipWaiting()` and `clients.claim()` lifecycle handling.

---

### Task 3: PWA Meta Tags, Registration & Safe-Area Layout Refinements

**Files:**
- Modify: `resources/views/components/layouts/app.blade.php`
- Modify: `resources/views/layouts/app.blade.php`
- Modify: `resources/js/app.js`
- Modify: `resources/css/app.css`

- [ ] Add PWA headers to both layout files (`viewport-fit=cover`, theme-color media queries, apple-touch-icon, manifest link, mobile-web-app-capable).
- [ ] Add service worker registration in `resources/js/app.js` on `window.load` in supported browsers.
- [ ] Add safe-area CSS utility variables and bottom bar safe-area padding to `resources/css/app.css`.
- [ ] Refactor mobile bottom navigation:
  - Add safe-area padding: `pb-[env(safe-area-inset-bottom,0px)]`.
  - Add 5th interactive item: "Menu" (triggers mobile drawer) so users can reach any page with one thumb.
  - Adjust main content bottom padding to prevent content clipping: `pb-[calc(4.5rem+env(safe-area-inset-bottom,0px))]`.

---

### Task 4: Antislop-UI Cleanup & Mobile Touch Polish

**Files:**
- Modify: `resources/views/pos/monthly.blade.php`
- Modify: `resources/views/devices/index.blade.php`
- Modify: `resources/views/vouchers/index.blade.php`
- Modify: `resources/views/traffic/index.blade.php`
- Modify: `resources/views/settings/maintenance.blade.php`
- Modify: `resources/views/components/command-palette.blade.php`

- [ ] Remove decorative emojis from buttons, headings, badges, and tabs (e.g. `⚡ Generate Tagihan`, `📋 Invoices`, `👥 Master`, `🎬 Streaming`, `🎮 Gaming`, `🎟 Voucher`).
- [ ] Replace with crisp, semantic SVG icons or clean typographic labels.
- [ ] Ensure buttons and form controls have minimum 44px touch targets on mobile viewports.
- [ ] Ensure tables have smooth scrolling (`-webkit-overflow-scrolling: touch`) and clean scrollbar hints.

---

### Task 5: Build, Test & Verification

**Files:**
- Verify: `npm run build`
- Verify: Manifest & Service Worker availability
- Verify: No broken Blade syntax

- [ ] Run `npm run build` to confirm Vite compiles all CSS and JS assets cleanly.
- [ ] Verify `manifest.json` and `sw.js` are served without syntax or path issues.
- [ ] Test offline page rendering and responsive layout.
