# Hotspot Users Enhancement & Redundant Sync Cleanup Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor the Hotspot Users page (`/hotspot/users`) to fix the double plus icon, eliminate redundant page-level sync buttons, add category filtering and badges, improve live uptime progress, introduce server-side pagination, and fix table layout alignment.

**Architecture:**
1. Backend: Enhance `HotspotUserController::index()` to query active RouterOS sessions for live uptime calculation, accept and filter by `category` (alongside `search`, `profile_id`, and `status`), and pass paginated `$users` (`paginate(25)->withQueryString()`) with `$activeUserMap`.
2. Frontend: Modernize `resources/views/hotspot/users.blade.php` to render paginated users with Blade, integrate Alpine.js for interactive modals (create, edit, delete, toggle status), add category badges, format uptime with `FormatHelper::getUptimeProgress`, fix header buttons (single plus icon, remove redundant sync), and polish table cell alignment.
3. Redundant Sync Cleanup: Remove obsolete page-level "Sinkron dari Router" buttons from `hotspot/users.blade.php` and `hotspot/profiles.blade.php` in favor of the global navbar Sync Router button.
4. Testing: Add comprehensive test cases in `tests/Feature/HotspotUserEnhancementTest.php` to verify category filtering, pagination, uptime display, and button rendering.

**Tech Stack:** Laravel 12, Blade, Alpine.js, Tailwind CSS, RouterOS API.

**Spec:** Direct user requirements (7 items) from chat prompt.

## Global Constraints

- Never use generic AI slop or decorative emojis (🚀, ⚡, etc.); use clean Zinc + semantic Tailwind colors.
- Preserve RouterOS API compatibility (`name`, `rate-limit`, `shared-users`).
- Maintain 100% test suite pass rate.
- Semantic category colors: `voucher` = Emerald, `bulanan` = Blue/Sky, `pribadi` = Purple.

## Review Focus

1. Category filter with legacy `profit` profile mappings.
2. Pagination query string preservation when navigating pages with active filters.
3. Uptime calculation handling both active sessions and offline profiles with null or formatted limits.
4. Hotspot user create/update modal operation with Alpine without full page reload.
5. Removal of local sync buttons without breaking navbar `settings.router.sync`.

---

### Task 1: Clean Up Redundant Page-Level Sync Buttons & Fix Plus Icons

**Files:**
- Modify: `resources/views/hotspot/users.blade.php:1-35`
- Modify: `resources/views/hotspot/profiles.blade.php:1-35`
- Test: `tests/Feature/HotspotUserEnhancementTest.php`

- [ ] **Step 1: Write test for single plus icon and absence of redundant sync buttons**
  Create `tests/Feature/HotspotUserEnhancementTest.php` testing that `GET /hotspot/users` has `<span>Tambah User</span>` (not `<span>+ Tambah User</span>`), and does not contain the page-level `syncUsers()` button.
- [ ] **Step 2: Run test to verify failure**
  Run: `php artisan test tests/Feature/HotspotUserEnhancementTest.php`
- [ ] **Step 3: Update `resources/views/hotspot/users.blade.php`**
  Remove the `syncUsers()` button in the header; change `<span>+ Tambah User</span>` to `<span>Tambah User</span>`.
- [ ] **Step 4: Update `resources/views/hotspot/profiles.blade.php`**
  Remove the `syncProfiles()` button in the header (navbar already handles sync).
- [ ] **Step 5: Run test to verify it passes**
  Run: `php artisan test tests/Feature/HotspotUserEnhancementTest.php`

---

### Task 2: Backend Controller Enhancement in `HotspotUserController`

**Files:**
- Modify: `app/Http/Controllers/HotspotUserController.php:15-65`
- Test: `tests/Feature/HotspotUserEnhancementTest.php`

**Interfaces:**
- Consumes: `RouterOsService::getActiveHotspotSessions()`, `HotspotProfile::all()`
- Produces: `$users` (`LengthAwarePaginator`), `$profiles` (`Collection`), `$activeUserMap` (`array`), `$category` (`string`)

- [ ] **Step 1: Add test for category filtering and pagination in `HotspotUserEnhancementTest`**
  Assert `GET /hotspot/users?category=voucher` only returns voucher users, and `GET /hotspot/users?page=2` returns page 2 paginator.
- [ ] **Step 2: Run test to verify failure**
  Run: `php artisan test tests/Feature/HotspotUserEnhancementTest.php`
- [ ] **Step 3: Implement category filter, active sessions map, and pagination in `HotspotUserController::index`**
  Update query with category filter (`whereHas('profile', ...)`), query active sessions for `$activeUserMap`, and paginate 25 items per page with query string.
- [ ] **Step 4: Run test to verify it passes**
  Run: `php artisan test tests/Feature/HotspotUserEnhancementTest.php`

---

### Task 3: Modernize Frontend View & Table Layout in `hotspot/users.blade.php`

**Files:**
- Modify: `resources/views/hotspot/users.blade.php`
- Test: `tests/Feature/HotspotUserEnhancementTest.php`

- [ ] **Step 1: Update Toolbar Filter**
  Add category dropdown (`Semua Kategori`, `Voucher (Profit)`, `Bulanan (Profit)`, `Pribadi (Non-Profit)`), profile dropdown with category tags, status dropdown, search input, and reset filter button.
- [ ] **Step 2: Update Table Rows, Column Alignment & Category Badges**
  Add Kategori column with semantic badges (`Voucher`, `Bulanan`, `Pribadi`). Fix column text alignments (`text-left` for username/profile/uptime, `text-center` for status, `text-right` for actions).
- [ ] **Step 3: Implement Live Uptime Progress**
  For active sessions: show `Online` badge and remaining/used uptime progress with a clean mini progress bar. For offline users: show configured limit or `Unlimited`.
- [ ] **Step 4: Add Pagination Bar**
  Add `{{ $users->links() }}` and pagination summary below the table.
- [ ] **Step 5: Keep Interactive Modals Operational**
  Ensure Alpine `openCreateModal()`, `editUser(u)`, `deleteUser(u)`, and `toggleStatus(u)` work seamlessly with the server-rendered rows.

---

### Task 4: Full Suite Verification & Build

**Files:**
- Test: Full PHPUnit test suite
- Build: Vite production build & Blade cache

- [ ] **Step 1: Run full test suite**
  Run: `php artisan test`
- [ ] **Step 2: Run frontend asset build**
  Run: `npm run build`
- [ ] **Step 3: Run blade view cache check**
  Run: `php artisan view:clear && php artisan view:cache`
