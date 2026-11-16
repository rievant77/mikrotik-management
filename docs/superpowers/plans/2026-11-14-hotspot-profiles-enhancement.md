# Hotspot Profiles Enhancement Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Upgrade Hotspot Profiles with category classification (`profit` vs `pribadi`), percentage-based FUP speed calculation, flexible cost price strategies (percent/fixed/custom), 12h validity option, `Notice & Record` expired mode, grid vs table view toggle, real-time search, filters, and exclusion of personal profiles from financial omset calculations.

**Architecture:** 
- Add database migration to augment `hotspot_profiles` table with `category`, `cost_price_type`, `cost_price_percent`, and `fup_rate_percent`.
- Implement dynamic percentage rate-limit calculator (e.g. `10M/10M` * 50% = `5M/5M`) and cost price auto-calculation in backend controller and frontend Alpine component.
- Ensure revenue/omset aggregation queries in POS and Dashboard omit transactions associated with `category === 'pribadi'` packages.
- Revamp `resources/views/hotspot/profiles.blade.php` to eliminate icon duplication, introduce clean Grid vs Table display modes, live searching, category/status filters, and full support for new package attributes.

**Tech Stack:** Laravel 11/12, PHP 8.4, SQLite/MySQL, Tailwind CSS v4, Alpine.js v3.

**Spec:** `docs/superpowers/specs/2026-11-14-hotspot-profiles-enhancement-design.md`

## Global Constraints

- Never break RouterOS profile synchronization (only `name`, `rate_limit`, `shared_users` are sent to MikroTik API).
- Backward compatibility: existing profiles default to `category = 'profit'` and `cost_price_type = 'custom'`.
- Packages with `category = 'pribadi'` must NOT contribute to omset in POS and Dashboard.
- Zero decorative AI emoji slop per `/antislop-ui`.
- All forms must work seamlessly on both mobile (touch-friendly) and desktop.

## Review Focus

1. **FUP Rate Parsing & Multiplication**: Ensure rate strings with varied units (e.g. `512k/2M`, `10M`, `10M/10M`) calculate correct percentage equivalents without producing invalid RouterOS rate format.
2. **Omset Accuracy**: Verify that generating or selling a voucher with a `pribadi` profile does not increment POS cashier shift totals or today's revenue.
3. **View State Persistence**: Ensure toggling between Grid and Table view works smoothly and remembers user choice via Alpine/localStorage.
4. **Notice & Record Expired Mode**: Validate that `Notice & Record` is accepted by controller validation and saved correctly.
5. **No Blade Compilation or Migration Failures**: Verify `php artisan migrate` and `npm run build` pass with zero errors.

---

### Task 1: Database Migration & HotspotProfile Model Updates

**Files:**
- Create: `database/migrations/YYYY_MM_DD_HHMMSS_add_category_and_fup_options_to_hotspot_profiles_table.php`
- Modify: `app/Models/HotspotProfile.php`

- [ ] Create database migration adding `category` (enum/string: 'profit', 'pribadi' default 'profit'), `cost_price_type` (string default 'custom'), `cost_price_percent` (decimal nullable), and `fup_rate_percent` (integer nullable) to `hotspot_profiles`.
- [ ] Update `app/Models/HotspotProfile.php` `$fillable` and `$casts`.
- [ ] Run `php artisan migrate` and verify schema updates.

---

### Task 2: Controller & Backend Rate/Cost Calculation Logic

**Files:**
- Modify: `app/Http/Controllers/HotspotProfileController.php`
- Modify: `app/Support/FormatHelper.php` (or dedicated helper method)
- Test: `tests/Feature/HotspotProfileTest.php`

- [ ] Add helper function to compute throttled rate limit string from base rate limit and percentage (e.g. `calculateFupRateLimit($baseRateLimit, $percentage)`).
- [ ] Update `HotspotProfileController::store` and `HotspotProfileController::update` validation rules:
  - Add `category` in `['profit', 'pribadi']`.
  - Add `cost_price_type` in `['custom', 'percentage', 'fixed']`.
  - Add `cost_price_percent` nullable numeric.
  - Add `fup_rate_percent` nullable integer.
  - Allow `expired_mode` to accept `Notice & Record`.
  - Automatically compute `cost_price` if `cost_price_type === 'percentage'` or `'fixed'`.
  - Automatically compute `fup_rate_limit` from `rate_limit` and `fup_rate_percent` if provided.
- [ ] Write unit/feature test verifying profile creation with new fields and FUP percentage calculation.

---

### Task 3: Omset Exclusion for Personal ('Pribadi') Profiles in POS & Dashboard

**Files:**
- Modify: `app/Http/Controllers/DashboardController.php`
- Modify: `app/Http/Controllers/PosController.php`
- Modify: `app/Models/VoucherSale.php`

- [ ] Audit queries calculating revenue and voucher counts in `DashboardController.php` and `PosController.php`.
- [ ] Add condition/scope so that voucher sales belonging to profiles with `category = 'pribadi'` are excluded from total revenue / omset calculations (or counted strictly as 0 revenue).
- [ ] Verify existing tests still pass.

---

### Task 4: UI Refactoring in Hotspot Profiles View

**Files:**
- Modify: `resources/views/hotspot/profiles.blade.php`

- [ ] Fix "+ Tambah Profile" button: remove duplicate plus sign so it displays a single plus icon and clean text.
- [ ] Add interactive View Toggle: **Grid Cards** vs **Table Rows** with icons and reactive Alpine state.
- [ ] Add Toolbar:
  - Real-time search input (`x-model="search"`).
  - Category filter dropdown: `Semua`, `Profit (Komersial)`, `Pribadi (Non-Profit)`.
  - Status filter dropdown: `Semua`, `Aktif`, `Non-Aktif`.
- [ ] Implement Table Row layout (`<div x-show="viewMode === 'table'">`) showing Name, Category Badge, Rate Limit, Shared Users, Validity, Cost & Selling Price, FUP, Status, and Action buttons.
- [ ] Update Create & Edit Profile Modals:
  - Add Category radio cards / toggle: **Profit (Komersial)** vs **Pribadi (Non-Profit / Internal)**.
  - Add Expired Mode option: **Notice & Record** (alongside Remove, Notice, Remove & Record).
  - Add Validity preset: **12 Jam (12h)**.
  - Add Cost Price mode selector: **Persentase (%)**, **Fixed**, **Custom**, with dynamic live computation preview.
  - Add FUP Rate percentage slider / input with live preview of the resulting throttled speed (e.g. `50%` of `10M/10M` = `5M/5M`).

---

### Task 5: Verification & End-to-End Build

**Files:**
- Verify: `php artisan test`
- Verify: `npm run build`
- Verify: `php artisan view:cache`

- [ ] Run `php artisan test` to verify no regressions in features.
- [ ] Run `npm run build` to confirm assets compile cleanly.
- [ ] Run `php artisan view:cache` and `php artisan view:clear` to verify Blade syntax.
