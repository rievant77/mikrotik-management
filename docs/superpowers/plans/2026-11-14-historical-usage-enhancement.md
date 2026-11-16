# Historical Usage Enhancement Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Enhance the Historical Usage menu (`/users/historical`) by adding user category support (`voucher`, `bulanan`, `pribadi`), adding category filters alongside profile and search filters, implementing client-side pagination on the user breakdown table, and correcting all table column alignments (`align-middle`, matching header and cell alignments).

**Architecture:**
1. **Controller Backend (`UserController.php`)**:
   - In `historical()`:
     - Map user category into each item of `$summaries`:
       `'category' => $userModel?->profile?->category ?? 'voucher'`.
     - Ensure `$profiles` are ordered by category hierarchy (`voucher`, `bulanan`, `pribadi`).
     - Include category stats in summary metrics if needed.
2. **View Template (`resources/views/users/historical.blade.php`)**:
   - **Filter Toolbar**:
     - Search input.
     - Kategori dropdown filter (`Semua Kategori`, `Voucher (Profit)`, `Bulanan (Profit)`, `Pribadi (Non-Profit)`).
     - Profil filter dropdown.
     - Sort dropdown (Total Pemakaian, Download Rx, Upload Tx, Uptime, Username).
   - **Table Alignment & Components**:
     - Columns:
       1. User (`text-left`, `align-middle`)
       2. Kategori (`text-center`, `align-middle`, with semantic color badge)
       3. Status (`text-center`, `align-middle`, Online/Offline badge)
       4. Profil (`text-left`, `align-middle`)
       5. Sesi (`text-center`, `align-middle`)
       6. Uptime (`text-center`, `align-middle`)
       7. Upload Tx (`text-right`, `align-middle`)
       8. Download Rx (`text-right`, `align-middle`)
       9. Total Pemakaian (`text-right`, `align-middle`)
       10. Aksi / Detail (`text-center`, `align-middle`)
   - **Pagination (Daftar Pengguna)**:
     - Alpine pagination: `currentPage`, `perPage` (default 15, options 10/15/25/50/100).
     - Computed `paginatedSummaries` slicing `filteredSummaries`.
     - Reset `currentPage = 1` on filter/search/sort change.
     - Pagination navigation toolbar at bottom with page numbers, Prev/Next buttons, and "Menampilkan X - Y dari Z pengguna".
3. **Automated Testing (`tests/Feature/HistoricalUsageEnhancementTest.php`)**:
   - Test `/users/historical` page renders with category filter, table headers, and pagination controls.
   - Test summaries contain `category`.
   - Test CSV export includes category column.

---

### Task 1: Backend Session & Category Mapping in UserController

**Files:**
- Modify: `app/Http/Controllers/UserController.php:510-565`

- [ ] In `UserController::historical()`:
  - Add `'category' => $userModel?->profile?->category ?? 'voucher'` to each item in `$summaries`.
  - In CSV export, add `Kategori` column to header and data rows.
  - Order `$profiles` by category priority (`voucher`, `bulanan`, `pribadi`).

---

### Task 2: Refactor UI, Filters, Table Alignment & Pagination in historical.blade.php

**Files:**
- Modify: `resources/views/users/historical.blade.php`

- [ ] Add `Kategori` filter dropdown in toolbar.
- [ ] Add `categoryFilter` property in Alpine `historicalAnalytics()`.
- [ ] Add pagination state: `currentPage: 1`, `perPage: 15`.
- [ ] Create `paginatedSummaries` computed getter and watcher to reset `currentPage` when filter changes.
- [ ] Update Table Structure:
  - Add `Kategori` column with center-alignment.
  - Set `align-middle` on all cells.
  - Fix header alignment to match cell alignment (`text-center` for Status, Sesi, Uptime, Aksi; `text-right` for Tx, Rx, Total Pemakaian).
  - Style Detail button as a cohesive action component.
- [ ] Add Pagination Controls component at the bottom of the table.

---

### Task 3: Feature Tests & Verification

**Files:**
- Create: `tests/Feature/HistoricalUsageEnhancementTest.php`

- [ ] Write feature tests for:
  - Page rendering with category filter, table alignment, and pagination elements.
  - CSV export containing category data.
- [ ] Run `php artisan test --filter HistoricalUsageEnhancementTest`.
- [ ] Run full test suite `php artisan test`.
- [ ] Run `npm run build` and `php artisan view:clear`.
