# Online Users Enhancement Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Enhance the Online Users menu (`/users`) in accordance with the prior improvements across the app: add category awareness (`voucher`, `bulanan`, `pribadi`), category filters, top KPI metric cards, accurate human-readable uptime formatting (`FormatHelper`), perfect table alignment with vertical centering (`align-middle`), and refined action button components.

**Architecture:**
1. **Controller Backend (`UserController.php`)**:
   - In `mapActiveSessions`:
     - Resolve user category: `$userModel?->profile?->category ?? 'voucher'`.
     - Resolve formatted Indonesian uptime using `FormatHelper::formatUptime(FormatHelper::parseUptime($raw['uptime'] ?? 0))`.
     - Provide `uptime_formatted` and `uptime_seconds`.
     - Calculate aggregated summary metrics in `index()` and `liveData()`: `total_online`, `voucher_count`, `bulanan_count`, `pribadi_count`, `total_rx_bps`, `total_tx_bps`, `total_bytes`.
2. **View Template (`resources/views/users/index.blade.php`)**:
   - **KPI Metric Summary Cards**:
     - 5 metric cards: Total Online, Voucher (Profit), Bulanan (Profit), Pribadi (Non-Profit), and Live Traffic (Rx/Tx).
   - **Filter Toolbar**:
     - Search input.
     - Kategori filter (Semua, Voucher, Bulanan, Pribadi).
     - Paket / Profil filter chips.
     - Sort dropdown (Speed Rx, Total Kuota, Uptime, Username).
   - **Table Alignment & Components (Desktop)**:
     - Header and cell alignment alignment matching data types (`align-middle` across all rows).
     - User & Device: `text-left`
     - IP / MAC: `text-left`, mono with quick-copy
     - Kategori: `text-center`, color badges (Emerald for Voucher, Sky for Bulanan, Purple for Pribadi)
     - Profil: `text-left`, clean badge
     - Uptime: `text-center`, mono formatted duration
     - Live Rate (Rx/Tx): `text-right`, mono with download/upload arrows
     - Total Kuota: `text-right`, mono bold
     - Aksi: `text-center`, polished button components (`Detail` and `Kick`) with clear borders and semantic icons.
   - **Mobile Cards (Responsive)**:
     - Category badge next to username.
     - Formatted uptime and clear rate metrics.
     - Refined action buttons.
3. **Automated Testing (`tests/Feature/OnlineUsersEnhancementTest.php`)**:
   - Test category identification and filtering for online users.
   - Test uptime formatting and KPI stats calculations.
   - Test table headers, alignment, and action button components rendering.

---

### Task 1: Backend Session Mapping & Stats in UserController

**Files:**
- Modify: `app/Http/Controllers/UserController.php`

- [ ] In `UserController::mapActiveSessions()`:
  - Add `category` to each session item from `$userModel?->profile?->category ?? 'voucher'`.
  - Parse and format uptime with `FormatHelper::parseUptime()` and `FormatHelper::formatUptime()`.
  - Add `uptime_formatted` and `uptime_seconds`.
- [ ] In `UserController::index()` and `UserController::liveData()`:
  - Calculate `$stats` array:
    - `total_online`: count of active sessions.
    - `voucher`: count where category is 'voucher'.
    - `bulanan`: count where category is 'bulanan'.
    - `pribadi`: count where category is 'pribadi'.
    - `total_rx_bps`: sum of `current_rx_bps`.
    - `total_tx_bps`: sum of `current_tx_bps`.
    - `total_bytes`: sum of `total_bytes`.
  - Pass `$stats` to `view('users.index')` and return `$stats` in `liveData()` JSON.
  - Sort profiles in `index()` by category order (`voucher`, `bulanan`, `pribadi`).

---

### Task 2: Refactor UI, Filters, Table Alignment & Action Buttons in users/index.blade.php

**Files:**
- Modify: `resources/views/users/index.blade.php`

- [ ] Add 5 KPI metric cards at top: Total Online, Voucher, Bulanan, Pribadi, and Live Traffic (Rx/Tx).
- [ ] Add Kategori dropdown/chips filter to toolbar (`categoryFilter`).
- [ ] Update Alpine `filteredUsers` getter to filter by search query, `categoryFilter`, and `selectedProfile`, with sort options.
- [ ] Desktop Table Alignment & Structure:
  - Add `Kategori` column with center-alignment.
  - Align `Uptime` column to center with clock icon and formatted uptime.
  - Align `Live Rate` and `Total Data Sesi` to right.
  - Ensure all `<td>` and `<th>` elements have matching text alignment and `align-middle`.
  - Update Action column component: Replace bare icon links with styled action button components (`Detail` and `Putus/Kick`) with border, hover states, and clear icons.
- [ ] Mobile Cards:
  - Display category badges and formatted uptime.
  - Polish bottom action buttons.

---

### Task 3: Feature Tests & Verification

**Files:**
- Create: `tests/Feature/OnlineUsersEnhancementTest.php`

- [ ] Write feature tests for:
  - Online users page rendering with KPI metrics and category filter.
  - Live data API returns session items with `category`, `uptime_formatted`, and `stats`.
  - Action buttons and table alignment classes are present.
- [ ] Run `php artisan test --filter OnlineUsersEnhancementTest`.
- [ ] Run full test suite `php artisan test`.
- [ ] Run `npm run build` and `php artisan view:clear`.
