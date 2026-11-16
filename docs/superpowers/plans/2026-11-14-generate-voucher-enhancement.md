# Generate Voucher Enhancement Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Enhance the batch voucher generator page (`/hotspot/generate`) to add an icon to the "User Hotspot" navigation button, set the default Name Length to 4, and filter the default Profile options to only include the voucher category (with an option to view other categories if needed).

**Architecture:** 
1. In `resources/views/hotspot/generate.blade.php`, add a user-group SVG icon to the "User Hotspot" top button.
2. In `generate.blade.php` Alpine state and `VoucherBatchService`, set default `length: 4` and make the preview code match the configured length dynamically.
3. In `VoucherController::generateView`, ensure active profiles are filtered by category with `voucher` as the primary default, and provide category-aware profile selection in `generate.blade.php` Alpine component (defaulting to `voucher`).
4. Add automated feature tests verifying default length 4, default voucher category profiles, and button styling/icons.

**Tech Stack:** Laravel 11, Blade, Alpine.js, Tailwind CSS, PHPUnit.

**Spec:** Direct user request:
1. Tombol user hotspot tambahkan icon.
2. Name Length default adalah 4.
3. profile (paket hotspot) default isinya adalah hanya kategori voucher.

## Global Constraints
- Clean, semantic UI (anti-slop, no decorative emojis).
- RouterOS API compatibility and robust fallback when router is offline.
- File references must use clickable markdown links with `file:///` format and forward slashes.

## Review Focus
1. Generate view loads profiles with `category == 'voucher'` (and legacy `profit`) by default without failing when only non-voucher profiles exist.
2. Batch voucher generation request defaults to length 4 if omitted.
3. Live preview sample code accurately responds to the default length 4 and changes if the user edits the length input.
4. Top navigation buttons have uniform styling, spacing, and icons (`Daftar Voucher` and `User Hotspot`).
5. All existing test suites continue to pass without regression.

---

### Task 1: Add Icon & Set Default Length to 4 in Frontend View

**Files:**
- Modify: `resources/views/hotspot/generate.blade.php`
- Modify: `app/Services/VoucherBatchService.php:32`

- [ ] Add SVG users icon to the "User Hotspot" anchor button in `resources/views/hotspot/generate.blade.php`.
- [ ] Set `length: 4` in Alpine `form` default state in `resources/views/hotspot/generate.blade.php`.
- [ ] Update `sampleCode` in `generate.blade.php` to generate preview code respecting `this.form.length` (4 characters default).
- [ ] Update fallback length in `VoucherBatchService.php` from 5 to 4.

---

### Task 2: Controller & UI Profile Category Filtering (Default to Voucher)

**Files:**
- Modify: `app/Http/Controllers/VoucherController.php:152-165`
- Modify: `resources/views/hotspot/generate.blade.php:87-100`

- [ ] In `VoucherController::generateView`, query active profiles and pass them with their category, defaulting `$profiles` to only `whereIn('category', ['voucher', 'profit'])` and also passing `$allProfiles` (or passing all active profiles with category for client-side filtering).
- [ ] In `generate.blade.php`, add a category filter pill/selector or category-aware filter to "7. Profile (Paket Hotspot)", defaulting to `voucher` so the select list initially only contains voucher profiles.
- [ ] Ensure default selected profile `form.profile` is initialized to the first available voucher profile.

---

### Task 3: Automated Feature Test & Verification

**Files:**
- Create: `tests/Feature/GenerateVoucherEnhancementTest.php`

- [ ] Write feature test verifying:
  - `GET /hotspot/generate` displays the User Hotspot icon.
  - The default length input has value `4`.
  - The profiles rendered in the select dropdown by default belong to `category = 'voucher'`.
  - Batch generation without explicit length defaults to 4.
- [ ] Run `php artisan test --filter GenerateVoucherEnhancementTest`.
- [ ] Run full test suite `php artisan test`.
- [ ] Run `npm run build` and `php artisan view:clear`.
