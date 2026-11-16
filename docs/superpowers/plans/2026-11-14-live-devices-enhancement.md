# Live Devices Enhancement Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Enhance the Live Devices menu (`/devices`) to support category filters (`voucher`, `bulanan`, `pribadi`, `bypassed`, `standby`), properly identify and present non-login bypassed devices (PCs, CCTV, servers via IP binding), fix table alignment, and resolve the false "Blocked" inspect bug.

**Architecture:**
1. **MikroTik IP Binding Querying**:
   - In `RouterOsService`, add `getHotspotIpBindings(): array` to fetch all active IP bindings from `/ip/hotspot/ip-binding/print`.
   - Update `getHotspotIpBinding(string $macAddress)` to ignore disabled bindings (`disabled != 'true'`).
2. **Device Controller Mapping**:
   - In `DeviceController::index` and `liveData`, retrieve IP bindings and active Hotspot users (with `profile.category`).
   - Match devices with their IP binding: if a device has an enabled `bypassed` binding, set `is_bypassed = true`, status to `bypassed` (or online bypassed), and populate `ip_binding_type` & `comment`.
   - Match logged-in devices with their user profile category (`voucher`, `bulanan`, `pribadi`).
3. **Inspect Modal & "Blocked" Bug Fix**:
   - In `DeviceController::detail`, ensure disabled IP bindings are never marked as active blocked bindings.
   - In `resources/views/devices/index.blade.php`, fix the IP binding badge in the inspect modal: only show badge when binding is enabled; render `bypassed` as emerald, `blocked` as red (only when actually blocked), and `regular` as neutral slate; do not display a false "BLOCKED" red badge for active or regular users.
4. **Filter Toolbar & UI Presentation**:
   - Add a Category filter dropdown/chips (`Semua Kategori`, `Voucher`, `Bulanan`, `Pribadi`, `Bypassed (Tanpa Login)`, `Standby / Belum Login`).
   - In the table and mobile cards, display category badges and clear bypassed status (e.g. `Bypassed (PC Admin / CCTV)`).
5. **Table Alignment**:
   - Fix table alignment: Left-aligned for Device/MAC/IP/User/Category, Center-aligned for Status, Right-aligned for Actions. All cells `align-middle`.

**Tech Stack:** Laravel 11, Blade, Alpine.js, Tailwind CSS, RouterOS API, PHPUnit.

---

### Task 1: RouterOS Service & DeviceController Backend Enhancement

**Files:**
- Modify: `app/Services/RouterOsService.php:760-855`
- Modify: `app/Http/Controllers/DeviceController.php:12-165, 210-252`

- [ ] Add `getHotspotIpBindings(): array` in `RouterOsService.php` that returns non-disabled bindings indexed by MAC/IP.
- [ ] Fix `getHotspotIpBinding(string $macAddress)` in `RouterOsService.php` to ignore disabled bindings (`disabled == 'true' || disabled == 'yes'`).
- [ ] In `DeviceController::index` and `liveData`:
  - Fetch IP bindings and active user profile categories (`HotspotUser::with('profile')`).
  - Map each device to its category: `voucher`, `bulanan`, `pribadi`, `bypassed`, or `standby`.
  - Set `is_bypassed = true`, `ip_binding = [...]`, and `status = 'bypassed'` for bypassed devices without hotspot login.
- [ ] In `DeviceController::detail`:
  - Ensure disabled IP bindings return `null` or explicit `disabled: true` so the UI does not show false blocks.

---

### Task 2: Fix Inspect "Blocked" Bug, Add Category Filters & Fix Table Alignment in View

**Files:**
- Modify: `resources/views/devices/index.blade.php`

- [ ] Fix the inspect modal header IP binding badge:
  - Do not render if binding is null or disabled.
  - If `bypassed`: render green badge `BYPASS`.
  - If `blocked`: render red badge `DIBLOKIR`.
  - If `regular`: render neutral badge `REGULAR`.
- [ ] Add Category filter to the toolbar:
  - Dropdown or chips for `Semua Kategori`, `Voucher`, `Bulanan`, `Pribadi`, `Bypassed`, `Standby`.
  - Update Alpine `filteredDevices` to filter by both search, status, type, and category.
- [ ] Display category badges on data rows:
  - For logged-in users: show category badge (`voucher` emerald, `bulanan` sky, `pribadi` purple).
  - For bypassed devices: show teal/emerald badge `Bypassed (Tanpa Login)` with binding comment if available.
  - For standby devices: show amber badge `Standby (Belum Login)`.
- [ ] Fix table alignment:
  - Consistent padding and vertical centering (`align-middle`).
  - Center-aligned `Status` column header and badges.
  - Right-aligned `Aksi` column header and buttons.

---

### Task 3: Feature Tests & Full Suite Verification

**Files:**
- Create: `tests/Feature/LiveDevicesEnhancementTest.php`

- [ ] Write tests covering:
  - Bypassed devices via IP binding have `is_bypassed = true` and category `bypassed`.
  - Devices with active hotspot users inherit profile category (`voucher`, `bulanan`, `pribadi`).
  - Disabled IP bindings in MikroTik do not return as blocked in device detail.
  - Inspect detail does not falsely tag active online users as "BLOCKED".
  - Filter category works in live devices endpoint / view.
- [ ] Run `php artisan test --filter LiveDevicesEnhancementTest`.
- [ ] Run full test suite `php artisan test`.
- [ ] Run `npm run build` and `php artisan view:clear`.
