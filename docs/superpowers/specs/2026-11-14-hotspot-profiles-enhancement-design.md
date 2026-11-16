# Hotspot Profiles & Paket Enhancement Design

**Date:** 2026-11-14  
**Topic:** Hotspot Profiles Enhancement (Category, FUP Percentage, Cost Price Strategy, Expired Mode, Search/Filter & View Toggle)  
**Status:** Approved  

---

## 1. Intent and The Why

The Hotspot Profiles page is the core configuration hub for Mikrotik hotspot voucher packages, bandwidth caps, validity periods, and pricing. The admin needs:
1. Precise UI refinement (removing redundant plus icon).
2. Reliable expiration handling (`Notice & Record`) to guarantee audit trail continuity.
3. Segregation of profiles between commercial packages (`profit`) and internal/complimentary use (`pribadi` / personal non-profit), ensuring personal vouchers are strictly excluded from financial revenue/omset calculations in POS and reports.
4. Simplified FUP throttled rate configuration using dynamic percentage calculation based on original rate limits.
5. Flexible Cost of Goods Sold (Harga Pokok Penjualan / HPP) methods (percentage of selling price, fixed amount, custom manual).
6. Short-duration 12-hour validity preset.
7. Enhanced overview via dual view modes (Card Grid vs Table Rows), comprehensive status/category filters, and live search.

---

## 2. Goals, Non-Goals, and Anti-Goals

### Goals
- UI cleanup: Single clean plus icon on "+ Tambah Profile" button.
- Database & Model additions:
  - `category`: `profit` (default) vs `pribadi` (personal / non-profit).
  - `cost_price_type`: `custom`, `percentage`, `fixed`.
  - `cost_price_percent`: decimal percentage if type is percentage.
  - `fup_rate_percent`: percentage of base rate limit used when FUP is triggered.
  - `expired_mode`: support `Notice & Record` alongside `Remove`, `Notice`, `Remove & Record`.
- Omset calculation exclusion: When generating vouchers or viewing sales summaries (in POS, Reports, Dashboard), items with category `pribadi` do NOT count towards revenue/omset.
- Validity preset: Add `12 Jam` (`12h`) in the dropdown alongside existing options (1 Jam, 3 Jam, 6 Jam, 1 Hari, 7 Hari, 30 Hari, Unlimited).
- UI Views & Filters:
  - Card view (existing card grid layout).
  - Table view (dense row layout for quick scanning).
  - Search input filtering profile name and validity.
  - Filter by category (`all`, `profit`, `pribadi`) and status (`all`, `active`, `inactive`).

### Non-Goals
- Altering RouterOS protocol or Winbox core rate limit syntax (RouterOS only expects `rx/tx` strings like `5M/5M`, which our percentage calculator will generate before sending to the router).
- Breaking existing voucher profiles or historical transactions.

### Anti-Goals
- Hardcoding financial formulas that prevent custom pricing edits.
- Counting personal/internal voucher creation as cash inflow or POS revenue.

---

## 3. Constraints

- Framework: Laravel 11/12, Tailwind CSS v4, Alpine.js v3.
- All new database fields must have migrations with backward-compatible defaults (`category` defaults to `'profit'`, `cost_price_type` defaults to `'custom'`).
- RouterOS synchronization: `HotspotProfileController` and `RouterOsService` must continue to sync `name`, `rate_limit`, and `shared_users` with MikroTik without throwing errors on new metadata fields.
- Zero decorative AI emoji slop per `/antislop-ui`.

---

## 4. Technical Specifications

### 4.1 Database Migration
Add to `hotspot_profiles`:
- `category` (`string`, default `'profit'`) -> values: `profit`, `pribadi`.
- `cost_price_type` (`string`, default `'custom'`) -> values: `custom`, `percentage`, `fixed`.
- `cost_price_percent` (`decimal(5,2)`, nullable).
- `fup_rate_percent` (`integer`, nullable, e.g. 50 for 50%).

Update `expired_mode` validation and options:
- Allowed values: `Remove`, `Notice`, `Notice & Record`, `Remove & Record`.

### 4.2 FUP Percentage Calculation
Given a base `rate_limit` (e.g., `10M/10M` or `512k/2M`):
- If `fup_rate_percent` is chosen (e.g., `50%`):
  - Helper parses Rx and Tx values (e.g. `10M` -> 10,000,000 bps).
  - Multiplies by percentage: `10M * 50% = 5M`.
  - Produces `fup_rate_limit = '5M/5M'`.
- Supports manual override if needed.

### 4.3 Cost Price (HPP) Calculation
In the form and backend:
- `custom`: Direct manual input of `cost_price` in IDR.
- `percentage`: `cost_price = selling_price * (cost_price_percent / 100)`.
- `fixed`: Global or preset default fixed cost price.

### 4.4 Omset & Revenue Logic
In POS and sales queries (e.g. `VoucherSale`, `MonthlyInvoice`, `DashboardController`, `PosController`):
- Sales tied to a profile with `category === 'pribadi'` are flagged as non-revenue (omset = 0) or filtered out from revenue totals.

### 4.5 Hotspot Profiles Frontend (`resources/views/hotspot/profiles.blade.php`)
- Remove duplicate plus sign from "+ Tambah Profile" button.
- Add View toggle: Grid vs Table buttons with icons.
- Add Search input (`x-model="search"`).
- Add Filter dropdowns for Category (`Semua`, `Profit (Komersial)`, `Pribadi (Non-Profit)`) and Status (`Semua`, `Aktif`, `Non-Aktif`).
- Form Modal:
  - Add Category radio/selector (Profit vs Pribadi).
  - Add Expired Mode option: `Notice & Record`.
  - Add Validity option: `12 Jam (12h)`.
  - Add Cost Price mode selector: Persentase (%), Fixed, Custom.
  - Add FUP Rate calculation slider / selector (e.g. 20%, 30%, 50%, 70% of base speed).
